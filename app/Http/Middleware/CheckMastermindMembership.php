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

        // Get the company (admin_id) for this user
        $adminId = getAdminIdByUserRole();
        
        if (!$adminId) {
            return $next($request);
        }

        // Find the company/admin record
        $admin = \App\Models\Admin::find($adminId);
        
        if (!$admin) {
            return $next($request);
        }

        // Get the company OWNER's user record
        $ownerUser = \App\Models\User::find($admin->user_id);
        
        if (!$ownerUser) {
            return $next($request);
        }

        // Validate the COMPANY OWNER's email (not the logged-in user's email)
        $memberService = new MemberValidationService();
        if (!$memberService->isActiveMember($ownerUser->email)) {
            Log::warning('Company with inactive Mastermind owner attempted access', [
                'logged_in_user_id' => $user->id,
                'logged_in_email' => $user->email,
                'company_owner_id' => $ownerUser->id,
                'company_owner_email' => $ownerUser->email,
                'admin_id' => $admin->id,
                'company_name' => $admin->company_name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your company owner\'s Mastermind membership is not active. VendorConnect is only available to companies with active member owners.',
                'membership_suspended' => true
            ], 403);
        }

        return $next($request);
    }
}

