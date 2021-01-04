<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Car;
use App\Follow;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    //
    public function login(Request $request){
        $x = new \stdClass();
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = auth()->user();
            $user->device_token = $request->input('device_token', '');
            $user->api_token = Str::random(60);
            $user->save();

            $user->follower_count = Follow::where('following_id',$user->id)->count();
            $user->following_count = Follow::where('follower_id',$user->id)->count();
            $user->win_count = 0;
            $user->loss_count = 0;
            $carList = Car::where('user_id',$user->id)->get();
            $user->carList = $carList;

            return response()->json(['success' => true,'data'=>$user,'message'=>'Login Successfully'], 200);
        }
        else{
            return response()->json(['success'=>false,'data'=>$x,'message'=>'Unauthorised'], 401);
        }
    }

    public function register(Request $request)
    {
        $x = new \stdClass();
        $arr_rules['first_name']          = "required|string|max:255";
        $arr_rules['last_name']          = "required|string|max:255";
        $arr_rules['email']         = "required|string|max:255|email|";
        $arr_rules['password']      = "required|string|min:6";
        $arr_rules['racername']      = "required|string|min:6";
        $validator = Validator::make($request->all(), $arr_rules);
        if ($validator->fails())
        {
            return response()->json(['success'=>false,'data'=>$x,'message'=>$validator->errors()->first()], 401);
        }else{
            $check = User::where('racername',$request->input('racername'))->first();
            if(isset($check->id))
            {
                return response()->json(['success'=>false,'data'=>$x,'message'=>'racername already exist'], 401);
            }else{
                $check = User::where('email',$request->input('email'))->first();
                if(isset($check->id))
                {
                    return response()->json(['success'=>false,'data'=>$x,'message'=>'user email already exist'], 401);
                }else{
                    $user = new User;
                    $user->first_name = $request->input('first_name');
                    $user->last_name = $request->input('last_name');
                    $user->email = $request->input('email');
                    $user->password = Hash::make($request->input('password'));
                    $user->api_token = Str::random(60);
                    if($request->has('address'))
                    {
                        $user->address = $request->input('address');
                    }
                    $user->racername = $request->input('racername');
                    $user->zipcode = $request->input('zipcode');
                    $user->image = "";
                    $user->email_verified_at = date('Y-m-d H:i:s');
                    $user->save();
                    return response()->json(['success' => true,'data'=>$user,'message'=>'User Registration Successfully'], 200);
                }
            }
        }
    }

    public function forgot_password(Request $request)
    {
        $credentials = request()->validate(['email' => 'required|email']);
        Password::sendResetLink($credentials);
        return response()->json(["msg" => 'Reset password link sent on your email id.']);
    }

}
