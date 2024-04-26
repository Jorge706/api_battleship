<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->user()) {
            $user = auth()->user();

            if ($user->email_verified_at == null || $user->active != 1) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            else{
                return $next($request);
            
            }
        } else {
            return response()->json(['oreo' => 'No autorizado'], 403);
        }
    
        // return $next($request);

        


    }
}
