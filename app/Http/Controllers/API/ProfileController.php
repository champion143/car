<?php

namespace App\Http\Controllers\API;

use App\Car;
use App\Follow;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    protected $userId;

    public function __construct(Request $request)
    {
        $x = new \stdClass();
        $headers = getallheaders();
        if(isset($headers['token']))
        {
            $check = User::where('api_token',$headers['token'])->first();
            if(!isset($check->id))
            {
                return response()->json(['success'=>false,'data'=>$x,'message'=>'token mis matched'], 401);
                die();
            }else{
                $this->userId = $check->id;
            }
        }else{
            return response()->json(['success'=>false,'data'=>array(),'message'=>'token blanked'], 401);
            die();
        }
    }
    //
    public function index(Request $request)
    {
        $x = new \stdClass();
        $userDetail = User::where('api_token',$request->header('token'))->first();
        if(isset($userDetail->id))
        {
            // $follwerList = Follow::where('following_id',$this->userId)->with('followingUser')->get();
            // $followingList = Follow::where('follower_id',$this->userId)->with('followerUser')->get();
            // $userDetail->follwerList = $follwerList;
            // $userDetail->followingList = $followingList;
            $userDetail->follower_count = Follow::where('following_id',$userDetail->id)->count();
            $userDetail->following_count = Follow::where('follower_id',$userDetail->id)->count();
            $userDetail->win_count = 0;
            $userDetail->loss_count = 0;
            if($userDetail->image != "")
            {
                $userDetail->image = url('images').'/'.$userDetail->image;
            }
            $carList = Car::where('user_id',$this->userId)->get();
            foreach($carList as $car)
            {
                if($car->image != "")
                {
                    $car->image = url('images').'/'.$car->image;
                }
            }
            $userDetail->carList = $carList;
            return response()->json(['success'=>true,'data'=>$userDetail,'message'=>'user profile get successfully'], 200);
        }else{
            return response()->json(['success'=>false,'data'=>$x,'message'=>'user not found'], 401);
        }
    }

    // update profile
    public function update(Request $request)
    {
        $userDetail = array();
        $userDetail['first_name'] = $request->input('first_name');
        $userDetail['last_name'] = $request->input('last_name');
        $userDetail['racername'] = $request->input('racername');
        $userDetail['zipcode'] = $request->input('zipcode');
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $userDetail['image'] = $name;
        }
        if($request->has('address'))
        {
            $userDetail['address'] = $request->input('address');
        }else{
            $userDetail['address'] = "";
        }
        User::where('id',$this->userId)->update($userDetail);
        $userData = User::where('id',$this->userId)->first();
        if($userData->image != "")
        {
            $userData->image = url('images').'/'.$userData->image;
        }
        return response()->json(['success'=>true,'data'=>$userData,'message'=>'User Profile Updated successfully'], 200);
    }

    // get cart list
    public function carList(Request $request)
    {
        $carList = Car::where('user_id',$this->userId)->get();
        foreach($carList as $car)
        {
            if($car->image != "")
            {
                $car->image = url('images').'/'.$car->image;
            }
        }
        return response()->json(['success'=>true,'data'=>$carList,'message'=>'Car List Retrieve successfully'], 200);
    }

    //storeCar
    public function storeCar(Request $request)
    {
        $Car = new Car;
        $Car->name = $request->input('name');
        $Car->year = $request->input('year');
        $Car->trim = $request->input('trim');
        $Car->engine = $request->input('engine');
        $Car->power = $request->input('power');
        $Car->mods = $request->input('mods');
        $Car->make = $request->input('make');
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $Car->image = $name;
        }
        $Car->user_id = $this->userId;
        $Car->save();

        if($Car->image != "")
        {
            $Car->image = url('images').'/'.$Car->image;
        }

        return response()->json(['success'=>true,'data'=>$Car,'message'=>'Item Registered successfully'], 200);
    }
    //Car details
    public function getCarDetail(Request $request,$id)
    {
        $Car = Car::where('id',$id)->first();
        return response()->json(['success'=>true,'data'=>$Car,'message'=>'Item Registered successfully'], 200);
    }
    // update car
    public function updateCar(Request $request)
    {
        $userDetail = array();
        $userDetail['name'] = $request->input('name');
        $userDetail['year'] = $request->input('year');
        $userDetail['trim'] = $request->input('trim');
        $userDetail['engine'] = $request->input('engine');
        $userDetail['power'] = $request->input('power');
        $userDetail['mods'] = $request->input('mods');
        $userDetail['make'] = $request->input('make');
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $userDetail['image'] = $name;
        }
        Car::where('id',$request->input('id'))->update($userDetail);
        $Car = Car::where('id',$request->input('id'))->first();

        if($Car->image != "")
        {
            $Car->image = url('images').'/'.$Car->image;
        }

        return response()->json(['success'=>true,'data'=>$Car,'message'=>'Item Updated successfully'], 200);
    }


    // do follow and un follow
    public function followStatusChange(Request $request)
    {
        $x = new \stdClass();
        $following_id = $request->input('following_id');
        $follower_id = $this->userId;
        if($following_id == $follower_id)
        {
            $message = 'User Can not follow own';
            return response()->json(['success'=>true,'data'=>$x,'message'=>$message], 200);
        }else{
            $UserCount = User::where('id',$following_id)->count();
            if($UserCount <= 0)
            {
                $message = "Following User Not Found";
                return response()->json(['success'=>true,'data'=>$x,'message'=>$message], 200);
            }else{
                $count = Follow::where('following_id',$following_id)->where('follower_id',$follower_id)->count();
                if($count > 0)
                {
                    Follow::where('following_id',$following_id)->where('follower_id',$follower_id)->delete();
                    $message = 'User Un-follow Successsfully';
                }else{
                    $follow = new Follow;
                    $follow->following_id = $following_id;
                    $follow->follower_id = $follower_id;
                    $follow->save();
                    $message = 'User Follow Successsfully';
                }
                return response()->json(['success'=>true,'data'=>$x,'message'=>$message], 200);
            }
        }
    }

    // followers list
    public function followerList()
    {
        $follwerList = Follow::where('following_id',$this->userId)->with('followingUser')->get();
        return response()->json(['success'=>true,'data'=>$follwerList,'message'=>"Follower List Get Successfully"], 200);
    }

    // followers list
    public function followingList()
    {
        $followingList = Follow::where('follower_id',$this->userId)->with('followerUser')->get();
        return response()->json(['success'=>true,'data'=>$followingList,'message'=>"Following List Get Successfully"], 200);
    }
}
