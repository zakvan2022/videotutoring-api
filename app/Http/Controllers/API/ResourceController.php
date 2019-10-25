<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller; 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

use App\Classes\PropertySuccessResponse;
use App\Classes\SuccessResponse;
use App\Classes\PropertyErrorResponse;
use App\Classes\ErrorResponse;

use App\Models\Price;
use App\Models\Topic;
use App\Models\Minor;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\Degree;
use App\Models\BasicDegree;
use App\Models\Identity;
use App\Models\SchoolLevel;
use Storage;

class ResourceController extends Controller
{
    /** PUT
     * /api/degree/{degreeId}
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function updateDegree(Request $request, $degreeId) 
    {
        $input = json_decode($request->getContent(), true);
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        $degree = Degree::find($degreeId);
        if(!$degree){
            return response()->json(new ErrorResponse("Degree not found", "400", [
                new PropertyErrorResponse("Invalid Degree ID", "id")
            ]), $this->badRequest);
        }
        $degree->fill($input);
        $degree->save();
        return response()->json(new SuccessResponse("Success", "200", []), $this->successStatus); 
    }

    /** POST
     * /api/degree/upload
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function uploadDegree(Request $request)
    {
        $file = $request->file('request');
        $folder = "degree";
        $filename = time()."-".$file->getClientOriginalName();
        $file->storeAs($folder, $filename, "public");
        /*
        $filename = '/degree/'.time();
        Storage::disk('public')->put($filename, $request->getContent());
        */
        $public_url = Storage::disk('public')->url("/".$folder."/".$filename);
        $degree = new Degree;
        $degree->fill([
            "upload_url" => $public_url
        ]);
        $degree->save();
        return response()->json($degree, $this->successStatus); 
    }

    /**
     * /api/basicdegree
    */
    public function getBasicDegrees(Request $request) {
        return response()->json(BasicDegree::all(), $this->successStatus);
    }

    /** POST
     * /api/identity/upload
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function uploadIdentity(Request $request)
    {
        $file = $request->file('request');
        $folder = "identity";
        $filename = time()."-".$file->getClientOriginalName();
        $file->storeAs($folder, $filename, "public");
        /*
        $filename = '/identity/'.time();
        Storage::disk('public')->put($filename, $request->getContent());
        $public_url = Storage::disk('public')->url($filename);
        */
        $public_url = Storage::disk('public')->url("/".$folder."/".$filename);

        $identity = new Identity;
        $identity->fill([
            "upload_url" => $public_url
        ]);
        $identity->save();
        return response()->json($identity, $this->successStatus); 
    }

    /** GET
     * /api/price
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getPrices(Request $request) 
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json(Price::get(), $this->successStatus); 
    }

    /** GET
     * /api/price/priceId
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getPrice(Request $request, $priceId)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json(Price::find($priceId), $this->successStatus); 
    }

    /** GET
     * /api/topic
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getTopics(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json(Topic::get(), $this->successStatus); 
    }

    /** GET
     * /api/topic/topicId
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getTopic(Request $request, $topicId)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json(Topic::find($topicId), $this->successStatus); 
    }

    /** GET
     * /api/minor
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getMinors(Request $request)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json(Minor::get(), $this->successStatus); 
    }

    /** GET
     * /api/minor/{minorId}
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getMinor(Request $request, $minorId)
    {
        $user = Auth::user();
        if(!$user){
            return response()->json([], $this->unAuthorized); 
        }
        return response()->json(Minor::find($minorId), $this->successStatus); 
    }

    /** GET
     * /api/address/{addressId}
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getAddress(Request $request, $addressId)
    {
        return response()->json(Address::find($addressId), $this->successStatus); 
    }

    /** GET
     * /api/address/city
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getAllCities(Request $request)
    {
        return response()->json(City::all(), $this->successStatus); 
    }

    /** GET
     * /api/address/{$state_id}/cities
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getCities(Request $request, $stateId)
    {
        return response()->json(City::where('state_id', $stateId)->with(['state'])->get(), $this->successStatus); 
    }

    /** GET
     * /api/city/{cityId}
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getCity(Request $request, $cityId)
    {
        return response()->json(City::find($cityId), $this->successStatus); 
    }

    /** GET
     * /api/address/state
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getAllStates(Request $request)
    {
        return response()->json(State::get(), $this->successStatus); 
    }

    /** GET
     * /api/address/{countryId}/states
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getStates(Request $request, $countryId)
    {
        return response()->json(State::where('country_id', $countryId)->with(['country'])->get(), $this->successStatus);
    }

    /** GET
     * /api/state/{stateId}
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getState(Request $request, $stateId)
    {
        return response()->json(State::find($stateId), $this->successStatus); 
    }

    /** GET
     * /api/country
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getCountries(Request $request)
    {
        return response()->json(Country::get(), $this->successStatus); 
    }

    /** GET
     * /api/country/{countryId}
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function getCountry(Request $request, $countryId)
    {
        return response()->json(Country::find($countryId), $this->successStatus); 
    }

    /**
     * GET
     * /api/resources/schoollevels
     */
    public function getAllSchoolLevels(Request $request)
    {
        return response()->json(SchoolLevel::get(), $this->successStatus);
    }
}
