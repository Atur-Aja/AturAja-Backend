<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request, $id)
    {        
        // auth()->loginUsingId($id);
        // $user = $request->user();

        $user = User::findOrFail($id);

        if(!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid Email Verification URL'
            ], 400);            
        }

        if(!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

//        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
//            throw new AuthorizationException;
//        }
//
//        if ($user->markEmailAsVerified())
//            event(new Verified($user));

//        return redirect($this->redirectPath())->with('verified', true);

        return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login');
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
