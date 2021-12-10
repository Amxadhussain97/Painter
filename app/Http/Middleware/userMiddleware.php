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

        header("Access-Control-Allow-Origin: *");

        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin'
        ];
        if ($request->getMethod() == "OPTIONS") {

            // The client-side application can set only headers allowed in Access-Control-Allow-Headers

            return Response::make('OK', 200, $headers);
        }


        $userId = auth()->user()->id;
        $user = User::where('id', $userId)->first();

        if ($request->user_id) {
            if ($user->role != 'Admin') {
                $request->user_id = $userId;
            }
        } else $request->user_id = $userId;



        return $next($request);
    }
}
