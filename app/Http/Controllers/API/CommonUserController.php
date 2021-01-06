<?php

namespace App\Http\Controllers\API;

use App\Follow;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class CommonUserController extends Controller
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
    public function searchUserUsingRacerName(Request $request)
    {
        $racername = $request->input('racername');
        $users = User::where('racername', 'like', '%' . $racername . '%')->where('id', '!=' , $this->userId)->get();
        foreach($users as $user)
        {
            $is_follow = 0;
            $following_id = $user->id;
            $follower_id = $this->userId;
            $follow = Follow::where('following_id',$following_id)->where('follower_id',$follower_id)->first();
            if(isset($follow->id))
            {
                $is_follow = 1;
            }
            $user->is_follow = $is_follow;
            if($user->image != "")
            {
                $user->image = url('images').'/'.$user->image;
            }
        }
        return response()->json(
            [
                'success'=>true,
                'data'=>$users,
                'message'=>'User List Get successfully'
            ], 200);
    }

    public function searchUserUsingUserName(Request $request)
    {
        $username = $request->input('username');
        $users = User::where('first_name','like','%'.$username.'%')->orWhere('last_name','like','%'.$username.'%')->where('id','!=',$this->userId)->get();
        foreach($users as $user)
        {
            $is_follow = 0;
            $following_id = $user->id;
            $follower_id = $this->userId;
            $follow = Follow::where('following_id',$following_id)->where('follower_id',$follower_id)->first();
            if(isset($follow->id))
            {
                $is_follow = 1;
            }
            $user->is_follow = $is_follow;
            if($user->image != "")
            {
                $user->image = url('images').'/'.$user->image;
            }
        }
        return response()->json(
            [
                'success'=>true,
                'data'=>$users,
                'message'=>'User List Get successfully'
            ], 200);
    }
}
