<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request, $id)
    {
        if(!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid Email Verification URL'
            ], 400);
        }

        $user = User::findOrFail($id);

        if(!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->to('/');
    }

    public function resend()
    {
        if(auth()->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Invalid Email Verification URL'
            ], 400);
        }

        auth()->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Please cek your email. Successfully resend'
        ], 201);
    }
}
