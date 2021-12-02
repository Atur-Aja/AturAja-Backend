<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\IsValidPassword;

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
            'username' => ['required', 'min:4', 'max:16', 'alpha_dash', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'same:password_validate', new isValidPassword()],
            'password_validate' => ['required'],
        ]);

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => app('hash')->make($request->password)
            ]);
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

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {

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
                'message' => 'email / username / password incorrect'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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
            'username' => Auth::user()->username,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
