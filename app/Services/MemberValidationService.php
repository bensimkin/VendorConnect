<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemberValidationService
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mastermind.api_key');
        $this->apiUrl = config('services.mastermind.api_url', 'https://incomm.themastermind.com.au/api/portal/members/lookup/by-email');
    }

    /**
     * Check if an email belongs to an active Mastermind member
     */
    public function isActiveMember(string $email): bool
    {
        // If API key not configured, allow access (fallback for local dev)
        if (!$this->apiKey) {
            Log::warning('Mastermind API key not configured - allowing access');
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->apiUrl, [
                'email' => $email
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Mastermind member validation', [
                    'email' => $email,
                    'is_member' => $data['is_member'] ?? false,
                    'is_active' => $data['is_active'] ?? false,
                    'status' => $data['status'] ?? null
                ]);

                // Only block if we get a definitive "inactive" or "not found" response
                // If is_member is explicitly false, block access (member not found)
                if (isset($data['is_member']) && $data['is_member'] === false) {
                    return false;
                }

                // If is_active is explicitly false, block access (inactive member)
                if (isset($data['is_active']) && $data['is_active'] === false) {
                    return false;
                }

                // If is_active is explicitly true, allow access
                if (isset($data['is_active']) && $data['is_active'] === true) {
                    return true;
                }

                // If we don't get clear status indicators, allow access (fail open)
                Log::warning('Mastermind API returned unclear status - allowing access', [
                    'email' => $email,
                    'response' => $data
                ]);
                return true;
            }

            // On API failure (non-200 response), allow access (fail open)
            Log::warning('Mastermind API request failed - allowing access', [
                'email' => $email,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return true;

        } catch (\Exception $e) {
            // On exception (network error, timeout, etc.), allow access (fail open)
            Log::warning('Mastermind member validation error - allowing access', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return true;
        }
    }

    /**
     * Get full member details
     */
    public function getMemberDetails(string $email): ?array
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->apiUrl, [
                'email' => $email
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to fetch member details', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}

