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

Route::middleware(['ApiUserCheck'])->group(function () {
    Route::post('profile',[App\Http\Controllers\API\ProfileController::class, 'index']);
    Route::post('profile/update',[App\Http\Controllers\API\ProfileController::class, 'update']);
    Route::get('car/list',[App\Http\Controllers\API\ProfileController::class, 'carList']);
    Route::post('storeCar',[App\Http\Controllers\API\ProfileController::class, 'storeCar']);
    Route::post('getCarDetail/{id}',[App\Http\Controllers\API\ProfileController::class, 'getCarDetail']);
    Route::post('user/followandunfollow',[App\Http\Controllers\API\ProfileController::class, 'followStatusChange']);
    Route::get('user/followerList',[App\Http\Controllers\API\ProfileController::class, 'followerList']);
    Route::get('user/followingList',[App\Http\Controllers\API\ProfileController::class, 'followingList']);
});


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
