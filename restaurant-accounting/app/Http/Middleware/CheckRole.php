<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * This middleware uses Spatie Laravel Permission to check if the user has any of the specified roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Use Spatie's hasAnyRole method to check if user has any of the specified roles
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. You do not have the required role.');
    }
}
