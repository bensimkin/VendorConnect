<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\MemberValidationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckMastermindMembership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check for demo mode or if API key not configured
        if (env('APP_ENV') === 'demo' || !env('MASTERMIND_API_KEY')) {
            return $next($request);
        }

        // Skip for non-authenticated requests and login/register routes
        $skipRoutes = ['api/v1/auth/login', 'api/v1/company/register', 'api/v1/auth/forgot-password'];
        if (in_array($request->path(), $skipRoutes)) {
            return $next($request);
        }

        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return $next($request);
        }

        // Check if user is an active Mastermind member
        $memberService = new MemberValidationService();
        if (!$memberService->isActiveMember($user->email)) {
            Log::warning('Inactive Mastermind member attempted access', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your Mastermind membership is not active. VendorConnect is only available to active members.',
                'membership_suspended' => true
            ], 403);
        }

        return $next($request);
    }
}

