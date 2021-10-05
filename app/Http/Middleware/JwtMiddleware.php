<?php

namespace App\Http\Middleware;
use JWTAuth;
use Closure;

class JwtMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized',
                'description' => 'Token invalid',
                'exception' => $e
            ], 401);
        }
        return $next($request);
    }
}
