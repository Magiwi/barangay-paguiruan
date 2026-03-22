<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermitController extends Controller
{
    public function index()
    {
        $permits = auth()->user()->permits()->latest()->paginate(15);

        return view('resident.permits.index', compact('permits'));
    }

    public function create()
    {
        $permitTypes = config('permit_types');

        return view('resident.permits.create', compact('permitTypes'));
    }

    public function store(Request $request)
    {
        $permitTypes = config('permit_types');
        $validTypes = array_keys($permitTypes);

        // Determine document rule based on selected permit type
        $documentRule = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

        if ($request->filled('permit_type') && isset($permitTypes[$request->permit_type])) {
            if ($permitTypes[$request->permit_type]['requires_document']) {
                $documentRule = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
            }
        }

        $validated = $request->validate([
            'permit_type' => ['required', 'string', 'in:' . implode(',', $validTypes)],
            'document' => $documentRule,
        ], [
            'permit_type.in' => 'The selected permit type is invalid.',
            'document.required' => 'A supporting document is required for this permit type.',
        ]);

        $dynamicRules = $this->permitDynamicRules($validated['permit_type']);
        $request->validate($dynamicRules);

        $extraFields = [];
        foreach (array_keys($dynamicRules) as $field) {
            if ($request->filled($field) || is_numeric($request->input($field))) {
                $extraFields[$field] = $request->input($field);
            }
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';
        $purpose = $request->input('purpose');
        if ($purpose === 'Others' && $request->filled('purpose_other')) {
            $purpose = (string) $request->input('purpose_other');
        }
        $validated['purpose'] = $purpose ?: ($extraFields['purpose'] ?? 'Permit application');
        $validated['extra_fields'] = $extraFields;

        if ($request->hasFile('document')) {
            $validated['document_path'] = $request->file('document')->store('permits', 'public');
        }
        unset($validated['document']);

        Permit::create($validated);

        return redirect()->route('resident.permits.index')
            ->with('success', 'Permit application submitted successfully.');
    }

    private function permitDynamicRules(string $permitType): array
    {
        return match ($permitType) {
            'Business Permit' => [
                'business_name' => ['required', 'string', 'max:255'],
                'business_address' => ['required', 'string', 'max:255'],
                'purpose' => ['required', 'string', Rule::in([
                    'New Business Registration',
                    'Business Permit Renewal',
                    'Change of Business Address',
                    'Change of Business Name',
                    'Change of Ownership',
                    'Additional Line of Business',
                    'Closure / Cessation of Business',
                    'Compliance Requirement (BIR / DTI / SEC / LGU)',
                    'Loan / Financing Requirement',
                    'Others',
                ])],
                'purpose_other' => ['nullable', 'string', 'max:2000', Rule::requiredIf((string) request('purpose') === 'Others')],
                'previous_permit_number' => ['nullable', 'string', 'max:100'],
                'last_permit_year' => ['nullable', 'integer', 'min:1900', 'max:' . now()->year],
                'old_business_address' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Change of Business Address')],
                'new_business_address' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Change of Business Address')],
                'old_business_name' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Change of Business Name')],
                'new_business_name' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Change of Business Name')],
                'previous_owner_name' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Change of Ownership')],
                'new_owner_name' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Change of Ownership')],
                'current_line_of_business' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Additional Line of Business')],
                'additional_line_of_business' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Additional Line of Business')],
                'closure_effective_date' => ['nullable', 'date', Rule::requiredIf((string) request('purpose') === 'Closure / Cessation of Business')],
                'closure_reason' => ['nullable', 'string', 'max:1000', Rule::requiredIf((string) request('purpose') === 'Closure / Cessation of Business')],
                'agency_name' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Compliance Requirement (BIR / DTI / SEC / LGU)')],
                'reference_number' => ['nullable', 'string', 'max:100'],
                'financing_institution' => ['nullable', 'string', 'max:255', Rule::requiredIf((string) request('purpose') === 'Loan / Financing Requirement')],
                'financing_reference_number' => ['nullable', 'string', 'max:100'],
            ],
            'Event Permit' => [
                'event_name' => ['required', 'string', 'max:255'],
                'event_date' => ['required', 'date'],
                'purpose' => ['required', 'string', 'max:2000'],
            ],
            'Building Permit' => [
                'project_type' => ['required', 'string', 'max:255'],
                'project_location' => ['required', 'string', 'max:255'],
                'purpose' => ['required', 'string', 'max:2000'],
            ],
            default => [
                'purpose' => ['required', 'string', 'max:2000'],
            ],
        };
    }
}
