<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Redirect;

class CheckProfile
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

        if($user->photo != null)
        {
            return $next($request);
        }


        $url = 'http://localhost:3000/setup-profile';
        return \redirect($url);
    }
}
