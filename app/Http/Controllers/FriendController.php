<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Resources\Friend as FriendResourse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
class FriendController extends Controller
{
    public function friends(Request $request)
    {
        $user= User::where('username', $request->username)->firstOrFail(); 
        $friends= $user->friends;
        return response()->json(['friends'=> FriendResourse::collection($friends)]); 
    }
    public function unFriend(Request $request){

        $friend= Friend::where('user_id', Auth::user()->id)->where('friend_id', $request->friend_id)->first();
        $friend2= Friend::where('friend_id', Auth::user()->id)->where('user_id', $request->friend_id)->first();

        if($friend !=null && $friend !=null)
        { 
            $friend->delete();
            $friend2->delete();
            return response()->json(['success'=> "You are now not friends"]); 
        }
        return response()->json(['success'=> "You already are not friend"]); 
    }
}
