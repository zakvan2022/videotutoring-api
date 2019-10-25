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
use App\Models\City;
use App\Models\Degree;
use App\Models\Identity;
use App\Models\VerificationCode;
use App\Models\Balance;

use App\Classes\PropertySuccessResponse;
use App\Classes\SuccessResponse;
use App\Classes\PropertyErrorResponse;
use App\Classes\ErrorResponse;

use App\Utils\StringUtils;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Validator;
use Storage;
use Mail;

class UserController extends Controller 
{    
    public function test(Request $request){
        return response()->json( json_decode($request), $this->successStatus);
    }
    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(Request $request, $type=null){ 
        $input = json_decode($request->getContent());
        if(Auth::attempt(['email' => $input->email, 'password' => $input->password])){ 
            $with = $type."_profile";
            if($type == null) $with = ['parent_profile', 'student_profile', 'tutor_profile'];
            $with = [
                'balance' => function($query){
                    return $query;
                },
                'address' => function($query){
                    return $query;
                }, 
                'user_setting' => function($query){
                    return $query;
                }, 
                'parent_profile' => function($query){
                    return $query;
                }, 
                'student_profile' => function($query){
                    return $query->with(['school'=> function($query){return $query->with(['schoollevel', 'address']);}]);
                }, 
                'tutor_profile' => function($query){
                    return $query->with(['degrees', 'identity']);
                }
            ];
            $user = User::where('email', $input->email)->with($with)->first(); 
            $success['token'] =  $user->createToken('VideoTutoring')->accessToken; 
            $success['user'] = $user;
            return response()->json($success, $this->successStatus); 
        } else { 
            return response()->json(new ErrorResponse("Email or password were not matched", "401", []), $this->unAuthorized);
        } 
    }
    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function createUpdateUser(Request $request, $type)
    { 
        $input = json_decode($request->getContent(), true);
        $validator = Validator::make($input, [ 
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email'
        ]);
        
        if ($validator->fails()) { 
            return response()->json(new ErrorResponse("FirstName, LastName, Email must be required", "400", []), $this->badRequest);
        }
        $user = Auth::user();
        if(!$user){

            $user = User::where('email', $input['email'])->first();
            if ($user) {
                return response()->json(new ErrorResponse("This email has been already registered", "400", []), $this->badRequest);
            }
            $user = User::firstOrNew([
                "email" => $input['email']
            ]);
        }
        
        if (isset($input['password']) && $input['password'] !== null)
            $input['password'] = bcrypt($input['password']);
        
        $address = $user->address;
        if(!$address){
            $address = new Address;
        }
        
        if (isset($input['address'])) {
            if (isset($input['address']['city'])){
                if ($address->city_id)
                    $city = City::firstOrNew(["id" => $address->city_id]);
                else 
                    $city = new City;
                $city->fill($input['address']['city']);
                $city->save();
                $input['address']['city_id'] = $city->id;
            }
            $address->fill($input['address']);
            $address->save();
            $input["address_id"]=$address->id;
        }

        $user_setting = UserSetting::firstOrCreate([
            "notification" => $input['user_setting']['notification']
        ]);
        $input["user_setting_id"]=$user_setting->id;
        $user->fill($input);
        $user->save();
        $balance = new Balance();
        $balance->fill(['user_id'=>$user->id, 'amount'=>0.0]);
        $balance->save();
        if($type == "tutor"){
            $tutor_profile = TutorProfile::firstOrNew([
                "user_id" => $user->id
            ]);
            $input['tutor_profile'][0]['user_id'] = $user->id;
            $tutor_profile->fill( $input['tutor_profile'][0] );

            if (isset(['tutor_profile'][0]['degrees']) && ['tutor_profile'][0]['degrees'] !== null) {
                $degree = Degree::firstOrNew([
                    "id" => $input['tutor_profile'][0]['degrees'][0]['id']
                ]);
                if(!$degree){
                    $degree = new Degree;
                }
                $degree->fill($input['tutor_profile'][0]['degrees'][0]);
                $degree->tutor_profile_id = $tutor_profile->id;
                $degree->save();
            }

            $identity = Identity::firstOrNew([
                "id" => $tutor_profile->identity_id
            ]);
            $identity->fill( $input['tutor_profile'][0]['identity'] );
            $identity->save();
            $tutor_profile->identity_id = $identity->id;
            $tutor_profile->save();
        }
        elseif($type == "parent"){
            $parent_profile = ParentProfile::firstOrNew([
                "user_id" => $user->id
            ]);
            $input['parent_profile'][0]['user_id'] = $user->id;
            $parent_profile->fill( $input['parent_profile'][0] );
            $parent_profile->save();
        }
        elseif($type == "student"){
            $student_profile = StudentProfile::firstOrNew([
                "user_id" => $user->id
            ]);
            
            if ($student_profile->school_id)
                $school = School::firstOrNew([
                    "id" => $student_profile->school_id
                ]);
            else
                $school = new School;
            
            if ($school->address_id)
                $address = Address::firstOrNew([
                    "id" => $school->address_id
                ]);
            else
                $address = new Address;

            if ($address->city_id)
                $city = City::firstOrNew([
                    "id" => $address->city_id
                ]);
            else
                $city = new City;

            $city->fill( $input['student_profile'][0]['school']['address']['city'] );
            $city->save();
            $address->fill( $input['student_profile'][0]['school']['address'] );
            $address->city_id = $city->id;
            $address->save();
            $school->fill( $input['student_profile'][0]['school'] );
            $school->address_id = $address->id;
            $school->save();
            
            $input['student_profile'][0]['user_id'] = $user->id;
            $student_profile->fill( $input['student_profile'][0] );
            $student_profile->school_id = $school->id;
            $student_profile->user_id = $user->id;
            $student_profile->save();
        }
        $with = [
            'balance' => function($query){
                return $query;
            },
            'address' => function($query){
                return $query;
            }, 
            'user_setting' => function($query){
                return $query;
            }, 
            'parent_profile' => function($query){
                return $query;
            }, 
            'student_profile' => function($query){
                return $query->with(['school'=> function($query){return $query->with(['schoollevel', 'address']);}]);
            }, 
            'tutor_profile' => function($query){
                return $query->with(['degrees', 'identity']);
            }
        ];
        $user = User::where('email', $input['email'])->with($with)->first();
        $success['token'] =  $user->createToken('VideoTutoring')->accessToken;
        $success['user'] = $user;
        return response()->json($success, $this->successStatus); 
    }

    /** POST
     * /api/user/avatar/upload
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function uploadUserAvatar(Request $request)
    {
        $file = $request->file('request');
        $folder = "avatar";
        $filename = time()."-".$file->getClientOriginalName();
        $file->storeAs($folder, $filename, "public");
        $public_url = Storage::disk('public')->url("/".$folder."/".$filename);
        $success['uploaded_url'] = $public_url;
        return response()->json($success, $this->successStatus);
    }

     /** POST
     * /api/user/changepassword
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function changePassword(Request $request) {
        $user = Auth::user();
        $input = json_decode($request->getContent(), true);
        if($input == null) $input = [];
        $validator = Validator::make($input, [ 
            'oldpassword' => 'required',
            'newpassword' => 'required', 
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->badRequest);
        }
        if (Hash::check($input['oldpassword'], $user['password'])) {
            $user->password = bcrypt($input['newpassword']); 
            $user->save();
            return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus);
        } else {
            return response()->json(new ErrorResponse("old password is incorrected", "400", []), $this->serverError);
        }
    }

    /** POST
     * /api/user/gencode
     * @return \Illuminate\Http\Response
     */
    public function generateCode(Request $request) {
        $input = json_decode($request->getContent(), true);
        $email = $input['email'];
        $code = StringUtils::generateRandomCode(6);
        
        $data['title']="Your verfication code";
        $data['code']=$code;
 
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(new ErrorResponse("This email was not registered yet", "500", []), $this->successStatus);
        }
        $verification_code = $user->verification_code;
        if (!$verification_code) $verification_code = new VerificationCode();
        
        $verification_code->fill([
            "code" => $code,
            "user_id" => $user->id
        ]);
        $verification_code->save();

        Mail::send('emails.code', $data, function($message) use ($email, $user) {
            $message->to($email, $user->first_name." ".$user->last_name)->subject('Forgot Password');
        });
        if (Mail::failures()) {
            return response()->json(new ErrorResponse("Verification code was not sent, please confirm if your email is valid", "500", []), $this->successStatus);
        } else {
            return response()->json(new SuccessResponse("Verification code is sent successfully", "200", []), $this->successStatus);
        }
    }
     /** POST
     * /api/user/resetpassword
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function resetPassword(Request $request) {
        $input = json_decode($request->getContent(), true);

        $validator = Validator::make($input, [ 
            'email' => 'required',
            'verification_code' => 'required',
            'password'=>'required'
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], $this->badRequest);
        }
        $user = User::where('email', $input['email'])->first();
        if (!$user) {
            return response()->json(new ErrorResponse("This email was not registered yet", "500", []), $this->successStatus);
        }

        $verification_code = $user->verification_code;
        if (!$verification_code) {
            return response()->json(new ErrorResponse("You must ask verification code", "500", []), $this->successStatus);
        }
        if ($input['verification_code']!=$verification_code->code) {
            return response()->json(new ErrorResponse("Verification code is wrong, Try again", "500", []), $this->successStatus);
        }
        $user->password = bcrypt($input['password']); 
        $user->save();
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus);
    }
    /** GET
     * /api/user/setting api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getUserSetting(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $user_setting = $user->user_setting;
        if( empty($user->user_setting) ){
            $user_setting = new UserSetting;
            $user_setting->fill([
                "notification" => true
            ]);
            $user_setting->save();
            $user->update(["user_setting_id"=>$user_setting->id]);
        }
        return response()->json($user_setting, $this->successStatus); 
    } 

    /** POST
     * /api/user/setting api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function setUserSetting(Request $request)
    {
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $user_setting = $user->user_setting;
        if( empty($user->user_setting) ){
            $user_setting = new UserSetting;
            $user_setting->fill([
                "notification" => true
            ]);
            $user_setting->save();
            $user->update(["user_setting_id"=>$user_setting->id]);
        }
        $user_setting->fill($input);
        $user_setting->save();
        return response()->json($user->user_setting, $this->successStatus); 
    } 

    /** GET
     * /api/user/{type} api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getUser(Request $request, $account_type) 
    { 
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        if($account_type == 'tutor'){
            $user->tutor_profile = $user->tutor_profile->first();
        }
        else if($account_type == 'parent'){
            $user->parent_profile = $user->parent_profile->first();
        }
        else if($account_type == 'student'){
            $user->student_profile = $user->student_profile->first();
        }
        return response()->json($user, $this->successStatus); 
    } 

    /** GET
     * /api/user/{type}/topic api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getUserTopic(Request $request, $account_type){
        $user = Auth::user();
        return response()->json($user->{$account_type."_profile"}->first()->topics, $this->successStatus); 
    }

    /** POST
     * /api/user/{type}/topic api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function createUpdateUserTopic(Request $request, $account_type){
        $user = Auth::user();
        $input = json_decode($request->getContent(), true);
        $topic_ids = $input["topic_ids"];
        $user->{$account_type."_profile"}->first()->topics()->detach();
        $user->{$account_type."_profile"}->first()->topics()->attach(Topic::find($topic_ids));
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus);
    }
}