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
    //
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
            return response()->json(['success'=>false,'data'=>$x,'message'=>'Unauthorised'], 401);
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

    public function test()
    {
        $key = 'AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        /* start push notificaion */
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        //The device token.
        $token = "dVFYB80LR0bYpP77lPQioc:APA91bGXszjeBztoY6lN0QC6ZZeVGn_nUuxT-IHj1hish6wa8lQuHBgreTz9IU_9U_mRjCyAEvv1Wr0jl4zHPfgl4RIt-HXN0mMklrQ67B5bUb5squGRtov_mnzN0k7Jx1QTa6dvVZAf"; //token here
        //Title of the Notification.
        $title = "Carbon One To One";
        //Body of the Notification.
        $body = "Bear island knows no king but the king in the north, whose name is stark.";
        $x = new stdClass();
        $x->Nick = "Mario";
        $x->Room = "PortugalVSDenmark";
        //Creating the notification array.
        $notification = array('title' =>$title , 'text' => $body, 'body' => 'Hello Body','extra_data'=>$x,"content_available" => true);

        //This array contains, the token and the notification. The 'to' attribute stores the token.
        $arrayToSend = array('to' => $token, 'notification' => $notification,'data'=>$x,'priority'=>'high');
        //Generating JSON encoded string form the above array.
        $json = json_encode($arrayToSend);
        //Setup headers:
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key= AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5'; // key here
        //Setup curl, add headers and post parameters.
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        //Send the request
        $response = curl_exec($ch);
        //Close request
        curl_close($ch);
        print_r($response);

    }

    // car list
    public function otherUserCarList(Request $request)
    {
        $userId = $request->input('user_id');
        $carList = Car::where('user_id',$userId)->get();
        foreach($carList as $car)
        {
            if($car->image != "")
            {
                $car->image = url('images').'/'.$car->image;
            }
        }
        return response()->json(['success'=>true,'data'=>$carList,'message'=>'Car List Retrieve successfully'], 200);
    }

    public function noContentList(Request $request)
    {
        $userId = $request->input('user_id');
        $dataArray = array();
        $MatchResult = MatchResult::where(function($query) use ($userId)
                                    {
                                        $query->where('win_user_id',$userId)
                                        ->orWhere('loss_user_id',$userId);
                                    })->get();

        foreach($MatchResult as $match)
        {
            if($match->win_user_id == $userId && $match->win_user_status == 3)
            {
                $other_user_id = $match->loss_user_id;
                $user = User::where('id',$other_user_id)->first();
                if($user->image != "")
                {
                    $user->image = url('images').'/'.$user->image;
                }
                $match->user = $user;
                array_push($dataArray,$match);
            }else if($match->loss_user_id == $userId && $match->loss_user_status == 3)
            {
                $other_user_id = $match->win_user_id;
                $user = User::where('id',$other_user_id)->first();
                if($user->image != "")
                {
                    $user->image = url('images').'/'.$user->image;
                }
                $match->user = $user;
                array_push($dataArray,$match);
            }
        }

        return response()->json(
            [
                'success'=>true,
                'data'=> $dataArray,
                'message'=>'No Contest List Get successfully'
            ], 200);
    }

    public function winList(Request $request)
    {
        $userId = $request->input('user_id');
        $dataArray = array();
        $MatchResult = MatchResult::where(function($query) use ($userId)
                                    {
                                        $query->where('win_user_id',$userId)
                                        ->orWhere('loss_user_id',$userId);
                                    })->get();

        foreach($MatchResult as $match)
        {
            if($match->win_user_id == $userId && $match->win_user_status == 1)
            {
                $other_user_id = $match->loss_user_id;
                $user = User::where('id',$other_user_id)->first();
                if($user->image != "")
                {
                    $user->image = url('images').'/'.$user->image;
                }
                $match->user = $user;
                array_push($dataArray,$match);
            }else if($match->loss_user_id == $userId && $match->loss_user_status == 1)
            {
                $other_user_id = $match->win_user_id;
                $user = User::where('id',$other_user_id)->first();
                if($user->image != "")
                {
                    $user->image = url('images').'/'.$user->image;
                }
                $match->user = $user;
                array_push($dataArray,$match);
            }
        }

        return response()->json(
            [
                'success'=>true,
                'data'=> $dataArray,
                'message'=>'Win List Get successfully'
            ], 200);
    }
    public function lossList(Request $request)
    {
        $userId = $request->input('user_id');
        $dataArray = array();
        $MatchResult = MatchResult::where(function($query) use ($userId)
                                    {
                                        $query->where('win_user_id',$userId)
                                        ->orWhere('loss_user_id',$userId);
                                    })->get();

        foreach($MatchResult as $match)
        {
            if($match->win_user_id == $userId && $match->win_user_status == 2)
            {
                $other_user_id = $match->loss_user_id;
                $user = User::where('id',$other_user_id)->first();
                if($user->image != "")
                {
                    $user->image = url('images').'/'.$user->image;
                }
                $match->user = $user;
                array_push($dataArray,$match);
            }else if($match->loss_user_id == $userId && $match->loss_user_status == 2)
            {
                $other_user_id = $match->win_user_id;
                $user = User::where('id',$other_user_id)->first();
                if($user->image != "")
                {
                    $user->image = url('images').'/'.$user->image;
                }
                $match->user = $user;
                array_push($dataArray,$match);
            }
        }

        return response()->json(
            [
                'success'=>true,
                'data'=> $dataArray,
                'message'=>'Loss List Get successfully'
            ], 200);
    }

    public function matchDetail(Request $request)
    {
        $userId = $request->input('userId');
        $id = $request->input('id');
        $match = MatchResult::where('id',$id)->first();
        $raceDataId = 0;
        $rematchCount = 0;
        if($match->win_user_id == $userId)
        {
            $other_user_id = $match->loss_user_id;
            $raceDataId = $match->win_user_matchrace_id;
            $raceDataString = 1;
            $raceDataOtherId = $match->loss_user_matchrace_id;
            $raceDataOtherString = 2;
        }else{
            $other_user_id = $match->win_user_id;
            $raceDataId = $match->loss_user_matchrace_id;
            $raceDataString = 2;
            $raceDataOtherId = $match->win_user_matchrace_id;
            $raceDataOtherString = 1;
        }
        $user = User::where('id',$other_user_id)->first();
        if($user->image != "")
        {
            $user->image = url('images').'/'.$user->image;
        }

        /* rematch count*/
        $rematchCount = 0;
        $rematchCount1 = MatchResult::where('win_user_id',$userId)->where('loss_user_id',$other_user_id)->count();
        $rematchCount2 = MatchResult::where('loss_user_id',$userId)->where('win_user_id',$other_user_id)->count();
        $rematchCount = $rematchCount1 + $rematchCount2;
        /* end */

        $MatchRace = MatchRace::where('id',$raceDataId)->first();
        if($MatchRace->file != "")
        $MatchRace->file = url('images').'/'.$MatchRace->file;
        $MatchRace->matchresult = $raceDataString;
        $MatchRace->rematchcount = $rematchCount;
        $match->racedata = $MatchRace;

        $MatchRace1 = MatchRace::where('id',$raceDataOtherId)->first();
        if($MatchRace1->file != "")
        $MatchRace1->file = url('images').'/'.$MatchRace1->file;
        $MatchRace1->matchresult = $raceDataOtherString;
        $MatchRace1->rematchcount = $rematchCount;
        $match->raceotherdata = $MatchRace1;

        $match->user = $user;
        return response()->json(
            [
                'success'=>true,
                'data'=> $match,
                'message'=>'Match Detail Get successfully'
            ], 200);
    }

}
