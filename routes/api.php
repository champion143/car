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


Route::middleware(['ApiUserCheck'])->group(function () {
    Route::post('profile',[App\Http\Controllers\API\ProfileController::class, 'index']);
    Route::post('profile/update',[App\Http\Controllers\API\ProfileController::class, 'update']);
    Route::get('car/list',[App\Http\Controllers\API\ProfileController::class, 'carList']);
    Route::post('storeCar',[App\Http\Controllers\API\ProfileController::class, 'storeCar']);
    Route::post('updateCar',[App\Http\Controllers\API\ProfileController::class, 'updateCar']);
    Route::post('getCarDetail/{id}',[App\Http\Controllers\API\ProfileController::class, 'getCarDetail']);
    Route::post('deleteCar/{id}',[App\Http\Controllers\API\ProfileController::class, 'deleteCar']);
    Route::post('user/followandunfollow',[App\Http\Controllers\API\ProfileController::class, 'followStatusChange']);
    Route::get('user/followerList',[App\Http\Controllers\API\ProfileController::class, 'followerList']);
    Route::get('user/followingList',[App\Http\Controllers\API\ProfileController::class, 'followingList']);

});
Route::post('user/searchUserUsingRacerName',[App\Http\Controllers\API\CommonUserController::class, 'searchUserUsingRacerName']);
Route::post('user/searchUserUsingUserName',[App\Http\Controllers\API\CommonUserController::class, 'searchUserUsingUserName']);

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
