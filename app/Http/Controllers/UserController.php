<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function setup(Request $request, $username){
        // Validate request
        $validator = Validator::make($request->all(), [            
            'fullname' => 'required|string|min:3|max:100',
            'photo' => 'required|mimes:jpg,jpeg,png|max:2048',
            'phone_number' => 'required|min:10'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        // Validate User
        $user = $this->getAuthUser();
        if($user->username != $username)
            return response()->json(['message' => 'Not Authorized'], 403);

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