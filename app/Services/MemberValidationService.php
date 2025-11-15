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
        $this->apiKey = env('MASTERMIND_API_KEY');
        $this->apiUrl = 'https://incomm.themastermind.com.au/api/portal/members/lookup/by-email';
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

                // Only allow if is_active is explicitly true
                return isset($data['is_active']) && $data['is_active'] === true;
            }

            Log::error('Mastermind API request failed', [
                'email' => $email,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            // On API failure, deny access for security
            return false;

        } catch (\Exception $e) {
            Log::error('Mastermind member validation error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            // On exception, deny access for security
            return false;
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

