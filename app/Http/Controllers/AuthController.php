<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|unique:users',
            'email' => 'required|email',
            'phone_number' => 'required|min:10',
            'password' => 'required|min:6|same:password_validate',
            'password_validate' => 'min:6',
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'display_name' => $request->username,
                'email' => $request->email,
                'password' => app('hash')->make($request->password),
                'phone_number' => $request->phone_number
            ]);

            return response()->json([
                'code' => 201,
                'message' => 'Success',
                'description' => 'User successfully created'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 409,
                'message' => 'Conflict',
                'description' => 'User registration failed!',
                'exception' => $e
            ], 409);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {        
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'username / password incorrect'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile(Request $request, $username)
    { 
        // Get User
        try {
            return User::where('username', $username)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'user not found'
            ], 404);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'username'=> auth::user()->username,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    // Check token validity
    public function checktoken()
    { 
        if (Auth::check()) {
            return response()->json(['message' => 'Valid'], 200);
        }            
    }
}
