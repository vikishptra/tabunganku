<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;

class CheckRefreshToken
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
        $user = Auth::user();
        $refreshToken = $request->bearerToken();

        if (!$user || $refreshToken !== $user->refresh_token) {
            return redirect()->route('unauthenticated');
        }

        return $next($request);
    }
}
