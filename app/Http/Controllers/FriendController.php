<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;

class FriendController extends Controller
{
    
    public function getUserFriends()
    {
        $user = $this->getAuthUser();
        return $user->friends()->where('friends.status', 'accepted')->get();
    }

    public function getFriendsByUsername(Request $request)
    {
        $user = $this->getAuthUser();
        
        // Validate Request
        $validator = Validator::make($request->all(), [            
            'username' => 'required|string|min:1|max:16',
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }        
        
        return $user->friends()
            ->where('friends.status', 'accepted')
            ->where('users.username', 'like', '%'.$request->username."%")
            ->get(['users.id', 'users.username', 'users.photo']);
    }

    public function getFriendsReq(Request $request)
    {
        $user = $this->getAuthUser();
        return $user->friends()->where('friends.status', 'pending')->get();
    }

    public function getFriendsReqSent()
    {
        $user = $this->getAuthUser();
        return $user->friends()->where('friends.status', 'requested')->get();
    }

    public function invite(Request $request)
    {        
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id); 
        
        if($user_id == $friend_id){
            return response()->json([
                'message' => 'can\'t invite yourself'
            ], 409);
        }

        // Check if friend req exist
        if($user->friends()->where('second_user_id', $friend_id)->exists()){
            return response()->json([
                'message' => 'you have invited him or her'
            ], 409);
        }

        $user->friends()->attach($friend_id, ['status' => 'requested']);
        $friend->friends()->attach($user_id, ['status' => 'pending']);

        return response()->json([
            'message' => 'friend request successfully send'
        ], 200);
    }

    public function accept(Request $request)
    {
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;        

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id);
        
        // Check if friend req exist
        if($user->friends()->where('second_user_id', $friend_id)->doesntExist()){
            return response()->json([
                'message' => 'friend request not found'
            ], 404);
        }

        $user->friends()->updateExistingPivot($friend_id, ['status' => 'accepted']);
        $friend->friends()->updateExistingPivot($user_id, ['status' => 'accepted']);

        return response()->json([
            'message' => 'friend request successfully accepted'
        ], 200);                
    }

    public function decline(Request $request)
    {
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;        

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id);
        
        // Check if friend req exist
        if($user->friends()->where('second_user_id', $friend_id)->doesntExist()){
            return response()->json([
                'message' => 'friend request not found'
            ], 404);
        }

        $user->friends()->detach($friend_id);
        $friend->friends()->detach($user_id);

        return response()->json([
            'message' => 'friend request successfully declined'
        ], 200);
    }

    public function delete(Request $request)
    {
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id);
        
        // Check if friends exist
        if($user->friends()->where('second_user_id', $friend_id)->doesntExist()){
            return response()->json([
                'message' => 'friend not found'
            ], 404);
        }

        $user->friends()->detach($friend_id);
        $friend->friends()->detach($user_id);

        return response()->json([
            'message' => 'friend deleted successfully'
        ], 200);
    }    

    private function checkFriend($friend_id)
    {
        try {            
            $friend = User::findOrFail($friend_id);
            return $friend;
        } catch (ModelNotFoundException $e) {
            response()->json([
                'message' => 'user not found'
            ], 404)->send();
            exit;
        }
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