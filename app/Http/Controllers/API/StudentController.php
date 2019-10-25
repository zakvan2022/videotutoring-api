<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User;
use App\Models\TutorProfile;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\UserSetting;

use App\Models\School;
use App\Models\Topic;
use App\Models\Address;

use App\Classes\PropertySuccessResponse;
use App\Classes\SuccessResponse;
use App\Classes\PropertyErrorResponse;
use App\Classes\ErrorResponse;

use Illuminate\Support\Facades\Auth; 
use Validator;

class StudentController extends Controller
{
    /** GET
     * /api/user/student/school api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getStudentVideoClasses(Request $request){
        $user = User::with([
            "student_profile" => function($query){
                $query->with([
                    "video_classes" => function($query){
                        $query->with('tutor_profile');
                    }
                ]);
            }
        ])->find(Auth::id());
        return response()->json($user->student_profile->first()->video_classes, $this->successStatus); 
    }

    /** GET
     * /api/user/student/school api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getStudentSchool(Request $request){
        $user = Auth::user();
        return response()->json(School::with(['address'])->find($user->student_profile->first()->school_id), $this->successStatus); 
    }

    /** POST
     * /api/user/student/school api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function createUpdateStudentSchool(Request $request){
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        $student_profile = $user->student_profile->first();
        $school = $student_profile->school;
        if(!$school){
            $school = new School;
        }
        $school->fill($input);
        $school->save();
        $address = isset($school->address) ? $school->address : null;
        if(!$address){
            $address = new Address;
        }
        $address->fill($input["address"]);
        $address->save();
        return response()->json(School::with('address')->find($address->id), $this->successStatus); 
    }
    /** DELETE
     * /api/user/student/school api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function deleteStudentSchool(Request $request){
        $user = Auth::user();
        $student_profile = $user->student_profile->first();
        $school = $student_profile->school;
        if($school){
            $school->delete();
        }
        return response()->json("success", $this->successStatus); 
    }
}
