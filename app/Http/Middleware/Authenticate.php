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
        \Log::info('=== AUTHENTICATE MIDDLEWARE ===');
        \Log::info('Request URL: ' . $request->fullUrl());
        \Log::info('Request method: ' . $request->method());
        \Log::info('Expects JSON: ' . ($request->expectsJson() ? 'true' : 'false'));
        \Log::info('User authenticated: ' . (auth()->check() ? 'true' : 'false'));
        \Log::info('Auth user: ' . json_encode(auth()->user()));
        
        // For API requests, return null to prevent redirect
        if ($request->expectsJson()) {
            \Log::info('API request - returning null for redirect');
            return null;
        }

        // For web requests, try to redirect to login route
        try {
            $redirectPath = route('login.view');
            \Log::info('Web request - redirecting to: ' . $redirectPath);
            return $redirectPath;
        } catch (\Exception $e) {
            // If route doesn't exist, fallback to login path
            \Log::warning('Route login.view not found, falling back to /login');
            return '/login';
        }
    }
}
