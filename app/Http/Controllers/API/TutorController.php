<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
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

use App\Classes\PropertySuccessResponse;
use App\Classes\SuccessResponse;
use App\Classes\PropertyErrorResponse;
use App\Classes\ErrorResponse;

use Illuminate\Support\Facades\Auth; 
use Validator;

class TutorController extends Controller
{
    /**
     * /api/user/tutor/list
     * search tutor profiles by using degrees, prices
     * @return \Illuminate\Http\Response
     */
    public function getTutorList(Request $request) {
        $input = json_decode($request->getContent(), true);

        // get tutor profile id list by using degrees
        if (isset($input['degree_id']) && $input['degree_id']!==null)
            $tutor_profile_by_degrees = Degree::where('basic_degree_id', $input['degree_id'])->get();
        else
            $tutor_profile_by_degrees = Degree::all();
        $tutor_ids_by_degrees = [];
        foreach ($tutor_profile_by_degrees as $item) {
            array_push($tutor_ids_by_degrees, $item->tutor_profile_id);
        }

        // get tutor profile id list by using prices
        if (isset($input['price_id']) && $input['price_id']!=null) {
            $tutor_profiles_by_prices = Price::where('id', $input['price_id'])->first()->tutor_profiles;
        } else {
            $tutor_profiles_by_prices = TutorProfile::all();
        }
        $tutor_ids_by_prices = [];
        foreach ($tutor_profiles_by_prices as $item) {
            array_push($tutor_ids_by_prices, $item->id);
        }

        return response()->json(TutorProfile::with('user')->whereIn('id', $tutor_ids_by_degrees)->whereIn('id', $tutor_ids_by_prices)->get(), $this->successStatus);
    }

    /** GET
     * /api/user/tutor/degree
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getDegrees(Request $request) 
    {
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $tutor_profile = TutorProfile::with([
            "degrees" => function($query){
                $query->with(["school", "basic_degree"]);
            }
        ])->where('user_id', $user->id)->first();
        return response()->json($tutor_profile->degrees, $this->successStatus); 
    }

    /** POST
     * /api/user/tutor/degree
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function attachDegree(Request $request)
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        
        if (!isset($input['school']['name'])) {
            return response()->json(new ErrorResponse("school name is required", "400", []), $this->badRequest);
        } else {
            $school = School::firstOrCreate([
                "name" => $input['school']['name']
            ]);
            $input['school_id'] = $school->id;
        }

        if (isset($input['id']))
            $degree = Degree::firstOrNew([
                "id" => $input['id']
            ]);
        else
            $degree = new Degree();
        
        $degree->fill($input);
        $degree->tutor_profile_id = $user->tutor_profile->first()->id;
        $degree->save();

        return response()->json($degree, $this->successStatus);
    }

    /** DELETE
     * /api/user/tutor/degree
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function detachDegrees(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $degree_ids = $input["degree_ids"];
        Degree::where("tutor_profile_id", $user->tutor_profile->first()->id)->whereIn("id", $degree_ids)->update(["tutor_profile_id"=>null]);
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }

    /** GET
     * /api/user/tutor/price
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getPrices(Request $request) 
    { 
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json($user->tutor_profile->first()->prices, $this->successStatus); 
    }

    /** POST
     * /api/user/tutor/price
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function attachPrices(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $price_ids = $input["price_ids"];
        $user->tutor_profile->first()->prices()->attach(Price::find($price_ids));
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }
    
    /** PUT
     * /api/user/tutor/price
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function reAttachPrices(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $price_ids = $input["price_ids"];
        $tutor_profile = $user->tutor_profile->first();
        $tutor_profile->prices()->detach();
        $tutor_profile->prices()->attach(Price::find($price_ids));
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }

    /** DELETE
     * /api/user/tutor/price
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function detachPrices(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $price_ids = $input["price_ids"];
        $tutor_profile = $user->tutor_profile->first();
        $tutor_profile->prices()->detach();
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }

    /** GET
     * /api/user/tutor/identity
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getIdentity(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json($user->tutor_profile->first()->identity, $this->successStatus); 
    }

    /** POST
     * /api/user/tutor/identity
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function setIdentity(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $identity = Identity::find($input['id']);
        if(!$identity){
            return response()->json(new ErrorResponse("Identity not found", "400", [
                new PropertyErrorResponse("Invalid Identity ID", "id")
            ]), $this->badRequest);
        }
        $tutor_profile = $user->tutor_profile->first();
        $tutor_profile->identity_id = $identity->id;
        $tutor_profile->save();
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }

    /** GET
     * /api/user/tutor/w9form
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getW9Form(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json($user->tutor_profile->first()->w9form, $this->successStatus); 
    }

    /** POST
     * /api/user/tutor/w9form
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function setW9Form(Request $request) 
    { 
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $w9form = W9Form::find($input['id']);
        if(!$w9form){
            return response()->json(new ErrorResponse("W9Form not found", "400", [
                new PropertyErrorResponse("Invalid W9Form ID", "id")
            ]), $this->badRequest);
        }
        $tutor_profile = $user->tutor_profile->first();
        $tutor_profile->w9form_id = $w9form->id;
        $tutor_profile->save();
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }

    /** GET
     * /api/user/tutor/videoclass
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getTutorVideoClasses(Request $request) 
    {
        $user = User::with([
            "tutor_profile" => function($query){
                $query->with([
                    "video_classes" => function($query){
                        $query->with('student_profiles');
                    }
                ]);
            }
        ])->find(Auth::id());
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json($user->tutor_profile->first()->video_classes, $this->successStatus); 
    }

    /** POST
     * /api/user/tutor/videoclass
     * 
     * create Video Class and return
     */ 
    // public function createTutorVideoClass(Request $request) 
    // {
    //     $user = Auth::user();
    //     if(!$user){
    //         return response()->json([], $this->unAuthorized); 
    //     }
    //     $input = json_decode($request->getContent(), true);
    //     $video_class = new VideoClass;
    //     $video_class->fill($input);
    //     // $video_class->tutor_profile_id = $user->tutor_profile->first()->id;
    //     $video_class->save();
    //     if(isset($input["student_profiles"])){
    //         $video_class->student_profiles()->attach($input["student_profiles"][0]["id"]);
    //     }
    //     return response()->json($video_class, $this->successStatus); 
    // }
}
