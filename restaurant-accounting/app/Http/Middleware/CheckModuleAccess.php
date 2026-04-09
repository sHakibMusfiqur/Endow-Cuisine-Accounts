<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module = null): Response
    {
        // If no module is specified, allow the request
        if (!$module) {
            return $next($request);
        }

        $user = auth()->user();

        // If not authenticated, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // Admins and Managers always have access to all modules
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return $next($request);
        }

        // Accountants with 'both' module access can access all modules
        if ($user->hasRole('accountant') && $user->module_access === 'both') {
            return $next($request);
        }

        // Check module-based access for restricted accountants
        if ($user->hasRole('accountant')) {
            if ($module === 'restaurant' && $user->module_access === 'restaurant') {
                return $next($request);
            }

            if ($module === 'inventory' && $user->module_access === 'inventory') {
                return $next($request);
            }

            // Access denied - redirect to appropriate landing page
            if ($user->module_access === 'inventory') {
                return redirect()->route('inventory.dashboard')
                    ->with('error', 'You do not have access to this resource.');
            }

            if ($user->module_access === 'restaurant') {
                return redirect()->route('transactions.create')
                    ->with('error', 'You do not have access to this resource.');
            }
        }

        // Default: deny access
        return redirect()->route('dashboard')
            ->with('error', 'You do not have access to this resource.');
    }
}
