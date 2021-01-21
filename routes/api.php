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

/* other user route */
Route::post('otherUserGetProfile', [App\Http\Controllers\API\UserController::class, 'otherUserGetProfile']);
Route::post('otherUserFollowerList', [App\Http\Controllers\API\CommonUserController::class, 'otherUserFollowerList']);
Route::post('otherUserFollowerList', [App\Http\Controllers\API\CommonUserController::class, 'otherUserFollowerList']);
/* end */

Route::post('test', [App\Http\Controllers\API\UserController::class, 'test']);

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

    Route::get('user/notificaionList',[App\Http\Controllers\API\ProfileController::class, 'notificaionList']);
    Route::post('user/changeNotificationStatus',[App\Http\Controllers\API\ProfileController::class, 'changeNotificationStatus']);
    Route::post('user/raceChallenger',[App\Http\Controllers\API\ProfileController::class, 'raceChallenger']);

    Route::get('user/getCarMake',[App\Http\Controllers\API\ProfileController::class, 'getCarMake']);
    /* update toke */
    Route::post('user/updatedeviceToken',[App\Http\Controllers\API\ProfileController::class, 'updateDeviceToken']);
    Route::post('user/audioFileUpload',[App\Http\Controllers\API\ProfileController::class, 'audioFileUpload']);

    /* win and loss api */
    Route::get('user/winList',[App\Http\Controllers\API\ProfileController::class, 'winList']);
    Route::get('user/lossList',[App\Http\Controllers\API\ProfileController::class, 'lossList']);
    Route::post('user/matchDetail',[App\Http\Controllers\API\ProfileController::class, 'matchDetail']);

    /* start race api */
    Route::post('user/startRace',[App\Http\Controllers\API\ProfileController::class, 'startRace']);

    /* nocontest list */
    Route::get('user/noContentList',[App\Http\Controllers\API\ProfileController::class, 'noContentList']);
    Route::post('user/matchStatusChange',[App\Http\Controllers\API\ProfileController::class, 'matchStatusChange']);

});
Route::post('user/searchUserUsingRacerName',[App\Http\Controllers\API\CommonUserController::class, 'searchUserUsingRacerName']);
Route::post('user/searchUserUsingUserName',[App\Http\Controllers\API\CommonUserController::class, 'searchUserUsingUserName']);

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
