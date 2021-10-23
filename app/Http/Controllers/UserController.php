<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function uploadphoto(Request $request, $username){
        // Validate request
        $validator = Validator::make($request->all(), [             
            'photo' => 'required|mimes:jpg,jpeg,png|max:2048',
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        // Validate User
        $user = $this->getAuthUser();
        if($user->username != $username)
            return response()->json(['message' => 'Not Authorized'], 403);

        $imgName = $username . "." . $request->photo->extension();
        $request->photo->move(public_path('photo'), $imgName);

        // Update Photo
        $user->photo = $imgName;
        $user->save();
        
        return response()->json(['message' => 'Photo profile successfully updated'], 200);
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
