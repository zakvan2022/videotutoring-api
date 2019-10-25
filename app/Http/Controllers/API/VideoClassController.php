<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller; 

use App\User;
use App\Models\TutorProfile;
use App\Models\ParentProfile;
use App\Models\StudentProfile;

use App\Models\School;
use App\Models\Topic;
use App\Models\Address;
use App\Models\Degree;
use App\Models\Price;
use App\Models\Identity;
use App\Models\W9Form;
use App\Models\VideoClass;
use App\Models\Feedback;

use App\Classes\PropertySuccessResponse;
use App\Classes\SuccessResponse;
use App\Classes\PropertyErrorResponse;
use App\Classes\ErrorResponse;

use App\Repositories\PaymentRepository;
use App\Services\StripeService;
use App\Services\PaypalService;

use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;

class VideoClassController extends Controller
{
    
    public function __construct() {
        StripeService::setApiKey();
        PaypalService::setToken();
        $this->paypal_provider = new PaypalService();
    }

    /** GET
     * /api/user/videoclass
     * 
     * return Video Classes
     */ 
    public function getVideoClassesByType(Request $request, $type=null)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }

        $with = [
            'tutor_profile'=>function($query){
                return $query->with(['video_classes'=>function($query){
                    return $query->with(['student_profile'=>function($query){
                        return $query->with(['user']);
                    }]);
                }]);
            },
            'student_profile'=>function($query){
                return $query->with(['video_classes'=>function($query){
                    return $query->with(['tutor_profile'=>function($query){
                        return $query->with(['user']);
                    }]);
                }]);
            }
        ];
        $user = User::where('id', $user->id)->with($with)->first();
        
        $video_classes = [];
        if ($type=='student' && count($user->student_profile))
            $video_classes = $user->student_profile[0]->video_classes;
        else if ($type=='tutor' && count($user->tutor_profile))
            $video_classes = $user->tutor_profile[0]->video_classes;
        else
            $video_classes = [];

        return response()->json($video_classes, $this->successStatus); 
    }

    /** GET
     * /api/videoclass/{$call_id}
     */ 
    public function getVideoClass(Request $request, $call_id) 
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $with = [
            'feedback' => function($query){
                return $query;
            },
            'student_profile' => function($query){
                return $query->with(['user'=>function($query){return $query;}]);
            }, 
            'tutor_profile' => function($query){
                return $query->with(['user'=> function($query){return $query;}]);;
            }
        ];
        $video_class_info = VideoClass::where('call_id', $call_id)->with($with)->first();
        return response()->json($video_class_info , $this->successStatus); 
    }

    /**
     * post
     * /api/videoclass
     * student request video class to tutor
     */
    public function requestVideoClass(Request $request) 
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }

        $parent = $user->student_profile[0]->parent_profile->user;
        if (!$parent->default_billing_type)
            return response()->json(new ErrorResponse("Parent has to connect more that one billing method", "400", []), $this->badRequest); 
        
        $input = json_decode($request->getContent(), true);
        $video_class = new VideoClass;
        $input['started_at'] = null;
        $input['duration'] = 0;
        $video_class->fill($input);
        $video_class->save();
        return response()->json($video_class, $this->successStatus); 
    }
    
    /** post
     * /api/user/videoclass/{video_class_id}/start
     */ 
    public function startVideoClass(Request $request, $video_class_id) 
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $video_class = VideoClass::find($video_class_id);
        if ($video_class->started_at)
            return response()->json(new ErrorResponse("this class has been already started", "400", []), $this->badRequest);
        $video_class->started_at = Carbon::now();
        $video_class->duration = 0;
        $video_class->save();

        return response()->json($video_class, $this->successStatus); 
    }

    /** get
     * /api/videoclass/{video_class_id}/end
     */ 
    public function endVideoClass(Request $request, $video_class_id) 
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized);
        }
        $video_class = VideoClass::find($video_class_id);
        
        if (!$video_class->started_at)
            return response()->json(new ErrorResponse("this class has been not started yet", "400", []), $this->badRequest);
        $video_class->ended_at = Carbon::now();
        $video_class->duration = Carbon::now()->diffInSeconds($video_class->started_at);
        $video_class->paid = false;
        $video_class->save();
        
        $parent = $video_class->student_profile->parent_profile->user;
        $price = $video_class->price->price;
        $amount = round(($price * $video_class->duration / 3600), 2);

        try {
            PaymentRepository::addTransactionHistory($video_class->tutor_profile->user_id, "Tutor Payment", $amount,  "Get Payments for {$video_class->name}");
            PaymentRepository::updateBalance($video_class->tutor_profile->user_id, 500);
            switch ($parent->default_billing_type) {
                case "credit_card":
                    $customer_id = $parent->credit_card_info->stripe_customer_id;
                    StripeService::charge($customer_id, $amount, 'usd', "Paid for {$video_class->name}");
                    break;
                case "paypal":
                    $preapprovalId = $parent->paypal_info->preapproval_id;
                    $senderEmail   = $parent->paypal_info->email;
                    $receiverEmail = "sb-hxvlp232243@business.example.com";
                    $this->paypal_provider->executeAdaptivePay($senderEmail, $receiverEmail, $preapprovalId, $amount);
                    break;
                default:
            }
            $video_class->paid = true;
            $video_class->save();
            PaymentRepository::addTransactionHistory($video_class->student_profile->parent_profile->user_id, "Tutor Payment", $amount,  "Paid for {$video_class->name}");
        } catch (Exception $e) {
            PaymentRepository::addTransactionHistory($video_class->student_profile->parent_profile->user_id, "Tutor Payment", -1 * $amount,  "Paid Failure for {$video_class->name}");
            PaymentRepository::updateBalance($video_class->student_profile->parent_profile->user_id, -1 * $amount);
            return response()->json(new ErrorResponse($e->getMessage(), "400", []), $this->badRequest);
        }
        return response()->json($video_class, $this->successStatus); 
    }

    /**  post
     * /api/videoclass/{video_class_id}/feedback
    */
    public function setFeedback(Request $request, $video_class_id)
    {
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized);
        }
        $video_class = VideoClass::find($video_class_id);
        if (!$video_class->duration)
            return response()->json(new ErrorResponse("this class has been not ended yet", "400", []), $this->badRequest);
        
        $feedback = $video_class->feedback;
        if (!$feedback) 
            $feedback = new Feedback;
        $input['video_class_id'] = $video_class_id;
        $feedback->fill($input);
        $feedback->save();
        return response()->json($feedback, $this->successStatus);
    }
}
