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
Route::post('login', [App\Http\Controllers\API\UserController::class, 'login']);
Route::post('register', [App\Http\Controllers\API\UserController::class, 'register']);
Route::post('forgot-password', [App\Http\Controllers\API\UserController::class, 'forgot_password']);

/* end */

Route::post('test', [App\Http\Controllers\API\UserController::class, 'test']);

Route::middleware(['ApiUserCheck'])->group(function () {
    Route::post('profile',[App\Http\Controllers\API\ProfileController::class, 'index']);
    Route::post('profile/update',[App\Http\Controllers\API\ProfileController::class, 'update']);
    
    Route::post('profile/updateDriverStatus',[App\Http\Controllers\API\ProfileController::class, 'updateDriverStatus']);
    
    Route::get('profile/getNearByDrivers',[App\Http\Controllers\API\ProfileController::class, 'getNearByDrivers']);
    
    Route::post('profile/updateLatLng',[App\Http\Controllers\API\ProfileController::class, 'updateLatLng']);

    Route::post('profile/uploadItem',[App\Http\Controllers\API\ProfileController::class, 'uploadItem']);
    
    Route::post('profile/updateNewAddedStatus',[App\Http\Controllers\API\ProfileController::class, 'updateNewAddedStatus']);
    
    Route::get('profile/getUploadItem',[App\Http\Controllers\API\ProfileController::class, 'getUploadItem']);
    Route::post('profile/updateItemTag',[App\Http\Controllers\API\ProfileController::class, 'updateItemTag']);

    Route::post('profile/doEnquiry',[App\Http\Controllers\API\ProfileController::class, 'doEnquiry']);

    Route::post('profile/getEnquiry',[App\Http\Controllers\API\ProfileController::class, 'getEnquiry']);

    Route::post('profile/updateEnquiryStatus',[App\Http\Controllers\API\ProfileController::class, 'updateEnquiryStatus']);
    
    Route::post('profile/updateEnquiryAcceptOrRejectStatus',[App\Http\Controllers\API\ProfileController::class, 'updateEnquiryAcceptOrRejectStatus']);
    
    Route::post('profile/getEnquiryDetail',[App\Http\Controllers\API\ProfileController::class, 'getEnquiryDetail']);

    Route::post('profile/updateEnquiryDeliveryStatus',[App\Http\Controllers\API\ProfileController::class, 'updateEnquiryDeliveryStatus']);
    
    Route::post('profile/addDriver',[App\Http\Controllers\API\ProfileController::class, 'addDriver']);
    
    Route::post('profile/driverSelection',[App\Http\Controllers\API\ProfileController::class, 'driverSelection']);

    Route::get('profile/getOtherUploadItem',[App\Http\Controllers\API\ProfileController::class, 'getOtherUploadItem']);

    Route::get('profile/getOrderListing',[App\Http\Controllers\API\ProfileController::class, 'getOrderListing']);

});
