<?php

namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth as JWTAuthJWTAuth;


class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['message' => 'Unauthorized'],401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['message' => 'Token is Expired'],401);
            } else {
                return response()->json(['message' => 'Authorization Token not found'],401);
            }
        }
        return $next($request);
    }
}

