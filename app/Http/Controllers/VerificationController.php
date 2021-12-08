<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request, $id)
    {
        auth()->loginUsingId($id);
        $user = $request->user();
        if(!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid Email Verification URL'
            ], 400);
        }

        if(!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json(['message' => 'Email Verificaton Complate'], 201);
//        return redirect()->to('/');
    }

    public function resendEmail(Request $request)
    {
        if($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email has been verified'
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Successfully resend, Please cek your email.'
        ], 201);
    }
}
