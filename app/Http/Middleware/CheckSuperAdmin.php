<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            if (!auth()->user()->hasRole('superadmin')) {
                // Deny access with a 403 Forbidden status code
                abort(403, 'Unauthorized action.');
            }
            // dd($request, $next);
        }

        return $next($request);
    }
}