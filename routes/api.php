<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('github', 'API\AppCenterController@github');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('test', 'API\UserController@test');

Route::post('user/login', 'API\UserController@login');
Route::post('user/{type?}/login', 'API\UserController@login');
Route::post('user/{type}/register', 'API\UserController@createUpdateUser');

Route::post('/user/gencode', 'API\UserController@generateCode');
Route::post('/user/resetpassword', 'API\UserController@resetPassword');
Route::post('user/avatar/upload', 'API\UserController@uploadUserAvatar');
Route::post('/degree/upload', 'API\ResourceController@uploadDegree');
Route::post('/identity/upload', 'API\ResourceController@uploadIdentity');

// Address API
Route::prefix('address')->group(function(){
    Route::get('detail/{addressId}', 'API\ResourceController@getAddress');
    Route::get('city', 'API\ResourceController@getAllCities');
    Route::get('{stateId}/cities', 'API\ResourceController@getCities');
    Route::get('city/{cityId}', 'API\ResourceController@getCity');
    Route::get('state', 'API\ResourceController@getAllStates');
    Route::get('{countryId}/states', 'API\ResourceController@getStates');
    Route::get('state/{stateId}', 'API\ResourceController@getState');
    Route::get('country', 'API\ResourceController@getCountries');
    Route::get('country/{countryId}', 'API\ResourceController@getCountry');
});

// Resources API
Route::prefix('resources')->group(function() {
    Route::prefix('schoollevels')->group(function() {
        Route::get('/', 'API\ResourceController@getAllSchoolLevels');
    });
});

Route::group(['middleware' => 'auth:api'], function(){
    // User API
    Route::prefix('user')->group(function(){
        Route::post('changepassword', 'API\UserController@changePassword');
        // User Setting API
        Route::get('setting', 'API\UserController@getUserSetting');
        Route::post('setting', 'API\UserController@setUserSetting');

        Route::get('{type}/topic', 'API\UserController@getUserTopic');
        Route::post('{type}/topic', 'API\UserController@createUpdateUserTopic');
        
        Route::put('{type}', 'API\UserController@createUpdateUser');
        Route::get('{type}', 'API\UserController@getUser');
    });

    // Student API
    Route::prefix('user/student')->group(function(){
        // Student School
        Route::prefix('school')->group(function(){
            Route::get('/', 'API\StudentController@getStudentSchool');
            Route::post('/', 'API\StudentController@createUpdateStudentSchool');
            Route::delete('/', 'API\StudentController@deleteStudentSchool');
        });
        // Student Video Class
        Route::prefix('videoclass')->group(function(){
            Route::get('/', 'API\StudentController@getStudentVideoClasses');
        });
    });
    // Tutor API
    Route::prefix('user/tutor')->group(function(){
        Route::post('/list', "API\TutorController@getTutorList");
        // Tutor Degree
        Route::prefix('degree')->group(function(){
            Route::get('/', 'API\TutorController@getDegrees');
            Route::post('/', 'API\TutorController@attachDegree');
            Route::delete('/', 'API\TutorController@detachDegrees');
        });
        // Tutor Price
        Route::prefix('price')->group(function(){
            Route::get('/', 'API\TutorController@getPrices');
            Route::post('/', 'API\TutorController@attachPrices');
            Route::put('/', 'API\TutorController@reAttachPrices');
            Route::delete('/', 'API\TutorController@detachPrices');
        });
        // Tutor Identity
        Route::prefix('identity')->group(function(){
            Route::get('/', 'API\TutorController@getIdentity');
            Route::post('/', 'API\TutorController@setIdentity');
        });
        // Tutor W9Form
        Route::prefix('w9form')->group(function(){
            Route::get('/', 'API\TutorController@getW9Form');
            Route::post('/', 'API\TutorController@setW9Form');
        });
        // Tutor Video Class
        Route::prefix('videoclass')->group(function(){
            Route::get('/', 'API\TutorController@getTutorVideoClasses');
            Route::post('/', 'API\TutorController@createTutorVideoClass');
        });
    });

    // Video Class API
    Route::prefix('videoclass')->group(function(){
        Route::post('/request', 'API\VideoClassController@requestVideoClass');
        Route::post('{video_class_id}/start', 'API\VideoClassController@startVideoClass');
        Route::post('{video_class_id}/end', 'API\VideoClassController@endVideoClass');
        Route::post('{video_class_id}/feedback', 'API\VideoClassController@setFeedback');
        Route::get('{type}/list', 'API\VideoClassController@getVideoClassesByType');
        Route::get('{call_id}', 'API\VideoClassController@getVideoClass');
    });
    
    // Degree API
    Route::prefix('degree')->group(function(){
        Route::put('{id}', 'API\ResourceController@updateDegree');
    });

    Route::prefix('basicdegree')->group(function(){
        Route::get('/', 'API\ResourceController@getBasicDegrees');
    });

    // Price API
    Route::prefix('price')->group(function(){
        Route::get('/', 'API\ResourceController@getPrices');
        Route::get('{priceId}', 'API\ResourceController@getProce');
    });

    // Topic API
    Route::prefix('topic')->group(function(){
        Route::get('/', 'API\ResourceController@getTopics');
        Route::get('{topicId}', 'API\ResourceController@getTopic');
    });

    // Minor API
    Route::prefix('minor')->group(function(){
        Route::get('/', 'API\ResourceController@getMinors');
        Route::get('{minorId}', 'API\ResourceController@getMinor');
    });

    // Payment API
    Route::prefix('payment')->group(function(){
        
        // Billing Method by Stripe
        Route::prefix('stripe')->group(function(){
            Route::get('/', 'API\PaymentController@getStripeInfo');
            Route::post('create', 'API\PaymentController@createStripeCustomer');
            Route::delete('remove', 'API\PaymentController@removeStripeCustomer');
            Route::post('charge', 'API\PaymentController@chargeStripePayment');
        });

        // Withdrawable Method by Paypal
        Route::prefix('paypal')->group(function(){
            Route::post('setup', 'API\PaymentController@setupPaypal'); // Setup paypal for withdraw
            Route::post('withdraw', 'API\PaymentController@withdrawToPayPal'); // Withdraw from balance
            Route::post('requestPreApproval', 'API\PaymentController@requestPreApproval'); // request pre approval
            Route::post('activePreApproval', 'API\PaymentController@activePreApproval'); // active pre approval
            Route::get('test', 'API\PaymentController@test');
            Route::post('charge', 'API\PaymentController@chargePaypalPayment');
        });
    });
});