<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Mail\PasswordResetMail;
use App\Mail\EmailVerificationMail;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login user and return token
     */
    public function login(Request $request)
    {
        // Rate limiting for login attempts - temporarily disabled for debugging
        // $this->validateLoginAttempts($request);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $validator->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            // $this->incrementLoginAttempts($request);
            return response()->json([
                'success' => false,
                'message' => 'Account not found!'
            ], 404);
        }

        if (!Auth::attempt($credentials)) {
            // $this->incrementLoginAttempts($request);
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials!'
            ], 401);
        }

        if ($user->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently inactive. Please contact admin for assistance.'
            ], 403);
        }

        // Clear any existing tokens for this user (optional - for single session)
        // $user->tokens()->delete();

        // Create token with expiration
        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        // Get user permissions
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Update last login
        $user->forceFill(['last_login_at' => now()])->save();

        // Track session (NEW CODE)
        try {
            UserSession::startSession(
                $user->id,
                $token,
                $request->ip(),
                $request->userAgent()
            );
        } catch (\Exception $e) {
            // Don't fail login if session tracking fails
            \Log::error('Session tracking failed on login', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'photo' => $user->photo,
                    'status' => $user->status,
                    'dark_mode' => $user->dark_mode,
                    'country_code' => $user->country_code,
                    'last_login_at' => $user->last_login_at,
                    'roles' => $user->roles()->select('id', 'name')->get()->toArray(),
                ],
                'permissions' => $permissions,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(7)->toISOString()
            ]
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Find active session and end it
        try {
            $token = $request->bearerToken();
            if ($token) {
                $session = UserSession::findByToken($token);
                if ($session && $session->user_id === $user->id) {
                    $session->endSession();
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to end session on logout', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
        
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found in our system'
            ], 404);
        }

        // Generate password reset token
        $token = Str::random(60);
        
        // Store the token in the password_resets table
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        // Send password reset email using Laravel Mail
        $resetUrl = config('app.url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        
        try {
            Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));
            
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to send reset link. Please try again later.'
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the password reset record
        $passwordReset = \DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        // Check if token is valid and not expired (60 minutes)
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token'
            ], 400);
        }

        // Check if token is expired
        if (now()->diffInMinutes($passwordReset->created_at) > 60) {
            \DB::table('password_resets')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Reset token has expired. Please request a new one.'
            ], 400);
        }

        // Find the user
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Update the user's password
        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        // Delete the password reset record
        \DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Verify email
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Send verification email using Laravel Mail
        $verificationUrl = config('app.url') . '/verify-email?id=' . $user->id . '&hash=' . sha1($user->getEmailForVerification());
        
        try {
            Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));
            
            return response()->json([
                'success' => true,
                'message' => 'Verification link sent'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to send verification email. Please try again later.'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_at' => now()->addDays(7)->toISOString()
            ]
        ]);
    }

    /**
     * Send welcome email to user
     */
    public function sendWelcomeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Send welcome email using Laravel Mail
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
            
            return response()->json([
                'success' => true,
                'message' => 'Welcome email sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to send welcome email. Please try again later.'
            ], 500);
        }
    }

    /**
     * Validate login attempts and apply rate limiting
     */
    private function validateLoginAttempts(Request $request)
    {
        $key = 'login_attempts_' . $request->ip();
        $attempts = cache()->get($key, 0);
        
        if ($attempts >= 5) {
            $lockoutTime = cache()->get($key . '_lockout', 0);
            if (now()->timestamp < $lockoutTime) {
                abort(429, 'Too many login attempts. Please try again later.');
            } else {
                cache()->forget($key);
                cache()->forget($key . '_lockout');
            }
        }
    }

    /**
     * Increment login attempts
     */
    private function incrementLoginAttempts(Request $request)
    {
        $key = 'login_attempts_' . $request->ip();
        $attempts = cache()->get($key, 0) + 1;
        
        cache()->put($key, $attempts, now()->addMinutes(15));
        
        if ($attempts >= 5) {
            cache()->put($key . '_lockout', now()->addMinutes(15)->timestamp, now()->addMinutes(15));
        }
    }
}
