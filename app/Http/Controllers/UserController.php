<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;


class UserController extends Controller
{
    public function searchUser(Request $request)
    { 
        // Validate Request
        $validator = Validator::make($request->all(), [            
            'username' => 'required|string|min:1|max:16',
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }
        
        // Get Auth User
        $user = $this->getAuthUser();

        // Get User Friends
        $friends = $user->friends()->where('friends.status', 'accepted')->get(['username']);
        
        $username = $request->username;        
        $users = User::where('username', 'like', '%'.$username."%")
                    ->where('username', '!=', $user->username)
                    ->whereNotIn('username', $friends)
                    ->get(['id','username', 'photo']);
        
        foreach ($users as $user) {
            $user_photo = $user->photo;
            $user->link = Storage::url($user_photo);
            }
        
        return $users;       
    }
    
    public function profile(Request $request, $username)
    {         
        // Get Auth User
        $user = $this->getAuthUser();
        
        try {
            return User::where('username', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }
    }
    
    public function setup(Request $request){
        // Validate request
        $validator = Validator::make($request->all(), [            
            'fullname' => 'required|string|min:3|max:32',
            'photo' => 'required|mimes:jpg,jpeg,png|max:2048',
            'phone_number' => 'numeric|digits_between:10,16'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        // Get User
        $user = $this->getAuthUser();               

        // Save Image
        $imgName = $user->username . "." . $request->photo->extension();
        $request->photo->move(public_path('image'), $imgName);

        // Save Profile
        try {
            $user->update([
                'fullname' => request('fullname'),
                'photo' => $imgName,
                'phone_number' => request('phone_number')
            ]);

            $user->save();

            return response()->json([
                'message' => 'profile set up success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'profile set up failed!',
                'exception' => $e
            ], 422);
        }       
    }

    private function getAuthUser()
    {
        try{
            return $user = auth('api')->userOrFail();
        }catch(\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e){
            response()->json(['message' => 'Not authenticated, please login first'])->send();
            exit;
        }   
    }
}