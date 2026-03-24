<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $relationshipOptions = [
            'son',
            'daughter',
            'spouse',
            'father',
            'mother',
            'brother',
            'sister',
            'grandfather',
            'grandmother',
            'uncle',
            'aunt',
            'cousin',
            'nephew',
            'niece',
            'guardian',
            'boarder',
            'helper',
            'other',
        ];

        $householdConnectionTypes = array_keys(User::HOUSEHOLD_CONNECTION_TYPE_LABELS);

        $requestedIsPwd = $request->input('is_pwd', 'no') === 'yes';
        $requestedIsSenior = $request->input('is_senior', 'no') === 'yes';
        $requiresGovernmentId = ! $requestedIsPwd && ! $requestedIsSenior;

        $rules = [
            // Personal Information
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'in:Jr.,Sr.,I,II,III,IV'],
            // Address Information
            'house_no' => ['required', 'string', 'max:255'],
            'purok_id' => ['required', 'integer', 'exists:puroks,id'],
            'sitio_subdivision' => ['nullable', 'string', 'max:255'],
            // Contact & Demographics
            'contact_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'birthdate' => ['required', 'date', 'before:today', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'civil_status' => ['required', 'string', 'in:single,married,widowed,divorced,separated'],
            'head_of_family' => ['required', 'string', 'in:yes,no'],
            'resident_type' => ['required', 'string', 'in:permanent,non-permanent'],
            // Resident Classification
            'is_pwd' => ['nullable', 'string', 'in:yes,no'],
            'is_senior' => ['nullable', 'string', 'in:yes,no'],
            'pwd_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'senior_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'government_id_type' => ['nullable', 'string', 'in:national_id,passport,drivers_license,umid,philhealth,postal_id,voters_id'],
            'government_id_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            // Account Information
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            // Data Privacy Consent
            'privacy_consent' => ['accepted'],
            // Conditional (validated only when applicable)
            'head_first_name' => ['nullable', 'string', 'max:255'],
            'head_middle_name' => ['nullable', 'string', 'max:255'],
            'head_last_name' => ['nullable', 'string', 'max:255'],
            'relationship_to_head' => ['nullable', 'string', Rule::in($relationshipOptions)],
            'household_connection_type' => ['nullable', 'string', Rule::in($householdConnectionTypes)],
            'connection_note' => ['nullable', 'string', 'max:255'],
            'permanent_house_no' => ['nullable', 'string', 'max:255'],
            'permanent_street' => ['nullable', 'string', 'max:255'],
            'permanent_region' => ['nullable', 'string', 'max:255'],
            'permanent_barangay' => ['nullable', 'string', 'max:255'],
            'permanent_city' => ['nullable', 'string', 'max:255'],
            'permanent_province' => ['nullable', 'string', 'max:255'],
        ];

        if ($request->input('head_of_family') === 'no') {
            $rules['head_first_name'] = ['required', 'string', 'max:255'];
            $rules['head_last_name'] = ['required', 'string', 'max:255'];
            $rules['household_connection_type'] = ['required', 'string', Rule::in($householdConnectionTypes)];
        }

        if ($request->input('head_of_family') === 'no' && $request->input('household_connection_type') === 'family_member') {
            $rules['relationship_to_head'] = ['required', 'string', Rule::in($relationshipOptions)];
        }

        if ($request->input('head_of_family') === 'no' && $request->input('household_connection_type') === 'other') {
            $rules['connection_note'] = ['required', 'string', 'max:255'];
        }

        if ($request->input('resident_type') === 'non-permanent') {
            $rules['permanent_house_no'] = ['required', 'string', 'max:255'];
            $rules['permanent_street'] = ['required', 'string', 'max:255'];
            $rules['permanent_region'] = ['required', 'string', 'max:255'];
            $rules['permanent_barangay'] = ['required', 'string', 'max:255'];
            $rules['permanent_city'] = ['required', 'string', 'max:255'];
            $rules['permanent_province'] = ['required', 'string', 'max:255'];
        }

        if ($requestedIsPwd) {
            $rules['pwd_proof'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        }

        if ($requestedIsSenior) {
            $rules['senior_proof'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        }

        if ($requestedIsPwd || $requestedIsSenior) {
            $rules['government_id_type'] = ['nullable', 'string', 'in:national_id,passport,drivers_license,umid,philhealth,postal_id,voters_id'];
            $rules['government_id_proof'] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
        } else {
            $rules['government_id_type'] = ['required', 'string', 'in:national_id,passport,drivers_license,umid,philhealth,postal_id,voters_id'];
            $rules['government_id_proof'] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
        }

        $validated = $request->validate($rules, [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'house_no.required' => 'House number is required.',
            'purok_id.required' => 'Please select a purok.',
            'purok_id.exists' => 'The selected purok is invalid.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Enter exactly 10 digits (e.g. 9171234567).',
            'gender.required' => 'Please select a gender.',
            'birthdate.required' => 'Birthdate is required.',
            'birthdate.before' => 'Birthdate cannot be today or in the future.',
            'birthdate.before_or_equal' => 'You must be at least 18 years old to register.',
            'civil_status.required' => 'Please select a civil status.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'privacy_consent.accepted' => 'You must agree to the Data Privacy Act to register.',
            'pwd_proof.mimes' => 'PWD proof must be a JPG, PNG, or PDF file.',
            'pwd_proof.max' => 'PWD proof must not exceed 2MB.',
            'pwd_proof.required' => 'PWD proof is required when PWD is set to Yes.',
            'senior_proof.mimes' => 'Senior proof must be a JPG, PNG, or PDF file.',
            'senior_proof.max' => 'Senior proof must not exceed 2MB.',
            'senior_proof.required' => 'Senior proof is required when Senior Citizen is set to Yes.',
            'government_id_type.required' => 'Please select a government ID type.',
            'government_id_type.in' => 'The selected government ID type is invalid.',
            'government_id_proof.required' => 'Government ID upload is required.',
            'government_id_proof.mimes' => 'Government ID proof must be a JPG or PNG file.',
            'government_id_proof.max' => 'Government ID proof must not exceed 2MB.',
            'government_id_proof.uploaded' => 'Government ID upload failed. Please use a smaller image (max 2MB) and try again.',
            'pwd_proof.uploaded' => 'PWD proof upload failed. Please use a smaller file (max 2MB) and try again.',
            'senior_proof.uploaded' => 'Senior proof upload failed. Please use a smaller file (max 2MB) and try again.',
            'household_connection_type.required' => 'Please select your household connection type.',
            'household_connection_type.in' => 'The selected household connection type is invalid.',
            'relationship_to_head.required' => 'Please select your relationship to the household head.',
            'relationship_to_head.in' => 'The selected relationship to head is invalid.',
            'connection_note.required' => 'Please provide details when connection type is Other.',
            'permanent_region.required' => 'Permanent region is required for non-permanent residents.',
            'permanent_province.required' => 'Permanent province is required for non-permanent residents.',
            'permanent_city.required' => 'Permanent city is required for non-permanent residents.',
            'permanent_barangay.required' => 'Permanent barangay is required for non-permanent residents.',
        ]);

        // Calculate age from birthdate
        $validated['age'] = \Carbon\Carbon::parse($validated['birthdate'])->age;

        // Set the purok string for backwards compatibility
        $purok = Purok::find($validated['purok_id']);
        $validated['purok'] = $purok ? $purok->name : null;
        // Backward compatibility while users.street_name remains NOT NULL in schema.
        $validated['street_name'] = 'N/A';

        // Prepend country code to contact number
        $validated['contact_number'] = '+63' . $validated['contact_number'];

        // Null out suffix if not selected
        if (empty($validated['suffix'])) {
            $validated['suffix'] = null;
        }

        if ($validated['head_of_family'] === 'yes') {
            $validated['head_first_name'] = null;
            $validated['head_middle_name'] = null;
            $validated['head_last_name'] = null;
            $validated['relationship_to_head'] = null;
            $validated['household_connection_type'] = null;
            $validated['connection_note'] = null;
        } else {
            $validated['household_connection_type'] = $validated['household_connection_type'] ?? null;

            if (($validated['household_connection_type'] ?? null) !== 'family_member') {
                $validated['relationship_to_head'] = null;
            } elseif (! empty($validated['relationship_to_head'])) {
                $validated['relationship_to_head'] = strtolower(trim((string) $validated['relationship_to_head']));
            }

            if (($validated['household_connection_type'] ?? null) !== 'other') {
                $validated['connection_note'] = null;
            }
        }
        if ($validated['resident_type'] === 'permanent') {
            $validated['permanent_house_no'] = null;
            $validated['permanent_street'] = null;
            $validated['permanent_region'] = null;
            $validated['permanent_barangay'] = null;
            $validated['permanent_city'] = null;
            $validated['permanent_province'] = null;
        }

        // Convert classification radio values to booleans
        $isPwd = ($validated['is_pwd'] ?? 'no') === 'yes';
        $isSenior = ($validated['is_senior'] ?? 'no') === 'yes';

        $validated['is_pwd'] = $isPwd;
        $validated['is_senior'] = $isSenior;

        // Handle PWD proof upload
        if ($isPwd && $request->hasFile('pwd_proof')) {
            $validated['pwd_proof_path'] = $request->file('pwd_proof')->store('classification-proofs/pwd', 'public');
            $validated['pwd_status'] = 'pending';
        } elseif ($isPwd) {
            $validated['pwd_status'] = 'not_submitted';
        }
        unset($validated['pwd_proof']);

        // Handle Senior proof upload
        if ($isSenior && $request->hasFile('senior_proof')) {
            $validated['senior_proof_path'] = $request->file('senior_proof')->store('classification-proofs/senior', 'public');
            $validated['senior_status'] = 'pending';
        } elseif ($isSenior) {
            $validated['senior_status'] = 'not_submitted';
        }
        unset($validated['senior_proof']);

        if (! $isPwd && ! $isSenior) {
            if ($request->hasFile('government_id_proof')) {
                $validated['government_id_path'] = $request->file('government_id_proof')->store('id-proofs/government', 'public');
            }
        } else {
            $validated['government_id_type'] = null;
            $validated['government_id_path'] = null;
        }
        unset($validated['government_id_proof']);

        // Remove non-model fields before creation
        unset($validated['privacy_consent']);

        // Create user then forceFill protected fields (role/status not in $fillable)
        $user = User::create($validated);
        $user->forceFill([
            'role' => User::ROLE_RESIDENT,
            'status' => User::STATUS_PENDING,
        ])->save();

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
