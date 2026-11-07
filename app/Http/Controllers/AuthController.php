<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\Purok;
use App\Models\User;
use App\Services\ResidentRegistrationService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Throwable;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Please enter your email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($validated, $remember)) {
            $user = Auth::user();

            // Block suspended accounts
            if ($user->is_suspended ?? false) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $this->logLoginActivity($request, $user->id, LoginActivity::STATUS_BLOCKED);

                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact the barangay office.',
                ])->onlyInput('email');
            }

            // Block non-approved accounts
            if ($user->status !== User::STATUS_APPROVED) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $this->logLoginActivity($request, $user->id, LoginActivity::STATUS_BLOCKED);

                return back()->withErrors([
                    'email' => 'Your registration is pending approval by the barangay.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            $this->logLoginActivity($request, $user->id, LoginActivity::STATUS_SUCCESS);

            if (in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'You are now logged in.');
            }

            if ($user->role === User::ROLE_STAFF) {
                return redirect()->intended(route('staff.dashboard'))->with('success', 'You are now logged in.');
            }

            if ($user->mustUploadGovernmentId()) {
                return redirect()->route('resident.government-id.edit')
                    ->with('success', 'You are now logged in. Upload your government ID to continue.');
            }

            return redirect()->intended(route('resident.dashboard'))->with('success', 'You are now logged in.');
        }

        $this->logLoginActivity($request, null, LoginActivity::STATUS_FAILED);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log a login activity record.
     */
    private function logLoginActivity(Request $request, ?int $userId, string $status): void
    {
        try {
            LoginActivity::create([
                'user_id' => $userId,
                'email_attempted' => $request->input('email'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            // Silently fail — login must never break due to logging
        }
    }

    /**
     * Handle logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    /**
     * Show the change password form for authenticated users.
     *
     * @return \Illuminate\View\View
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle an in-system password change for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        // Update password (hashed cast will hash the value)
        $user->password = $validated['password'];
        $user->save();

        // Invalidate other active sessions
        Auth::logoutOtherDevices($validated['password']);

        $request->session()->regenerate();

        return back()->with('success', 'Your password has been updated successfully.');
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm()
    {
        $puroks = Purok::active()->orderBy('name')->get();

        return view('auth.register', compact('puroks'));
    }

    /**
     * Handle user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        app(ResidentRegistrationService::class)->register($request);

        return redirect()->route('login')->with('success', 'Registration submitted. Your account is pending approval. You will be able to log in once approved.');
    }

    /**
     * Show the \"forgot password\" request form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending a password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Always respond with a generic message to avoid exposing user existence.
            PasswordBroker::sendResetLink(
                $request->only('email')
            );
        } catch (Throwable $exception) {
            // Do not break the UX on transient mail transport/DNS errors.
            Log::warning('Password reset link dispatch failed.', [
                'email' => (string) $request->input('email'),
                'error' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'If your email is registered, a password reset link has been sent.');
    }

    /**
     * Show the password reset form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPasswordForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle an incoming new password submission from a reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset. You can now log in.');
        }

        return back()->withErrors([
            'email' => 'The password reset link is invalid or has expired.',
        ]);
    }
}
