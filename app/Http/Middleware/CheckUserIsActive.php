<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class CheckUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!filter_var($request->login, FILTER_VALIDATE_EMAIL) === false) {
            $user = User::where('email', $request->login)->firstOrFail();
        } else {
            $user = User::where('username', $request->login)->firstOrFail();
        }

        if($user->email_verified_at != null)
        {
            return $next($request);
        }

        return response()->json([
            'message' => 'email not verified, pleas cek your email'
        ], 404);
    }
}
