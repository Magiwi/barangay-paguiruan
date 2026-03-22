<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\CertificateRequest;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    public function index()
    {
        $requests = auth()->user()->certificateRequests()->latest()->get();

        return view('resident.certificates.index', compact('requests'));
    }

    public function create()
    {
        $user = auth()->user();
        $isHead = $user->canManageOwnFamily();
        $minorOptions = $isHead ? $this->buildMinorHouseholdOptions($user) : collect();
        $minorEligibleCertificateTypes = $this->minorEligibleCertificateTypes();

        return view('resident.certificates.create', [
            'isHead' => $isHead,
            'minorOptions' => $minorOptions,
            'minorEligibleCertificateTypes' => $minorEligibleCertificateTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'certificate_type' => ['required', 'string', Rule::in([
                'Residency Certificate',
                'Certificate of Indigency',
                'Barangay Clearance',
                'Barangay Certificate',
            ])],
        ]);

        $dynamicRules = $this->certificateDynamicRules($validated['certificate_type']);
        $request->validate($dynamicRules);

        $extraFields = [];
        foreach (array_keys($dynamicRules) as $field) {
            if ($field === 'valid_id' && $request->hasFile('valid_id')) {
                $extraFields['valid_id_path'] = $request->file('valid_id')->store('certificates/ids', 'public');
                continue;
            }
            if ($request->filled($field) || is_numeric($request->input($field))) {
                $extraFields[$field] = $request->input($field);
            }
        }

        $actor = $request->user();
        $policy = $this->certificateRequestPolicy((string) $validated['certificate_type']);
        $this->assertActorMeetsAgePolicy($actor, $policy['min_age']);
        $requestForPayload = $this->resolveRequestForPayload(
            actor: $actor,
            certificateType: (string) $validated['certificate_type'],
            selectedFamilyMemberId: $request->input('request_for_member_id')
        );
        $extraFields = array_merge($extraFields, $requestForPayload);

        $residencyYears = $request->input('residency_years_text');
        if (! $residencyYears && isset($extraFields['residency_start_year'])) {
            $residencyYears = $this->computeResidencyYearsText((int) $extraFields['residency_start_year']);
        }
        if (! $residencyYears && isset($extraFields['residency_years_text'])) {
            $residencyYears = (string) $extraFields['residency_years_text'];
        }

        $purpose = $request->input('purpose');
        if ($purpose === 'Others' && $request->filled('purpose_other')) {
            $purpose = (string) $request->input('purpose_other');
        }
        if (! $purpose) {
            $purpose = $extraFields['purpose'] ?? 'Certificate request';
        }

        CertificateRequest::create([
            'user_id' => $actor->id,
            'certificate_type' => $validated['certificate_type'],
            'purpose' => (string) $purpose,
            'residency_years_text' => $residencyYears,
            'extra_fields' => $extraFields,
            'status' => 'pending',
        ]);

        return redirect()->route('resident.certificates.index')
            ->with('success', 'Certificate request submitted successfully.');
    }

    private function certificateDynamicRules(string $certificateType): array
    {
        $generalPurposeRules = ['required', 'string', Rule::in([
            'School requirements (enrollment, scholarship)',
            'Job application',
            'Pagkuha ng government ID',
            'Legal documents',
            'Others',
        ])];

        $indigencyPurposeRules = ['required', 'string', Rule::in([
            'Medical Assistance',
            'Financial Assistance',
            'Scholarship',
            'Burial Assistance',
            'Others',
        ])];

        $purposeOtherRules = ['nullable', 'string', 'max:1000', Rule::requiredIf((string) request('purpose') === 'Others')];

        return match (strtolower($certificateType)) {
            'residency certificate' => [
                'purpose' => $generalPurposeRules,
                'purpose_other' => $purposeOtherRules,
                'residency_start_year' => ['required', 'integer', 'min:1900', 'max:' . now()->year],
            ],
            'certificate of indigency' => [
                'purpose' => $indigencyPurposeRules,
                'purpose_other' => $purposeOtherRules,
                'monthly_income' => ['required', 'string', Rule::in([
                    'Below ₱5,000',
                    '₱5,000 – ₱9,999',
                    '₱10,000 – ₱14,999',
                    '₱15,000 – ₱19,999',
                    '₱20,000 and above',
                    'No Income',
                ])],
            ],
            'barangay clearance' => [
                'purpose' => $generalPurposeRules,
                'purpose_other' => $purposeOtherRules,
                'valid_id' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            ],
            default => [
                'purpose' => $generalPurposeRules,
                'purpose_other' => $purposeOtherRules,
            ],
        };
    }

    /**
     * @return array{min_age:int,allow_minor_request_for:bool}
     */
    private function certificateRequestPolicy(string $certificateType): array
    {
        $normalized = strtolower(trim($certificateType));

        return match ($normalized) {
            'barangay clearance' => ['min_age' => 18, 'allow_minor_request_for' => false],
            'certificate of indigency' => ['min_age' => 0, 'allow_minor_request_for' => true],
            'residency certificate' => ['min_age' => 0, 'allow_minor_request_for' => true],
            'barangay certificate' => ['min_age' => 0, 'allow_minor_request_for' => true],
            default => ['min_age' => 18, 'allow_minor_request_for' => false],
        };
    }

    /**
     * @return array<int, string>
     */
    private function minorEligibleCertificateTypes(): array
    {
        return collect([
            'Residency Certificate',
            'Certificate of Indigency',
            'Barangay Certificate',
            'Barangay Clearance',
        ])->filter(function (string $certificateType): bool {
            $policy = $this->certificateRequestPolicy($certificateType);

            return $policy['allow_minor_request_for'] === true;
        })->values()->all();
    }

    private function assertActorMeetsAgePolicy(User $actor, int $minAge): void
    {
        if ($minAge <= 0) {
            return;
        }

        $age = $actor->birthdate ? Carbon::parse($actor->birthdate)->age : (int) ($actor->age ?? 0);
        if ($age < $minAge) {
            throw ValidationException::withMessages([
                'certificate_type' => "You must be at least {$minAge} years old to request this certificate.",
            ]);
        }
    }

    /**
     * Pre-filter family member records under the same household to minors only.
     *
     * @return Collection<int, array{id:int,name:string,birthdate:?string,age:?int}>
     */
    private function buildMinorHouseholdOptions(User $head): Collection
    {
        if (! $head->household_id) {
            return collect();
        }

        return FamilyMember::query()
            ->where('household_id', $head->household_id)
            ->whereNull('deleted_at')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (FamilyMember $member): array {
                $birthdate = $member->birthdate ? Carbon::parse($member->birthdate) : null;
                $age = $birthdate?->age;

                return [
                    'id' => (int) $member->id,
                    'name' => $this->formatFamilyMemberName($member),
                    'birthdate' => $birthdate?->toDateString(),
                    'age' => is_int($age) ? $age : null,
                ];
            })
            ->filter(fn (array $member): bool => is_int($member['age']) && $member['age'] < 18)
            ->values();
    }

    /**
     * Resolve and validate "request for" payload stored in extra_fields.
     *
     * @return array<string,mixed>
     */
    private function resolveRequestForPayload(User $actor, string $certificateType, mixed $selectedFamilyMemberId): array
    {
        $policy = $this->certificateRequestPolicy($certificateType);

        if (! $actor->canManageOwnFamily() || ! $actor->household_id) {
            return ['request_for_type' => 'self'];
        }

        if (! $policy['allow_minor_request_for']) {
            return ['request_for_type' => 'self'];
        }

        $memberId = (int) $selectedFamilyMemberId;
        if ($memberId <= 0) {
            // Keep self as the default path even for minor-related certificates.
            return ['request_for_type' => 'self'];
        }

        $member = FamilyMember::query()
            ->where('id', $memberId)
            ->where('household_id', $actor->household_id)
            ->whereNull('deleted_at')
            ->first();

        if (! $member || ! $member->birthdate) {
            throw ValidationException::withMessages([
                'request_for_member_id' => 'Selected household member is invalid.',
            ]);
        }

        $age = Carbon::parse($member->birthdate)->age;
        if ($age >= 18) {
            throw ValidationException::withMessages([
                'request_for_member_id' => 'Selected household member is not a minor.',
            ]);
        }

        return [
            'request_for_type' => 'minor_family_member',
            'request_for_member_source' => 'family_members',
            'request_for_member_id' => (int) $member->id,
            'request_for_name' => $this->formatFamilyMemberName($member),
            'request_for_birthdate' => Carbon::parse($member->birthdate)->toDateString(),
            'request_for_age' => $age,
        ];
    }

    private function formatFamilyMemberName(FamilyMember $member): string
    {
        return trim(implode(' ', array_filter([
            $member->first_name,
            $member->middle_name,
            $member->last_name,
            $member->suffix,
        ])));
    }

    private function computeResidencyYearsText(?int $startYear): ?string
    {
        if (! $startYear) {
            return null;
        }

        $currentYear = now()->year;
        $years = max(1, $currentYear - $startYear + 1);

        return $years === 1 ? '1 year up to present' : "{$years} years up to present";
    }
}
