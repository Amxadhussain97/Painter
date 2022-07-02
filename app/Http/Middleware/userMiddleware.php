<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class userMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {


        $userId = auth()->user()->id;
        $user = User::where('id', $userId)->first();

        // if ($request->user_id) {
        //     if ($user->role != 'Admin') {
        //         $request->user_id = $userId;
        //     }
        // } else $request->user_id = $userId;



        return $next($request)->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
