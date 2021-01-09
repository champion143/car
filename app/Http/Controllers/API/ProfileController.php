<?php

namespace App\Http\Controllers\API;

use App\Car;
use App\Follow;
use App\Http\Controllers\Controller;
use App\Notification;
use App\User;
use App\MatchRace;
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
        $x = User::where('id',$follower_id)->first();
        if($x->image != "")
        {
            $x->image = url('images').'/'.$x->image;
        }
        $is_follow = 0;
        $follow = Follow::where('following_id',$following_id)->where('follower_id',$follower_id)->first();
        if(isset($follow->id))
        {
            $is_follow = 1;
        }
        $x->is_follow = $is_follow;

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
        $Notification->type = "challenge";
        $Notification->save();

        /* start push notificaion */
        $receiver_data = User::where('id',$receiver_id)->first();
        $sender_data = User::where('id',$this->userId)->first();
        $device_token = $receiver_data->device_token;
        $sender_name = $sender_data->first_name;
        $receiver_name = $receiver_data->first_name;
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array (
            'registration_ids' => array (
                    $device_token
            ),
            'data' => array (
                    "message" => $sender_name." Challenged You For The Race"
            )
        );
        $fields = json_encode ( $fields );
        $headers = array (
                'Authorization: key=' . "AAAAFCC7KjQ:APA91bHm9NC4ONC_fzdn_A0fwbqPArQPb9dzbs8jn2_BNT_fZyLi1wMzH9U3FW5uayZwgq7jMuwDol8H0NxJ5gXrSXEbyxamgtuO8XO4EgCA6dCiOZbUiTFhlgXV9wDsclGATC5tucZ5",
                'Content-Type: application/json'
        );
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
        $result = curl_exec ( $ch );
        echo $result;
        curl_close ( $ch );
        /* end */

        return response()->json(['success'=>true,'data'=>$Notification,'message'=>'user challenge successfully'], 200);
    }

    // accept and reject
    public function changeNotificationStatus(Request $request)
    {
        $Notification_id = $request->input('Notification_id');
        $status = $request->input('status');
        $message = "";
        if($status == 1)
        {
            $message = "Challenge Accespted Successfully";
        }else if($status == 2){
            $message = "Challenge Rejected Successfully";
        }
        $Notification = Notification::where('id',$Notification_id)->update(
            array(
                'status' => $status
            )
        );
        return response()->json(['success'=>true,'data'=>$Notification,'message'=>$message], 200);
    }

    // notification list
    public function notificaionList()
    {
        $Notifications = Notification::with('user')->where('receiver_id',$this->userId)->get();
        // foreach($Notifications as $key=>$Notification)
        // {
        //     $user_data = $Notification->user;
        //     $image_full = url('image/').$user_data->image;
        //     $user_data->image = $image_full;
        //     $Notification->user_data = $user_data;
        // }
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

    // audio file store
    public function audioFileUpload(Request $request)
    {
        $challenge_id = $request->input('challenge_id');
        $speed = $request->input('speed');
        $distance = $request->input('distance');
        $racetype = $request->input('racetype');
        $speed_at_green = $request->input('speed_at_green');
        $MatchRace = new MatchRace();
        $MatchRace->user_id = $this->userId;
        $MatchRace->challenge_id = $challenge_id;
        $MatchRace->speed = json_encode($speed);
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
        return response()->json(
            [
                'success'=>true,
                'data'=>$MatchRace,
                'message'=>'Match Data successfully'
            ], 200);
    }
}
