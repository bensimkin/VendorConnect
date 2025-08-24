<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Session;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // For API requests, return null to prevent redirect
        if ($request->expectsJson()) {
            return null;
        }

        // For web requests, try to redirect to login route
        try {
            return route('login.view');
        } catch (\Exception $e) {
            // If route doesn't exist, fallback to login path
            return '/login';
        }
    }
}
