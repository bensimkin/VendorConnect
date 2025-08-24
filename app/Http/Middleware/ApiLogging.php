<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiLogging
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
        // Log the incoming request
        Log::info('=== API REQUEST START ===');
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Request method: ' . $request->method());
        Log::info('Request headers: ' . json_encode($request->headers->all()));
        Log::info('Request body: ' . json_encode($request->all()));
        Log::info('User agent: ' . $request->userAgent());
        Log::info('IP address: ' . $request->ip());
        Log::info('Auth check: ' . (auth()->check() ? 'true' : 'false'));
        Log::info('Auth user: ' . json_encode(auth()->user()));

        // Process the request
        $response = $next($request);

        // Log the response
        Log::info('=== API RESPONSE ===');
        Log::info('Response status: ' . $response->getStatusCode());
        Log::info('Response headers: ' . json_encode($response->headers->all()));
        
        // Try to get response content (be careful with large responses)
        try {
            $content = $response->getContent();
            if (strlen($content) < 10000) { // Only log if content is not too large
                Log::info('Response content: ' . $content);
            } else {
                Log::info('Response content: [Content too large to log]');
            }
        } catch (\Exception $e) {
            Log::warning('Could not get response content: ' . $e->getMessage());
        }
        
        Log::info('=== API REQUEST END ===');

        return $response;
    }
}
