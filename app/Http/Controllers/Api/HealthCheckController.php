<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HealthCheckController extends BaseController
{
    /**
     * Check Smart API health and environment variables
     */
    public function smartApiHealth(Request $request)
    {
        $checks = [
            'environment_variables' => $this->checkEnvironmentVariables(),
            'smart_api_functionality' => $this->checkSmartApiFunctionality(),
            'internal_api_access' => $this->checkInternalApiAccess(),
        ];

        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'healthy');

        return $this->sendResponse(
            $checks,
            $allHealthy ? 'Smart API is healthy' : 'Smart API has issues',
            $allHealthy ? 200 : 503
        );
    }

    /**
     * Check if required environment variables are loaded
     */
    private function checkEnvironmentVariables()
    {
        $requiredVars = ['SMART_API_KEY', 'OPENAI_API_KEY'];
        $missing = [];

        foreach ($requiredVars as $var) {
            if (empty(env($var))) {
                $missing[] = $var;
            }
        }

        return [
            'status' => empty($missing) ? 'healthy' : 'unhealthy',
            'message' => empty($missing) 
                ? 'All required environment variables are loaded' 
                : 'Missing environment variables: ' . implode(', ', $missing),
            'details' => [
                'SMART_API_KEY' => env('SMART_API_KEY') ? 'loaded' : 'missing',
                'OPENAI_API_KEY' => env('OPENAI_API_KEY') ? 'loaded' : 'missing',
            ]
        ];
    }

    /**
     * Check if Smart API can find users
     */
    private function checkSmartApiFunctionality()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => env('SMART_API_KEY')
            ])->get(secure_url('/api/v1/users'));

            if ($response->successful()) {
                $data = $response->json();
                $userCount = count($data['data'] ?? []);
                
                return [
                    'status' => $userCount > 0 ? 'healthy' : 'unhealthy',
                    'message' => $userCount > 0 
                        ? "Smart API can access users ({$userCount} found)" 
                        : 'Smart API cannot find users',
                    'details' => [
                        'user_count' => $userCount,
                        'response_status' => $response->status()
                    ]
                ];
            }

            return [
                'status' => 'unhealthy',
                'message' => 'Smart API internal request failed',
                'details' => [
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Smart API check failed: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Check if internal API access is working
     */
    private function checkInternalApiAccess()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => env('SMART_API_KEY')
            ])->get(secure_url('/api/v1/dashboard'));

            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'message' => $response->successful() 
                    ? 'Internal API access is working' 
                    : 'Internal API access failed',
                'details' => [
                    'response_status' => $response->status(),
                    'endpoint' => '/api/v1/dashboard'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Internal API access check failed: ' . $e->getMessage(),
                'details' => [
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
}
