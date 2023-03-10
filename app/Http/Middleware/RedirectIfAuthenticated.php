<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
    
        // Get the user from the authenticated guard (e.g. api, web, etc)
        $user = $request->user();
    
        if ($user && $user->refresh_token !== $request->bearerToken()) {
            return redirect()->route('unauthenticated');
         }
         
        return $next($request);
    }
    
}
