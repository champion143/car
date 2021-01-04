<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;

class CommonUserController extends Controller
{
    //
    public function searchUserUsingRacerName(Request $request)
    {
        $racername = $request->input('racername');
        $users = User::where('racername', 'like', '%' . $racername . '%')->get();
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
        $users = User::where('first_name', 'like', '%' . $username . '%')->orWhere('last_name', 'like', '%' . $username . '%')->get();
        return response()->json(
            [
                'success'=>true,
                'data'=>$users,
                'message'=>'User List Get successfully'
            ], 200);
    }
}
