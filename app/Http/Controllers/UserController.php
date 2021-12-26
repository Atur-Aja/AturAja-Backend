<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Http\Traits\AuthUserTrait;


class UserController extends Controller
{
    use AuthUserTrait;

    public function __construct()
    {
        $this->middleware('jwt.verify');
    }

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
        $invitedUsers = $user->friends()->where('friends.status', 'requested')->get(['username']);
        

        $username = $request->username;
        $users = User::where('username', 'like', '%'.$username."%")
                    ->where('username', '!=', $user->username)
                    ->whereNotIn('username', $friends)
                    ->get(['id','username', 'photo']);

        foreach ($users as $user) {            
            $user->link = Storage::url($user->photo);
            foreach ($invitedUsers as $invitedUser) {
                if(strcmp($user->username, $invitedUser->username) == 0){
                    $user->invited = 'true';
                }
            }
            if(strcmp($user->invited, 'true') != 0){
                $user->invited = 'false';
            }            
        }

        return $users;
    }

    public function profile(Request $request, $username)
    {
        try {
            return User::where('username', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }
    }

    public function setup(Request $request){
        // Get User
        $user = $this->getAuthUser();

        // Validate request
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|min:3|max:32',
            'photo' => 'required|mimes:jpg,jpeg,png|max:2048',
            'phone_number' => 'numeric|digits_between:10,16'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

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
}
