<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;

class FriendController extends Controller
{
    
    public function getUserFriend()
    {
        $user = $this->getAuthUser();
        return $user->friends()->get(['fullname', 'photo']);
    }

    public function invite(Request $request)
    {        
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id);              

        $user->friends()->attach($friend_id, ['status' => 'pending']);
        $friend->friends()->attach($user_id, ['status' => 'pending']);

        return response()->json([
            'message' => 'invite successfully send'
        ], 200);
    }

    public function accept(Request $request)
    {
        $this->updateInviteReq($request, 'accepted');        
    }

    public function decline(Request $request)
    {
        $this->updateInviteReq($request, 'declined');        
    }

    public function delete(Request $request)
    {
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id);        

        $user->friends()->detach($friend_id);
        $friend->friends()->detach($user_id);

        return response()->json([
            'message' => 'friend deleted successfully'
        ], 200);
    }

    private function updateInviteReq($request, $status){
        // Get Auth User
        $user = $this->getAuthUser();
        $user_id = $user->id;        

        // Check and Get Friend
        $friend_id = $request->user_id;
        $friend = $this->checkFriend($friend_id);
        
        // Check if invite req exist
        if($user->friends()->where('second_user_id', $friend_id)->doesntExist()){
            response()->json([
                'message' => 'invite not found'
            ], 404)->send();
            exit;
        }

        $user->friends()->updateExistingPivot($friend_id, ['status' => $status]);
        $friend->friends()->updateExistingPivot($user_id, ['status' => $status]);

        response()->json([
            'message' => 'invite request successfully ' . $status
        ], 200)->send();
        exit;
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