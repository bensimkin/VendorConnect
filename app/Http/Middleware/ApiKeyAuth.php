<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\Authenticate;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $apiKey = $request->header('X-API-Key');
        
        if (!$apiKey) {
            return app(Authenticate::class)->handle($request, $next, 'sanctum');
        } else {
            $key = ApiKey::where('key', $apiKey)->active()->first();

            if (!$key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or inactive API key'
                ], 401);
            }
    
            if (!$key->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'API key has expired or is inactive'
                ], 401);
            }
    
            // Check HTTP method permissions
            if (!$this->checkMethodPermission($request, $key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API key does not have permission for this HTTP method'
                ], 403);
            }
    
            // Update last used timestamp
            $key->markAsUsed();
    
            // Simulate user login by setting the authenticated user
            Auth::setUser($key->user);
            
            // Set the authenticated user in request
            $request->setUserResolver(function () use ($key) {
                return $key->user;
            });
    
            // Add API key info to request for use in controllers
            $request->attributes->set('api_key', $key);
    
            return $next($request);
        }
    }

    /**
     * Check if the API key has permission for the HTTP method
     */
    private function checkMethodPermission(Request $request, ApiKey $key): bool
    {
        $method = $request->method();
        $permissions = $key->permissions;

        // If no permissions specified, allow all methods
        if (!$permissions || empty($permissions)) {
            return true;
        }

        // Map HTTP methods to CRUD permissions
        $methodPermissions = [
            'GET' => 'read',
            'POST' => 'create',
            'PUT' => 'update',
            'PATCH' => 'update',
            'DELETE' => 'delete',
        ];

        $requiredPermission = $methodPermissions[$method] ?? null;

        if (!$requiredPermission) {
            return false;
        }

        // Check if the API key has the required permission
        return in_array($requiredPermission, $permissions) || in_array('*', $permissions);
    }
}

