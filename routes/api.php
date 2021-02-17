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

    Route::post('profile/uploadItem',[App\Http\Controllers\API\ProfileController::class, 'uploadItem']);
    Route::get('profile/getUploadItem',[App\Http\Controllers\API\ProfileController::class, 'getUploadItem']);
    Route::get('profile/getOtherUploadItem',[App\Http\Controllers\API\ProfileController::class, 'getOtherUploadItem']);



});
