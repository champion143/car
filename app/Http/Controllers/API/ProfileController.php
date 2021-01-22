<?php

namespace App\Http\Controllers\API;

use App\Car;
use App\Follow;
use App\Http\Controllers\Controller;
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
            // $follwerList = Follow::where('following_id',$this->userId)->with('followingUser')->get();
            // $followingList = Follow::where('follower_id',$this->userId)->with('followerUser')->get();
            // $userDetail->follwerList = $follwerList;
            // $userDetail->followingList = $followingList;
            $userDetail->follower_count = Follow::where('following_id',$userDetail->id)->count();
            $userDetail->following_count = Follow::where('follower_id',$userDetail->id)->count();
            $userDetail->win_count = MatchResult::where('win_user_id',$this->userId)->count();
            $userDetail->loss_count = MatchResult::where('loss_user_id',$this->userId)->count();
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
    // delete car
    public function deleteCar(Request $request,$id)
    {
        $Car = Car::where('id',$id)->delete();
        return response()->json(['success'=>true,'data'=>$Car,'message'=>'Item Deleted successfully'], 200);
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
        $following_id = $request->input('following_id');
        $follower_id = $this->userId;
        $x = User::where('id',$following_id)->first();
        if($x->image != "")
        {
            $x->image = url('images').'/'.$x->image;
        }
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
                    $x->is_follow = 0;
                }else{
                    $follow = new Follow;
                    $follow->following_id = $following_id;
                    $follow->follower_id = $follower_id;
                    $follow->save();
                    $message = 'User Follow Successsfully';
                    $x->is_follow = 1;
                }
                return response()->json(['success'=>true,'data'=>$x,'message'=>$message], 200);
            }
        }
    }

    // followers list
    public function followerList()
    {
        $follwerList = Follow::where('following_id',$this->userId)->with('followingUser')->get();
        $follwerList = $follwerList->toArray();
        foreach($follwerList as $key=>$follwer)
        {
            $user = $follwer['following_user'];
            if($user['image'] != "")
            {
                $user['image'] = url('images').'/'.$user['image'];
            }
            $is_follow = 0;
            $following_id = $user['id'];
            $follower_id = $this->userId;
            $follow = Follow::where('following_id',$following_id)->where('follower_id',$follower_id)->first();
            if(isset($follow->id))
            {
                $is_follow = 1;
            }
            $user['is_follow'] = $is_follow;

            $follwerList[$key]['user'] = $user;
            unset($follwerList[$key]['following_user']);
        }
        return response()->json(['success'=>true,'data'=>$follwerList,'message'=>"Follower List Get Successfully"], 200);
    }

    // followers list
    public function followingList()
    {
        $followingList = Follow::where('follower_id',$this->userId)->with('followerUser')->get();
        $followingList = $followingList->toArray();
        foreach($followingList as $key=>$follwer)
        {
            $user = $follwer['follower_user'];
            if($user['image'] != "")
            {
                $user['image'] = url('images').'/'.$user['image'];
            }
            $user['is_follow'] = 1;
            $followingList[$key]['user'] = $user;
            unset($followingList[$key]['follower_user']);
        }
        return response()->json(['success'=>true,'data'=>$followingList,'message'=>"Following List Get Successfully"], 200);
    }

    // notificaion
    public function raceChallenger(Request $request)
    {
        $receiver_id = $request->input('receiver_id');
        $Notification = new Notification;
        $Notification->sender_id = $this->userId;
        $Notification->receiver_id = $receiver_id;
        $Notification->race_type = $request->input('race_type','');
        $Notification->type = "challenge";
        $Notification->save();
        /* start push notificaion */
        $receiver_data = User::where('id',$receiver_id)->first();
        $sender_data = User::where('id',$this->userId)->first();
        $device_token = $receiver_data->device_token;
        $sender_name = $sender_data->first_name;
        $receiver_name = $receiver_data->first_name;
        $response = $this->sendPushNotificaion($device_token,$sender_name,$Notification->id,$Notification->race_type);
        return response()->json(['success'=>true,'data'=>$Notification,'message'=>'Challenge Sent'], 200);
    }

    function sendPushNotificaion($device_token,$sender_name,$Notificationid,$race_type="")
    {
        $key = 'AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $title = $sender_name.' challenged you';
        $body = "";
        $x = new \stdClass();
        $x->username = $sender_name;
        $x->challenged_id = $Notificationid;
        $x->type = "invitaion";
        $x->race_type = $race_type;
        $notification = array('title' =>$title , 'text' => $body, 'body' => $sender_name.' challenged you','extra_data'=>$x,"content_available" => true);
        $arrayToSend = array('to' => $device_token, 'notification' => $notification,'data'=>$x,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key= AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // accept and reject
    public function changeNotificationStatus(Request $request)
    {
        $Notification_id = $request->input('Notification_id');
        $status = $request->input('status');
        $message = "";
        if($status == 1)
        {
            $message = "Your Race was Accepted";
            $type = 'accept';
        }else if($status == 2){
            $message = "Your Race was Rejected";
            $type = 'reject';
        }
        $notifications = Notification::where('id',$Notification_id)->first();
        /* notification start */
        $sender_data = User::where('id',$notifications->sender_id)->first();
        $sender_name = $sender_data->first_name;
        $device_token = $sender_data->device_token;
        $receiver_data = User::where('id',$notifications->receiver_id)->first();
        $receiver_name = $receiver_data->first_name;
        $key = 'AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $title = 'Race Invitaion';
        $body = "";
        $x = new \stdClass();
        $x->username = $receiver_name;
        $x->challenged_id = $Notification_id;
        $x->type = $type;
        $x->race_type = $notifications->race_type;
        $notification = array('title' =>$title , 'text' => $body, 'body' => $message,'extra_data'=>$x,"content_available" => true);
        $arrayToSend = array('to' => $device_token, 'notification' => $notification,'data'=>$x,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key= AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $response = curl_exec($ch);
        curl_close($ch);
        /* notification end */

        $Notification = Notification::where('id',$Notification_id)->update(
            array(
                'status' => $status
            )
        );
        return response()->json(['success'=>true,'data'=>$Notification,'message'=>$message], 200);
    }

    /* start race */
    public function startRace(Request $request)
    {
        $notification_id = $request->input('notification_id');
        $Notification = Notification::where('id',$notification_id)->first();

        $sender_data = User::where('id',$Notification->sender_id)->first();
        $sender_name = $sender_data->first_name;
        $sender_token = $sender_data->device_token;
        $this->silentNotificaion($sender_token,$sender_name,$Notification);

        $receiver_data = User::where('id',$Notification->receiver_id)->first();
        $receiver_name = $receiver_data->first_name;
        $receiver_token = $receiver_data->device_token;
        $this->silentNotificaion($receiver_token,$receiver_name,$Notification);

        return response()->json(['success'=>true,'data'=>array(),'message'=>"Start Race Notificaions send successfully"], 200);
    }

    /* silent notificaion */
    public function silentNotificaion($device_token,$name,$notifications)
    {
        $key = 'AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $title = 'Start race';
        $body = "Please be ready to race";
        $message = "Please be ready to race";
        $x = new \stdClass();
        $x->username = $name;
        $x->challenged_id = $notifications->id;
        $x->type = 'startrace';
        $x->race_type = $notifications->race_type;
        $notification = array('title' =>$title , 'text' => $body, 'body' => $message,'extra_data'=>$x,"content_available" => true);
        $arrayToSend = array('to' => $device_token, 'notification' => $notification,'data'=>$x,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key= AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $response = curl_exec($ch);
        curl_close($ch);
    }

    // notification list
    public function notificaionList()
    {
        $Notifications = Notification::where('status',0)->where('sender_id',$this->userId)->orWhere('receiver_id',$this->userId)->get();
        foreach($Notifications as $key=>$Notification)
        {
            if($Notification->receiver_id == $this->userId)
                $other_user_id = $Notification->sender_id;
            else
                $other_user_id = $Notification->receiver_id;
            $otherUserData = User::where('id',$other_user_id)->first();
            if($otherUserData->image != "")
            {
                $otherUserData->image = url('images').'/'.$otherUserData->image;
            }
            $Notification->user = $otherUserData;
        }
        return response()->json(['success'=>true,'data'=>$Notifications,'message'=>'Notification list successfully'], 200);
    }

    //
    public function getCarMake()
    {
        $makeArr = array();
        $object = new \stdClass();
        $object->car_maker = 'acura';
        $car_models = array('RDX','TLX');
        $object->car_models = $car_models;
        array_push($makeArr,$object);

        $object = new \stdClass();
        $object->car_maker = 'toyoto';
        $car_models = array('4RUNNER','Avalon');
        $object->car_models = $car_models;
        array_push($makeArr,$object);

        $object = new \stdClass();
        $object->car_maker = 'Audi';
        $car_models = array('A7','Q5');
        $object->car_models = $car_models;
        array_push($makeArr,$object);

        $object = new \stdClass();
        $object->car_maker = 'BMW';
        $car_models = array('X3','X7');
        $object->car_models = $car_models;
        array_push($makeArr,$object);

        $object = new \stdClass();
        $object->car_maker = 'Buick';
        $car_models = array('Encore','Encore GX');
        $object->car_models = $car_models;
        array_push($makeArr,$object);

        return response()->json(['success'=>true,'data'=>$makeArr,'message'=>'CarMake list successfully'], 200);
    }

    public function updateDeviceToken(Request $request)
    {
        $device_token = $request->input('device_token');
        $user = User::where('id',$this->userId)->update(
            array(
                'device_token' => $device_token
            )
        );
        return response()->json(
            [
                'success'=>true,
                'data'=>array(),
                'message'=>'Device Token successfully'
            ], 200);
    }

    public function sendPushNotificaionForResult($x,$title,$device_token,$message)
    {
        /* notification start */
        $key = 'AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        $ch = curl_init("https://fcm.googleapis.com/fcm/send");
        $title = $title;
        $body = "";
        $notification = array('title' =>$title , 'text' => $body, 'body' => $message,'extra_data'=>$x,"content_available" => true);
        $arrayToSend = array('to' => $device_token, 'notification' => $notification,'data'=>$x,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key= AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        $response = curl_exec($ch);
        curl_close($ch);
        /* notification end */
    }

    // audio file store
    public function audioFileUpload(Request $request)
    {
        $challenge_id = (int)$request->input('challenge_id');
        $speed = $request->input('speed');
        $distance = $request->input('distance');
        $racetype = $request->input('racetype');
        $speed_at_green = $request->input('speed_at_green');
        $MatchRace = new MatchRace();
        $MatchRace->user_id = $this->userId;
        $MatchRace->challenge_id = $challenge_id;
        $MatchRace->speed = $speed;
        $MatchRace->distance = $distance;
        $MatchRace->racetype = $racetype;
        $MatchRace->speed_at_green = $speed_at_green;
        if ($request->hasFile('file')) {
            $image = $request->file('file');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $MatchRace->file = $name;
        }
        $MatchRace->save();

        if($MatchRace->file != "")
        {
            $MatchRace->file = url('images').'/'.$MatchRace->file;
        }

        $win_user_matchrace_id = 0;
        $loss_user_matchrace_id = 0;

        $other_id = 0;
        if($challenge_id == 0)
        {
            $MatchRace->rematchcount = 0;
            $MatchRace->matchresult = 0;
        }else{
            $Notification = Notification::where('id',$challenge_id)->orderBy('id')->first();
            $sender_id = $Notification->sender_id;
            $receiver_id = $Notification->receiver_id;
            if($this->userId == $sender_id)
            {
                $otherUserId = $receiver_id;
            }else{
                $otherUserId = $sender_id;
            }

            $user1Count = MatchRace::where('challenge_id',$challenge_id)->where('user_id',$otherUserId)->count();
            $user2Count = MatchRace::where('challenge_id',$challenge_id)->where('user_id',$this->userId)->count();

            if($user1Count == $user2Count)
            {
                $OtherUserMatchRaceData = MatchRace::where('user_id',$otherUserId)->orderBy('id','desc')->first();
                if(isset($OtherUserMatchRaceData->id))
                {
                    $distance1 = $distance;
                    $distance2 = $OtherUserMatchRaceData->distance;
                    if($distance1 > $distance2)
                    {
                        $win_user_id = $this->userId;
                        $loss_user_id = $otherUserId;
                        $win_user_matchrace_id = $MatchRace->id;
                        $loss_user_matchrace_id = $OtherUserMatchRaceData->id;
                        $MatchRace->matchresult = 1;
                    }else{
                        $MatchRace->matchresult = 2;
                        $win_user_id = $otherUserId;
                        $loss_user_id = $this->userId;
                        $loss_user_matchrace_id = $MatchRace->id;
                        $win_user_matchrace_id = $OtherUserMatchRaceData->id;
                    }
                    $MatchResult =  new MatchResult();
                    $MatchResult->win_user_id = $win_user_id;
                    $MatchResult->loss_user_id = $loss_user_id;
                    $MatchResult->win_user_matchrace_id = $win_user_matchrace_id;
                    $MatchResult->loss_user_matchrace_id = $loss_user_matchrace_id;
                    $MatchResult->save();

                    /* win user */
                    $winUser = User::where('id',$win_user_id)->first();
                    $title = 'Challenge result';
                    $device_token = $winUser->device_token;
                    $x = new stdClass();
                    $x->match_id = $MatchResult->id;
                    $x->type = "raceresult";
                    $message = 'You won';
                    $this->sendPushNotificaionForResult($x,$title,$device_token,$message);
                    $lossUser = User::where('id',$loss_user_id)->first();
                    $device_token = $lossUser->device_token;
                    $message = 'You lose';
                    $this->sendPushNotificaionForResult($x,$title,$device_token,$message);
                }
            }
            else{
                $MatchRace->matchresult = 0;
            }
            $allMatchChallengeData = MatchRace::where('challenge_id',$challenge_id)->count();
            $MatchRace->rematchcount = (int)($allMatchChallengeData / 2) + 1;
            $MatchRace->other_id = $otherUserId;
        }
        return response()->json(
            [
                'success'=>true,
                'data'=> $MatchRace,
                'message'=>'Match Data successfully'
            ], 200);
    }

    /* match status change */
    public function matchStatusChange(Request $request)
    {
        $match_id = $request->input('match_id');
        $status = $request->input('status');
        MatchResult::where('id',$match_id)->update(
            array(
                'status' => $status
            )
        );
        return response()->json(
            [
                'success'=>true,
                'data'=> array(),
                'message'=>'Match Status successfully'
            ], 200);
    }

    public function noContentList(Request $request)
    {
        $MatchResult = MatchResult::where('status',1)->where(function($query)
                                    {
                                        $query->where('win_user_id',$this->userId)
                                        ->orWhere('loss_user_id',$this->userId);
                                    })->get();
        foreach($MatchResult as $match)
        {
            $other_user_id = $match->win_user_id;
            $user = User::where('id',$other_user_id)->first();
            if($user->image != "")
            {
                $user->image = url('images').'/'.$user->image;
            }
            $match->user = $user;
        }
        return response()->json(
            [
                'success'=>true,
                'data'=> $MatchResult,
                'message'=>'No Contest List Get successfully'
            ], 200);
    }

    public function winList(Request $request)
    {
        $MatchResult = MatchResult::where('status',0)->where('win_user_id',$this->userId)->get();
        foreach($MatchResult as $match)
        {
            $other_user_id = $match->loss_user_id;
            $user = User::where('id',$other_user_id)->first();
            if($user->image != "")
            {
                $user->image = url('images').'/'.$user->image;
            }
            $match->user = $user;
        }
        return response()->json(
            [
                'success'=>true,
                'data'=> $MatchResult,
                'message'=>'Win List Get successfully'
            ], 200);
    }

    // update win

    public function lossList(Request $request)
    {
        $MatchResult = MatchResult::where('status',0)->where('loss_user_id',$this->userId)->get();
        foreach($MatchResult as $match)
        {
            $other_user_id = $match->win_user_id;
            $user = User::where('id',$other_user_id)->first();
            if($user->image != "")
            {
                $user->image = url('images').'/'.$user->image;
            }
            $match->user = $user;
        }
        return response()->json(
            [
                'success'=>true,
                'data'=> $MatchResult,
                'message'=>'Loss List Get successfully'
            ], 200);
    }

    public function matchDetail(Request $request)
    {
        $id = $request->input('id');
        $match = MatchResult::where('id',$id)->first();
        $raceDataId = 0;
        $rematchCount = 0;
        if($match->win_user_id == $this->userId)
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
        $rematchCount1 = MatchResult::where('win_user_id',$this->userId)->where('loss_user_id',$other_user_id)->count();
        $rematchCount2 = MatchResult::where('loss_user_id',$this->userId)->where('win_user_id',$other_user_id)->count();
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
