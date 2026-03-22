@extends('layouts.resident')

@section('title', 'Apply for Permit - e-Governance System')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">

        {{-- Back link --}}
        <a href="{{ route('resident.permits.index') }}" class="ui-focus-ring mb-6 inline-flex items-center gap-1.5 rounded-md text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back to My Permits
        </a>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <p class="text-sm font-medium text-red-800">Please correct the following errors:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1 ml-7">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Card --}}
        <form method="POST" action="{{ route('resident.permits.store') }}" enctype="multipart/form-data" class="ui-surface-card overflow-hidden">
            @csrf

            {{-- Card Header --}}
            <div class="border-b border-gray-200 bg-gray-50/60 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="ui-icon-chip bg-emerald-100 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Apply for Permit</h1>
                        <p class="mt-0.5 text-sm text-gray-500">Submit a permit application for barangay processing and approval.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">

                {{-- Section 1: Permit Information --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Permit Information</h3>
                    </div>

                    <div>
                        <label for="permit_type" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Permit Type <span class="text-red-500">*</span>
                        </label>
                        <select name="permit_type" id="permit_type" required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('permit_type') border-red-400 ring-red-100 @enderror">
                            <option value="">Select permit type</option>
                            @foreach ($permitTypes as $name => $config)
                                <option value="{{ $name }}" @selected(old('permit_type') === $name)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('permit_type')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Section 2: Additional Information --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Additional Information</h3>
                    </div>
                    <div id="dynamic_fields" class="space-y-4"></div>
                </div>

                {{-- Section 3: Supporting Document (conditional) --}}
                <div id="document-section" class="space-y-5 hidden">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Supporting Document</h3>
                    </div>

                    <div>
                        <label for="document" id="document-label" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Upload Document <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="document" id="document"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full rounded-lg border border-gray-300 text-sm text-gray-600 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 transition @error('document') border-red-400 @enderror">
                        <p class="mt-1.5 text-xs text-gray-500">Accepted: PDF, JPG, PNG. Max size: 5MB.</p>
                        @error('document')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Resident Info (auto-filled) --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Applicant Information</h3>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                            <input type="text" readonly value="{{ trim(auth()->user()->first_name.' '.auth()->user()->middle_name.' '.auth()->user()->last_name) }}" class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-gray-900">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Purok</label>
                            <input type="text" readonly value="{{ auth()->user()->purokRelation?->name ?? 'No purok assigned' }}" class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-gray-900">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                            @php
                                $purokLabel = trim((string) (auth()->user()->purokRelation?->name ?? ''));
                                $normalizedPurok = $purokLabel !== '' ? (preg_match('/^purok\b/i', $purokLabel) ? $purokLabel : ('Purok ' . $purokLabel)) : '';
                                $residentAddress = trim(implode(', ', array_filter([trim((string) auth()->user()->house_no), $normalizedPurok])));
                            @endphp
                            <input type="text" readonly value="{{ $residentAddress !== '' ? $residentAddress : 'No address on file' }}" class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-gray-900">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">This application will be submitted under your verified account.</p>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                <a href="{{ route('resident.permits.index') }}" class="ui-focus-ring rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    Submit Application
                </button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const permitTypes = @json($permitTypes);
    const select = document.getElementById('permit_type');
    const section = document.getElementById('document-section');
    const label = document.getElementById('document-label');
    const input = document.getElementById('document');
    const dynamicFields = document.getElementById('dynamic_fields');
    const oldValues = @json(old());
    const errors = @json($errors->toArray());

    const permitFields = {
        'Business Permit': [
            { name: 'business_name', type: 'text', label: 'Business Name' },
            { name: 'business_address', type: 'text', label: 'Business Address' },
            { name: 'purpose', type: 'select-business-purpose', label: 'Purpose' }
        ],
        'Event Permit': [
            { name: 'event_name', type: 'text', label: 'Event Name' },
            { name: 'event_date', type: 'date', label: 'Event Date' },
            { name: 'purpose', type: 'text', label: 'Purpose' }
        ],
        'Building Permit': [
            { name: 'project_type', type: 'text', label: 'Project Type' },
            { name: 'project_location', type: 'text', label: 'Project Location' },
            { name: 'purpose', type: 'text', label: 'Purpose' }
        ],
        default: [
            { name: 'purpose', type: 'text', label: 'Purpose' }
        ]
    };

    const businessPurposeOptions = [
        'New Business Registration',
        'Business Permit Renewal',
        'Change of Business Address',
        'Change of Business Name',
        'Change of Ownership',
        'Additional Line of Business',
        'Closure / Cessation of Business',
        'Compliance Requirement (BIR / DTI / SEC / LGU)',
        'Loan / Financing Requirement',
        'Others'
    ];

    const businessPurposeDependentFields = {
        'Business Permit Renewal': [
            { name: 'previous_permit_number', type: 'text', label: 'Previous Permit Number', required: false },
            { name: 'last_permit_year', type: 'number', label: 'Last Permit Year', required: false, min: 1900, max: new Date().getFullYear() }
        ],
        'Change of Business Address': [
            { name: 'old_business_address', type: 'text', label: 'Old Business Address', required: true },
            { name: 'new_business_address', type: 'text', label: 'New Business Address', required: true }
        ],
        'Change of Business Name': [
            { name: 'old_business_name', type: 'text', label: 'Old Business Name', required: true },
            { name: 'new_business_name', type: 'text', label: 'New Business Name', required: true }
        ],
        'Change of Ownership': [
            { name: 'previous_owner_name', type: 'text', label: 'Previous Owner Full Name', required: true },
            { name: 'new_owner_name', type: 'text', label: 'New Owner Full Name', required: true }
        ],
        'Additional Line of Business': [
            { name: 'current_line_of_business', type: 'text', label: 'Current Line of Business', required: true },
            { name: 'additional_line_of_business', type: 'text', label: 'Additional Line of Business', required: true }
        ],
        'Closure / Cessation of Business': [
            { name: 'closure_effective_date', type: 'date', label: 'Closure Effective Date', required: true },
            { name: 'closure_reason', type: 'text', label: 'Reason for Closure', required: true }
        ],
        'Compliance Requirement (BIR / DTI / SEC / LGU)': [
            { name: 'agency_name', type: 'text', label: 'Agency / Office', required: true },
            { name: 'reference_number', type: 'text', label: 'Reference Number', required: false }
        ],
        'Loan / Financing Requirement': [
            { name: 'financing_institution', type: 'text', label: 'Institution / Bank Name', required: true },
            { name: 'financing_reference_number', type: 'text', label: 'Reference Number', required: false }
        ]
    };

    function inputClasses(hasError) {
        return 'block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 ' +
            (hasError ? 'border-red-400 ring-red-100 focus:border-red-400' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20');
    }

    function buildBusinessPurposeOptions(selectedPurpose) {
        const selected = String(selectedPurpose || '');
        let options = '<option value="">Select purpose</option>';
        businessPurposeOptions.forEach((option) => {
            const isSelected = option === selected ? 'selected' : '';
            options += `<option value="${option}" ${isSelected}>${option}</option>`;
        });
        return options;
    }

    function buildInputHtml(field, hasError, oldValue) {
        if (field.type === 'select-business-purpose') {
            return `<select name="${field.name}" id="${field.name}" class="${inputClasses(hasError)}" required>${buildBusinessPurposeOptions(oldValue)}</select>`;
        }

        const minAttr = field.min ? `min="${field.min}"` : '';
        const maxAttr = field.max ? `max="${field.max}"` : '';
        const requiredAttr = field.required === false ? '' : 'required';
        return `<input type="${field.type}" name="${field.name}" value="${String(oldValue ?? '').replace(/"/g, '&quot;')}" class="${inputClasses(hasError)}" ${requiredAttr} ${minAttr} ${maxAttr}>`;
    }

    function appendField(field) {
        const hasError = Array.isArray(errors[field.name]) && errors[field.name].length > 0;
        const oldValue = oldValues[field.name] || '';
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                ${field.label} ${field.required === false ? '' : '<span class="text-red-500">*</span>'}
            </label>
            ${buildInputHtml(field, hasError, oldValue)}
            ${hasError ? `<p class="mt-1.5 text-xs text-red-600">${errors[field.name][0]}</p>` : ''}
        `;
        dynamicFields.appendChild(wrapper);
    }

    function renderBusinessPurposeDependents() {
        const purposeSelect = document.getElementById('purpose');
        if (!purposeSelect) return;

        const renderDependents = () => {
            dynamicFields.querySelectorAll('[data-business-dependent="1"]').forEach((el) => el.remove());

            const chosenPurpose = purposeSelect.value;

            const purposeOtherWrap = document.createElement('div');
            purposeOtherWrap.setAttribute('data-business-dependent', '1');
            const hasOtherError = Array.isArray(errors.purpose_other) && errors.purpose_other.length > 0;
            purposeOtherWrap.className = chosenPurpose === 'Others' ? '' : 'hidden';
            purposeOtherWrap.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Please specify purpose <span class="text-red-500">*</span>
                </label>
                <input type="text" name="purpose_other" id="purpose_other" value="${String(oldValues.purpose_other || '').replace(/"/g, '&quot;')}" class="${inputClasses(hasOtherError)}" ${chosenPurpose === 'Others' ? 'required' : ''}>
                ${hasOtherError ? `<p class="mt-1.5 text-xs text-red-600">${errors.purpose_other[0]}</p>` : ''}
            `;
            dynamicFields.appendChild(purposeOtherWrap);

            const dependentFields = businessPurposeDependentFields[chosenPurpose] || [];
            dependentFields.forEach((field) => {
                const dependentWrap = document.createElement('div');
                dependentWrap.setAttribute('data-business-dependent', '1');
                const hasError = Array.isArray(errors[field.name]) && errors[field.name].length > 0;
                const oldValue = oldValues[field.name] || '';
                dependentWrap.innerHTML = `
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        ${field.label} ${field.required === false ? '' : '<span class="text-red-500">*</span>'}
                    </label>
                    ${buildInputHtml(field, hasError, oldValue)}
                    ${hasError ? `<p class="mt-1.5 text-xs text-red-600">${errors[field.name][0]}</p>` : ''}
                `;
                dynamicFields.appendChild(dependentWrap);
            });
        };

        purposeSelect.addEventListener('change', renderDependents);
        renderDependents();
    }

    function renderDynamicFields() {
        const selected = select.value;
        const fields = permitFields[selected] || permitFields.default;
        dynamicFields.innerHTML = '';

        fields.forEach((field) => {
            appendField(field);
        });

        if (selected === 'Business Permit') {
            renderBusinessPurposeDependents();
        }
    }

    function updateDocumentField() {
        const selected = select.value;
        const config = permitTypes[selected];

        if (config && config.requires_document) {
            section.classList.remove('hidden');
            label.textContent = config.document_label + ' (required)';
            input.setAttribute('required', 'required');
        } else {
            section.classList.add('hidden');
            input.removeAttribute('required');
            input.value = '';
        }
    }

    select.addEventListener('change', function () {
        renderDynamicFields();
        updateDocumentField();
    });

    renderDynamicFields();
    updateDocumentField();
});
</script>
@endsection
