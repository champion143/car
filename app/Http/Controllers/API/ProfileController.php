<?php

namespace App\Http\Controllers\API;

use App\Banner;
use App\Car;
use App\Enquiry;
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
use App\Driver;
use DB;
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
                $userDetail->image = url('public/images').'/'.$userDetail->image;
            }
            if($userDetail->image1 != "")
            {
                $userDetail->image1 = url('public/images').'/'.$userDetail->image1;
            }
            if($userDetail->image2 != "")
            {
                $userDetail->image2 = url('public/images').'/'.$userDetail->image2;
            }
            return response()->json(['success'=>true,'data'=>$userDetail,'message'=>'user profile get successfully'], 200);
        }else{
            return response()->json(['success'=>false,'data'=>$x,'message'=>'user not found'], 401);
        }
    }
    
    public function getNearByDrivers(Request $request)
    {
        $userDetail = User::where('api_token',$request->header('token'))->first();
        $lat = $userDetail->lat2;
        $lng = $userDetail->lng2;
        $user_id = $userDetail->id;
        $nearbyDriver = DB::select("SELECT  *, SQRT( POW(69.1 * (lat2 - $lat), 2) + POW(69.1 * ($lng - lng2) * COS(lat2 / 57.3), 2)) AS distance FROM users WHERE is_driver = 1 AND id != $user_id HAVING distance < 25 ORDER BY distance");
        return response()->json(['success'=>true,'data'=>$nearbyDriver,'message'=>'Driver List'], 200);
    }
    
    public function updateLatLng(Request $request)
    {
        $lat = $request->input('lat','');
        $lng = $request->input('lng','');
        User::where('api_token',$request->header('token'))->update(
            array(
                'lat2' => $lat,
                'lng2' => $lng
            )
        );
        $userDetail = User::where('api_token',$request->header('token'))->get();
        return response()->json(['success'=>true,'data'=>$userDetail,'message'=>'lat lng updated successfully'], 200);
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
        $Banner->device_type = $request->input('device_type');
        
        if(!empty($request->input('at_store'))){
            $Banner->at_store  = $request->input('at_store');
        }
        if(!empty($request->input('delivery'))){
            $Banner->delivery  = $request->input('delivery');
        }
        if(!empty($request->input('takeout'))){
            $Banner->takeout  = $request->input('takeout');
        }
        
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

        return response()->json(['success'=>true,'data'=>$request->all(),'message'=>'Item Inserted successfully'], 200);
    }
    
    public function updateDriverStatus(Request $request)
    {
        $userDetail = User::where('api_token',$request->header('token'))->first();
        $status_value = $userDetail->is_driver == 0 ? 1 : 0;
        User::where('api_token',$request->header('token'))->update(
            array(
                'is_driver' => $status_value
            )
        );
        $userDetail = User::where('api_token',$request->header('token'))->first();
        return response()->json(['success'=>true,'data'=>$userDetail,'message'=>'Driver Status Updated successfully'], 200);
    }
    
    public function updateNewAddedStatus(Request $request)
    {
        $banner_id = $request->input('banner_id');
        $status_name = $request->input('status_name');
        $Banner = Banner::where('id',$banner_id)->first();
        $status_value = $Banner->$status_name == 0 ? 1 : 0;
        Banner::where('id',$banner_id)->update(
            array(
                $status_name => $status_value
            )
        );
        $Banner = Banner::where('id',$banner_id)->first();
        return response()->json(['success'=>true,'data'=>$Banner,'message'=>'Item Tag Updated successfully'], 200);
    }

    public function getUploadItem()
    {
        $Banner = Banner::with('item')->where('user_id',$this->userId)->get();
        foreach($Banner as $Ban)
        {
            if($Ban->name != "")
            {
                $Ban->name = url('public/images').'/'.$Ban->name;
            }
            foreach($Ban->item as $item)
            {
                if($item->file_name != "")
                {
                    $item->file_name = url('public/images').'/'.$item->file_name;
                }
            }
        }
        return response()->json(['success'=>true,'data'=>$Banner,'message'=>'Item Get successfully'], 200);
    }

    public function updateItemTag(Request $request)
    {
        $item_id = $request->input('item_id');
        $tag_name = $request->input('tag_name');
        $Banner = Banner::where('id',$item_id)->first();
        $tag_value = $Banner->$tag_name == 0 ? 1 : 0;
        Banner::where('id',$item_id)->update(
            array(
                $tag_name => $tag_value
            )
        );
        $Banner = Banner::where('id',$item_id)->first();
        return response()->json(['success'=>true,'data'=>$Banner,'message'=>'Item Tag Updated successfully'], 200);
    }

    public function doEnquiry(Request $request)
    {
        $Enquiry = new Enquiry();
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/images');
            $image->move($destinationPath, $name);
            $Enquiry->image = $name;
        }
        $Enquiry->user_id = $request->input('user_id');
        $Enquiry->item_id = $request->input('item_id');
        $Enquiry->dimension = $request->input('dimension');
        $Enquiry->text = $request->input('text');
        $Enquiry->tag_name = $request->input('tag_name');
        $Enquiry->save();
        return response()->json(['success'=>true,'data'=>$Enquiry,'message'=>'Enquiry Send successfully'], 200);
    }

    public function updateEnquiryStatus(Request $request)
    {
        $enquiry_id = $request->input('enquiry_id');
        $status = $request->input('status');
        $Enquiry = Enquiry::where('id',$enquiry_id)->update(
            array(
                'status' => $status
            )
        );
        $Enquiry = Enquiry::where('id',$enquiry_id)->first();
        return response()->json(['success'=>true,'data'=>$Enquiry,'message'=>'Enquiry Status Updated successfully'], 200);
    }
    
    // do enquiry accept or reject
    public function updateEnquiryAcceptOrRejectStatus(Request $request)
    {
        $enquiry_id = $request->input('enquiry_id');
        $status = $request->input('status');
        $Enquiry = Enquiry::where('id',$enquiry_id)->update(
            array(
                'is_accept' => $status
            )
        );
        $Enquiry = Enquiry::where('id',$enquiry_id)->first();
        return response()->json(['success'=>true,'data'=>$Enquiry,'message'=>'Enquiry Status Updated successfully'], 200);
    }
    
    // delivery_status
    public function updateEnquiryDeliveryStatus(Request $request)
    {
        $enquiry_id = $request->input('enquiry_id');
        $status = $request->input('status');
        $Enquiry = Enquiry::where('id',$enquiry_id)->update(
            array(
                'delivery_status' => $status
            )
        );
        Banner::where('id',$enquiry_id)->update(
            array(
                'delivery_status' => $status
            )
        );
        $Enquiry = Banner::where('id',$enquiry_id)->first();
        return response()->json(['success'=>true,'data'=>$Enquiry,'message'=>'Enquiry Status Updated successfully'], 200);
    }
    
    // get enquiry Status
    public function getEnquiryDetail(Request $request)
    {
        $enquiry_id = $request->input('enquiry_id');
        $Enquirys = Enquiry::where('id',$enquiry_id)->first();
        if(isset($Enquirys->id))
        {
            $Enquirys->image = url('public/images').'/'.$Enquirys->image;   
        }
        return response()->json(['success'=>true,'data'=>$Enquirys,'message'=>'Enquiry Send successfully'], 200);
    }

    public function getEnquiry(Request $request)
    {
        // $BannerIds = Banner::with('item')->where('user_id',$this->userId)->pluck('id');
        // //$item_id = $request->input('item_id');
        // $Enquirys = Enquiry::whereIn('item_id',$BannerIds)->get();
        // foreach($Enquirys as $Enquiry)
        // {
        //     $Enquiry->image = url('public/images').'/'.$Enquiry->image;
        // }
        $BannersArray = array();
        $Banners = Banner::with('item')->where('user_id',$this->userId)->get();
        foreach($Banners as $Banner)
        {
            $Banner->name = url('public/images').'/'.$Banner->name;
            $Enquirys = Enquiry::where('item_id',$Banner->id)->get();
            $EnquiryCount = Enquiry::where('item_id',$Banner->id)->count();
            foreach($Enquirys as $Enquiry)
            {
                $Enquiry->image = url('public/images').'/'.$Enquiry->image;
            }
            $Banner->enquiries = $Enquirys;
            foreach($Enquirys as $Enquiry)
            {
                $Enquiry->user_info = User::where('id',$Enquiry->user_id)->get();
            }
            if($EnquiryCount > 0)
            {
                array_push($BannersArray,$Banner);   
            }
        }
        return response()->json(['success'=>true,'data'=>$BannersArray,'message'=>'Enquiry Get successfully'], 200);
    }
    
    // add driver
    public function addDriver(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $Driver = Driver::where('email',$email)->orWhere('phone',$phone)->first();
        if(isset($Driver->id))
        {
            return response()->json(['success'=>false,'data'=>$Driver,'message'=>'Driver Already Exist'], 200);
        }else{
            $Driver = new Driver();
            $Driver->name = $name;
            $Driver->email = $email;
            $Driver->phone = $phone;
            $Driver->address = $address;
            $Driver->save();
            return response()->json(['success'=>true,'data'=>$Driver,'message'=>'Driver successfully'], 200);
        }
    }
    
    // driverSelection
    public function driverSelection(Request $request)
    {
        $enquiry_id = $request->input('enquiry_id');
        $driver_id = $request->input('driver_id');
        $Enquiry = Enquiry::where('id',$enquiry_id)->update(
            array(
                'driver_id' => $driver_id
            )
        );
        $Enquiry = Enquiry::where('id',$enquiry_id)->first();
        return response()->json(['success'=>true,'data'=>$Enquiry,'message'=>'Enquiry Driver Updated successfully'], 200);
    }

    public function getOtherUploadItem()
    {
        // $Banner = Banner::with('item')->where('user_id','!=',$this->userId)->get();
        $Banner = Banner::with('item')->get();
        foreach($Banner as $Ban)
        {
            foreach($Ban->item as $item)
            {
                if($item->file_name != "")
                {
                    $item->file_name = url('public/images').'/'.$item->file_name;
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
    
    // getOrderListing
    public function getOrderListing()
    {
        $allitemsIds = Enquiry::where('user_id',$this->userId)->groupBy('item_id')->pluck('item_id');
        $allOrder = Banner::with('item')->whereIn('id',$allitemsIds)->get();
        foreach($allOrder as $Ban)
        {
            if($Ban->name != "")
            {
                $Ban->name = url('public/images').'/'.$Ban->name;
            }
            foreach($Ban->item as $item)
            {
                if($item->file_name != "")
                {
                    $item->file_name = url('public/images').'/'.$item->file_name;
                }
            }
            $enquiry = Enquiry::where('item_id',$Ban->id)->get();
            foreach($enquiry as $enq)
            {
                $enq->image = url('public/images').'/'.$enq->image;
            }
            $Ban->enquiry = $enquiry;
            
        }
        return response()->json(['success'=>true,'data'=>$allOrder,'message'=>'User Items Get successfully'], 200);
    }


}
