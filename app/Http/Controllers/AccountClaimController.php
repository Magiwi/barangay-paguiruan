<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ResidentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountClaimController extends Controller
{
    public function create(): View
    {
        return view('auth.claim');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ], [
            'code.required' => 'Enter the code from the barangay office.',
        ]);

        $user = app(ResidentRegistrationService::class)->findByClaimCode($validated['code']);
        if (! $user || ! $user->hasUnclaimedAccount()) {
            throw ValidationException::withMessages([
                'code' => 'That code is not valid. Ask the barangay office for a new one.',
            ]);
        }

        $request->session()->put('claim_user_id', $user->id);

        return redirect()->route('account.claim.setup');
    }

    public function setup(Request $request): View|RedirectResponse
    {
        $user = $this->unclaimedUser($request);
        if (! $user) {
            return redirect()->route('account.claim');
        }

        return view('auth.claim-setup', ['user' => $user]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $user = $this->unclaimedUser($request);
        if (! $user) {
            return redirect()->route('account.claim');
        }

        $request->merge([
            'contact_number' => app(ResidentRegistrationService::class)->normalizePhilippineMobile($request->input('contact_number')),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'contact_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Enter a valid mobile number (e.g. 917 123 4567).',
        ]);

        $user->forceFill([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'contact_number' => '+63' . $validated['contact_number'],
            'claim_code_hash' => null,
        ])->save();

        $request->session()->forget('claim_user_id');
        Auth::login($user->fresh());
        $request->session()->regenerate();

        if ($user->fresh()->mustUploadGovernmentId()) {
            return redirect()->route('resident.government-id.edit')
                ->with('success', 'Account created. Upload your government ID to continue.');
        }

        return redirect()->route('resident.dashboard')
            ->with('success', 'Your account is ready.');
    }

    private function unclaimedUser(Request $request): ?User
    {
        $userId = $request->session()->get('claim_user_id');
        if (! $userId) {
            return null;
        }

        $user = User::query()->find($userId);

        return $user && $user->hasUnclaimedAccount() ? $user : null;
    }
}
