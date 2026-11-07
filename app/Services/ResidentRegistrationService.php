<?php

namespace App\Services;

use App\Http\Controllers\Admin\PendingRegistrationsController;
use App\Models\ApprovalLog;
use App\Models\Purok;
use App\Models\User;
use App\Support\NameFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ResidentRegistrationService
{
    public const GOVERNMENT_ID_TYPES = 'national_id,passport,drivers_license,umid,philhealth,postal_id,voters_id';

    public ?string $issuedClaimCode = null;

    /**
     * Create a resident from the public form or an on-site staff form.
     * Public accounts stay pending. On-site accounts are approved immediately.
     */
    public function register(Request $request, bool $assisted = false): User
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
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'in:Jr.,Sr.,I,II,III,IV'],
            'house_no' => ['required', 'string', 'max:255'],
            'purok_id' => ['required', 'integer', 'exists:puroks,id'],
            'sitio_subdivision' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'birthdate' => ['required', 'date', 'before:today', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'civil_status' => ['required', 'string', 'in:single,married,widowed,divorced,separated'],
            'head_of_family' => ['required', 'string', 'in:yes,no'],
            'resident_type' => ['required', 'string', 'in:permanent,non-permanent'],
            'is_pwd' => ['nullable', 'string', 'in:yes,no'],
            'is_senior' => ['nullable', 'string', 'in:yes,no'],
            'pwd_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'senior_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'government_id_type' => ['nullable', 'string', 'in:' . self::GOVERNMENT_ID_TYPES],
            'government_id_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'email' => $assisted
                ? ['nullable']
                : ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $assisted
                ? ['nullable']
                : ['required', 'string', Password::min(8), 'confirmed'],
            'privacy_consent' => ['accepted'],
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

        if ($requiresGovernmentId) {
            $rules['government_id_type'] = ['required', 'string', 'in:' . self::GOVERNMENT_ID_TYPES];
            $rules['government_id_proof'] = $assisted
                ? ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048']
                : ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'];
        }

        $validator = Validator::make($request->all(), $rules, $this->messages());
        $validator->after(function ($validator) use ($assisted, $requiresGovernmentId, $request) {
            if (! $assisted || ! $requiresGovernmentId || $request->hasFile('government_id_proof')) {
                return;
            }

            if (! $request->boolean('id_verified_in_person')) {
                $validator->errors()->add(
                    'id_verified_in_person',
                    'Confirm that the government ID was verified in person, or upload a copy.'
                );
            }
        });
        $validated = $validator->validate();

        $validated['first_name'] = NameFormatter::properCase($validated['first_name'] ?? null);
        $validated['middle_name'] = NameFormatter::properCase($validated['middle_name'] ?? null);
        $validated['last_name'] = NameFormatter::properCase($validated['last_name'] ?? null);
        $validated['suffix'] = NameFormatter::formatSuffix($validated['suffix'] ?? null);
        $validated['head_first_name'] = NameFormatter::properCase($validated['head_first_name'] ?? null);
        $validated['head_middle_name'] = NameFormatter::properCase($validated['head_middle_name'] ?? null);
        $validated['head_last_name'] = NameFormatter::properCase($validated['head_last_name'] ?? null);

        $validated['age'] = \Carbon\Carbon::parse($validated['birthdate'])->age;

        $purok = Purok::find($validated['purok_id']);
        $validated['purok'] = $purok ? $purok->name : null;
        $validated['street_name'] = 'N/A';

        $validated['contact_number'] = '+63' . $validated['contact_number'];

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

        $isPwd = ($validated['is_pwd'] ?? 'no') === 'yes';
        $isSenior = ($validated['is_senior'] ?? 'no') === 'yes';

        $validated['is_pwd'] = $isPwd;
        $validated['is_senior'] = $isSenior;

        if ($isPwd && $request->hasFile('pwd_proof')) {
            $validated['pwd_proof_path'] = $request->file('pwd_proof')->store('classification-proofs/pwd', 'public');
            $validated['pwd_status'] = 'pending';
        } elseif ($isPwd) {
            $validated['pwd_status'] = 'not_submitted';
        }
        unset($validated['pwd_proof']);

        if ($isSenior && $request->hasFile('senior_proof')) {
            $validated['senior_proof_path'] = $request->file('senior_proof')->store('classification-proofs/senior', 'public');
            $validated['senior_status'] = 'pending';
        } elseif ($isSenior) {
            $validated['senior_status'] = 'not_submitted';
        }
        unset($validated['senior_proof']);

        $hasGovernmentIdFile = ! $isPwd && ! $isSenior && $request->hasFile('government_id_proof');
        if ($hasGovernmentIdFile) {
            $validated['government_id_path'] = $request->file('government_id_proof')->store('id-proofs/government', 'public');
        } elseif ($isPwd || $isSenior) {
            $validated['government_id_type'] = null;
            $validated['government_id_path'] = null;
        }
        unset($validated['government_id_proof']);
        unset($validated['privacy_consent']);

        if ($assisted) {
            $validated['email'] = null;
            $validated['password'] = Str::random(40);
        }

        $idVerifiedInPerson = $assisted
            && $requiresGovernmentId
            && ! $hasGovernmentIdFile
            && $request->boolean('id_verified_in_person');

        $user = User::create($validated);
        $user->forceFill([
            'role' => User::ROLE_RESIDENT,
            'status' => $assisted ? User::STATUS_APPROVED : User::STATUS_PENDING,
            'registered_by' => $assisted ? $request->user()?->id : null,
            'id_verified_in_person' => $idVerifiedInPerson,
        ])->save();

        if ($assisted) {
            app(PendingRegistrationsController::class)->attemptFamilyLink($user->fresh());

            ApprovalLog::create([
                'user_id' => $user->id,
                'action' => ApprovalLog::ACTION_APPROVED,
                'performed_by' => $request->user()?->id,
                'remarks' => 'Registered on site.',
            ]);

            $this->issuedClaimCode = $this->issueClaimCode($user);

            $registrar = $request->user();
            AuditService::log(
                'assisted_registration',
                $user,
                sprintf(
                    'Registered on site by %s for %s. Account activation is pending.%s',
                    $registrar?->full_name ?? 'staff',
                    $user->full_name,
                    $idVerifiedInPerson ? ' Government ID verified in person.' : ''
                )
            );
        }

        return $user->fresh();
    }

    public function issueClaimCode(User $user): string
    {
        $plain = $this->generateClaimCode();
        $user->forceFill([
            'claim_code_hash' => Hash::make($this->normalizeClaimCode($plain)),
        ])->save();

        return $this->formatClaimCode($plain);
    }

    public function findByClaimCode(string $code): ?User
    {
        $normalized = $this->normalizeClaimCode($code);
        if ($normalized === '') {
            return null;
        }

        return User::query()
            ->whereNotNull('claim_code_hash')
            ->orderByDesc('id')
            ->get()
            ->first(fn (User $user) => Hash::check($normalized, (string) $user->claim_code_hash));
    }

    public function formatClaimCode(string $code): string
    {
        $normalized = $this->normalizeClaimCode($code);

        return substr($normalized, 0, 4) . '-' . substr($normalized, 4);
    }

    private function generateClaimCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    private function normalizeClaimCode(string $code): string
    {
        return (string) preg_replace('/[^A-Z0-9]/', '', strtoupper($code));
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
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
        ];
    }
}
