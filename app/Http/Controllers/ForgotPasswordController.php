<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Rules\IsValidPassword;

class ForgotPasswordController extends Controller
{
    public function forgot(){
        $credentials = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($credentials);

        return response()->json([
            'message' => 'Reset password link sent on your email id.'
        ], 200);
    }

    public function reset(){
        $credentials = request()->validate([
            'email' => 'required|email',
            'password' => ['required', 'min:8', 'confirmed', new isValidPassword()],
            'token' => 'required|string'
        ]);

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
            'message' => 'Success reset password'
        ], 200);
    }
}
