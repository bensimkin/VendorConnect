<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ForgotPassword;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        try {
            // Check if the email exists in the users table
            $userExists = User::where('email', $request->email)->exists();
            if(!$userExists)
            {
                return response()->json(['error' => true, 'message' => __('Email is not exist in database.')]);
            }


            $token = Str::random(60);
            User::where('email', $request->email)->update([
                'remember_token'    => $token
            ]);

            $resetUrl = $this->generateResetUrl($token, $request->email);

            if ($resetUrl) {
                // Session::flash('message', __($response));
                return response()->json(['error' => false, 'message' => __('Password reset link emailed successfully.')]);
            } else {
                return response()->json(['error' => true, 'message' => __('Something went wrong.')]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Something went wrong.']);
        }
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function ResetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);
        $provider = $this->determineProvider($request->email);
        if ($provider == 'users') {
            $status = Password::broker('users')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    // if (isEmailConfigured()) {
                    //     event(new PasswordReset($user));
                    // }
                }
            );
        } else {
            $status = Password::broker('clients')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (Client $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    // if (isEmailConfigured()) {
                    //     event(new PasswordReset($user));
                    // }
                }
            );
        }

        if ($status === Password::PASSWORD_RESET) {
            Session::flash('message', __($status));
            return response()->json(['error' => false]);
        } else {
            return response()->json(['error' => true, 'message' => __($status)]);
        }
    }

    protected function determineProvider($email)
    {
        // Determine whether the email belongs to a user or a client
        return User::where('email', $email)->exists() ? 'users' : (Client::where('email', $email)->exists() ? 'clients' : null);
    }

    // Generate the reset password URL
    protected function generateResetUrl($token, $email)
    {
        // Generate the URL with the token embedded in the path and email as a query parameter
        return url('/reset-password/' . $token) . '?' . http_build_query([
            'email' => $email,
        ]);
    }
}
