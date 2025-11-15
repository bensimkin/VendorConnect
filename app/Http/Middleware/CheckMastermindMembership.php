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

        // Only validate ADMIN users (company owners), not team members
        // Get the admin (company owner) for this user
        $admin = \App\Models\Admin::where('user_id', $user->id)->first();
        
        // If user is not a company owner, skip validation (they're a team member)
        if (!$admin) {
            return $next($request);
        }

        // User is a company owner - validate their Mastermind membership
        $memberService = new MemberValidationService();
        if (!$memberService->isActiveMember($user->email)) {
            Log::warning('Inactive Mastermind member (company owner) attempted access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'admin_id' => $admin->id,
                'company_name' => $admin->company_name
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

