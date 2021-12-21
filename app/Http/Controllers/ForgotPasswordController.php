<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Rules\IsValidPassword;

class ForgotPasswordController extends Controller
{
    public function forgot(){
        // Validate Request
        $validator = Validator::make(request()->all(), [            
            'email' => 'required|email|exists:App\Models\User,email',
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        $credentials = request()->only(['email']);
        Password::sendResetLink($credentials);

        return response()->json([
            'message' => 'Reset password link sent on your email id.'
        ], 200);
    }

    public function reset(){
        // Validate Request
        $validator = Validator::make(request()->all(), [            
            'email' => 'required|email|exists:App\Models\User,email',
            'password' => ['required', new isValidPassword(), 'confirmed'],
            'token' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->messages());
        }

        $credentials = request()->only(['email', 'password', 'password_confirmation', 'token']);

        $email_password_status = Password::reset($credentials, function ($user, $password){
            $user->password = app('hash')->make($password);
            $user->save();
        });

        if($email_password_status == Password::INVALID_TOKEN){
            return response()->json([
                'message' => 'Invalid reset password token'
            ], 401);
        }

        return response()->json([
            'message' => 'Reset password success'
        ], 200);
    }
}
