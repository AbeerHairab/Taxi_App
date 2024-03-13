<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Driver
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
        if ($request->user()->role != 3) {
            return response()->json([
                "message" => "Unauthorized",
            ]);

        }
        return $next($request);
    }
}
