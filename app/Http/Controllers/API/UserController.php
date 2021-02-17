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
use App\MatchRace;
use App\MatchResult;
use Illuminate\Support\Facades\Password;
use stdClass;

class UserController extends Controller
{
    public function login(Request $request){
        $x = new \stdClass();
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = auth()->user();
            $user->device_token = $request->input('device_token', '');
            $user->api_token = Str::random(60);
            $user->save();
            if($user->image != "")
            {
                $user->image = url('images').'/'.$user->image;
            }
            return response()->json(['success' => true,'data'=>$user,'message'=>'Login Successfully'], 200);
        }
        else{
            return response()->json(['success'=>false,'data'=>$x,'message'=>'Email and password Mismatched..'], 401);
        }
    }

    public function register(Request $request)
    {
        $x = new \stdClass();
        $arr_rules['email']         = "required|string|max:255|email|";
        $arr_rules['password']      = "required|string|min:6";
        $arr_rules['mobile']      = "required";
        $validator = Validator::make($request->all(), $arr_rules);
        if ($validator->fails())
        {
            return response()->json(['success'=>false,'data'=>$x,'message'=>$validator->errors()->first()], 401);
        }else{
            $checked = User::where('email',$request->input('email'))->first();
            if(isset($checked->id))
            {
                return response()->json(['success'=>false,'data'=>$x,'message'=>'user email already exist'], 401);
            }else{
                $user = new User;
                $user->email = $request->input('email');
                $user->password = Hash::make($request->input('password'));
                $user->api_token = Str::random(60);
                if($request->has('address'))
                {
                    $user->address = $request->input('address');
                }else{
                    $user->address = "";
                }
                $user->device_token = $request->input('device_token','');
                $user->mobile = $request->input('mobile');
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $name);
                    $user->image = $name;
                }
                if ($request->hasFile('image1')) {
                    $image = $request->file('image1');
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $name);
                    $user->image1 = $name;
                }
                if ($request->hasFile('image2')) {
                    $image = $request->file('image2');
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = public_path('/images');
                    $image->move($destinationPath, $name);
                    $user->image2 = $name;
                }
                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();
                return response()->json(['success' => true,'data'=>$user,'message'=>'User Registration Successfully'], 200);
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
