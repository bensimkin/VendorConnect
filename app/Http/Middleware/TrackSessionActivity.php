<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackSessionActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track activity if user is authenticated
        if (Auth::check()) {
            try {
                // Get the bearer token
                $token = $request->bearerToken();
                
                if ($token) {
                    // Find the session and update activity
                    $session = UserSession::findByToken($token);
                    if ($session && is_null($session->logout_at)) {
                        $session->updateActivity();
                    }
                }
            } catch (\Exception $e) {
                // Silent fail - don't interrupt the request
                \Log::debug('Failed to track session activity', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $next($request);
    }
}
