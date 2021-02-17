<?php

namespace App\Http\Controllers\API;

use App\Banner;
use App\Car;
use App\Follow;
use App\Http\Controllers\Controller;
use App\Item;
use App\Notification;
use App\User;
use App\MatchRace;
use App\MatchResult;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use stdClass;

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
            if($userDetail->image != "")
            {
                $userDetail->image = url('images').'/'.$userDetail->image;
            }
            if($userDetail->image1 != "")
            {
                $userDetail->image1 = url('images').'/'.$userDetail->image1;
            }
            if($userDetail->image2 != "")
            {
                $userDetail->image2 = url('images').'/'.$userDetail->image2;
            }
            return response()->json(['success'=>true,'data'=>$userDetail,'message'=>'user profile get successfully'], 200);
        }else{
            return response()->json(['success'=>false,'data'=>$x,'message'=>'user not found'], 401);
        }
    }

    public function uploadItem(Request $request)
    {
        $Banner = new Banner();
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $Banner->name = $name;
        }
        $Banner->user_id = $this->userId;
        $Banner->save();

        $item_dimension = json_decode($request->input('item_dimension'));
        $item_price = json_decode($request->input('item_price'));

        $images=array();
        if($files=$request->file('item_image')){
            foreach($files as $key=>$file){
                $name = $file->getClientOriginalName();
                $destinationPath = public_path('/images');
                $file->move($destinationPath, $name);
                $Item = new Item();
                $Item->banner_id = $Banner->id;
                $Item->file_name = $name;
                $Item->dimension = $item_dimension[$key];
                $Item->price = $item_price[$key];
                $Item->save();
            }
        }

        return response()->json(['success'=>true,'data'=>$images,'message'=>'Item Inserted successfully'], 200);
    }

    public function getUploadItem()
    {
        $Banner = Banner::with('item')->where('user_id',$this->userId)->get();
        foreach($Banner as $Ban)
        {
            foreach($Ban->item as $item)
            {
                if($item->file_name != "")
                {
                    $item->file_name = url('images').'/'.$item->file_name;
                }
            }
        }
        return response()->json(['success'=>true,'data'=>$Banner,'message'=>'Item Get successfully'], 200);
    }

    public function getOtherUploadItem()
    {
        $Banner = Banner::with('item')->where('user_id','!=',$this->userId)->get();
        foreach($Banner as $Ban)
        {
            foreach($Ban->item as $item)
            {
                if($item->file_name != "")
                {
                    $item->file_name = url('images').'/'.$item->file_name;
                }
            }
        }
        return response()->json(['success'=>true,'data'=>$Banner,'message'=>'Item Get successfully'], 200);
    }

    // update profile
    public function update(Request $request)
    {
        $user = User::where('id',$this->userId);
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
        $user->save();
        return response()->json(['success'=>true,'data'=>$user,'message'=>'User Profile Updated successfully'], 200);
    }


}
