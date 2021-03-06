<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\IsValidPassword;
use App\Http\Traits\AuthUserTrait;

class AuthController extends Controller
{    
    use AuthUserTrait;
    
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => ['required', 'min:4', 'max:16', 'alpha_dash', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'same:password_validate'],
            'password_validate' => ['required'],
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => app('hash')->make($request->password)
            ])->sendEmailVerificationNotification();
            return response()->json([
                'message' => 'user successfully created'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'user registration failed!',
                'exception' => $e
            ], 409);
        }
    }

    public function login(Request $request)
    {
        // Validate Request
        $this->validate($request, [
            'login' => ['required'],
            'password' => ['required'],
        ]);

        $isEmailExist = User::where('email', '=', request()->input('login'))->exists();
        $isUsernameExist = User::where('username', '=', request()->input('login'))->exists();

        if($isEmailExist || $isUsernameExist){
            $loginField = request()->input('login');
            $credentials = null;

            if ($loginField !== null) {
                $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
                request()->merge([ $loginType => $loginField ]);
                $credentials = request([ $loginType, 'password' ]);
            } else {
                return response()->json([
                    'message' => 'please log in using email / username'
                ], 401);
            }

            if (!$token = auth()->attempt($credentials)) {
                return response()->json([
                    'message' => 'password incorrect'
                ], 401);
            }

            return $this->respondWithToken($token);
        }else{
            return response()->json([
                'message' => 'email / username invalid'
            ], 401);
        }        
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'successfully logged out'
        ], 200);
    }

    public function changeEmail(Request $request)
    {
        // Get Auth user
        $user = $this->getAuthUser();
        
        // Validate Request
        $this->validate($request, [
            'email' => ['required', 'email', 'unique:users']
        ]);
        
        // Update Email
        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        // Verify new email
        $user->sendEmailVerificationNotification();        
        
        return response()->json([
            'message' => 'Please check your email to verify your new email.'
        ], 201);        
    }

    public function changePassword(Request $request){
        // Get Auth user
        $user = $this->getAuthUser();
        
        // Validate Request
        $this->validate($request, [
            'password' => ['required', 'min:8', 'same:password_validate'],
            'password_validate' => ['required'],
        ]);

        // Update Password
        $user->password = app('hash')->make($request->password);
        $user->save();

        return response()->json([
            'message' => 'password successfully changed'
        ], 201);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'username' => Auth::user()->username,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
