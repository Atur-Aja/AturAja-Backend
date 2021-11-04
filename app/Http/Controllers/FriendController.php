<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class FriendController extends Controller
{
    
    public function getUserFriend()
    {
        //
    }

    public function invite(Request $request)
    {        
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;

        $friend_id = $request->user_id;
        $friend = User::findOrFail($friend_id);

        $user->friends()->attach($friend_id);
        $friend->friends()->attach($user_id);

        return response()->json([
            'message' => 'invite successfully send'
        ], 200);
    }

    public function accept(Request $request)
    {
        //
    }

    public function delete(Request $request)
    {
        //
    }
    
    private function getAuthUser()
    {
        try{
            return $user = auth('api')->userOrFail();
        }catch(\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e){
            response()->json([
                'message' => 'Not authenticated, please login first'
            ], 401)->send();
            exit;
        }   
    }
}