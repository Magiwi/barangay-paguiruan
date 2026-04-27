@extends('layouts.resident')

@section('title', 'Request Document - e-Governance System')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">

        {{-- Back link --}}
        <a href="{{ route('resident.certificates.index') }}" class="ui-focus-ring mb-6 inline-flex items-center gap-1.5 rounded-md text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back to My Requests
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
        <form method="POST" action="{{ route('resident.certificates.store') }}" enctype="multipart/form-data" class="ui-surface-card overflow-hidden">
            @csrf

            {{-- Card Header --}}
            <div class="border-b border-gray-200 bg-gray-50/60 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="ui-icon-chip bg-blue-100 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Request Document</h1>
                        <p class="mt-0.5 text-sm text-gray-500">Submit a request for a barangay certificate or official document.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">

                {{-- Section 1: Request Information --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Request Information</h3>
                    </div>

                    <div>
                        <label for="certificate_type" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Certificate Type <span class="text-red-500">*</span>
                        </label>
                        <select name="certificate_type" id="certificate_type" required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('certificate_type') border-red-400 ring-red-100 @enderror">
                            <option value="">Select certificate type</option>
                            <option value="Barangay Clearance" @selected(old('certificate_type') === 'Barangay Clearance')>Barangay Clearance</option>
                            <option value="Certificate of Indigency" @selected(old('certificate_type') === 'Certificate of Indigency')>Certificate of Indigency</option>
                            <option value="Residency Certificate" @selected(old('certificate_type') === 'Residency Certificate')>Residency Certificate</option>
                            <option value="Barangay Certificate" @selected(old('certificate_type') === 'Barangay Certificate')>Barangay Certificate</option>
                        </select>
                        @error('certificate_type')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if(($isHead ?? false) && ($minorOptions ?? collect())->isNotEmpty())
                        <div id="request_for_minor_wrap" class="hidden">
                            <label for="request_for_member_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Request For (Minor)
                            </label>
                            <select
                                name="request_for_member_id"
                                id="request_for_member_id"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('request_for_member_id') border-red-400 ring-red-100 @enderror"
                            >
                                <option value="">Self (default)</option>
                                @foreach(($minorOptions ?? collect()) as $minor)
                                    <option value="{{ $minor['id'] }}" @selected((string) old('request_for_member_id') === (string) $minor['id'])>
                                        {{ $minor['name'] }} ({{ $minor['age'] }}) - Minor
                                    </option>
                                @endforeach
                            </select>
                            @error('request_for_member_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                </div>

                {{-- Section 2: Additional Information --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Additional Information</h3>
                    </div>
                    <div id="dynamic_fields" class="space-y-4"></div>
                </div>

                {{-- Section 3: Resident Information (auto-filled) --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Resident Information</h3>
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
                    <p class="text-xs text-gray-400">This request will be submitted under your verified account.</p>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                <a href="{{ route('resident.certificates.index') }}" class="ui-focus-ring rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const certificateType = document.getElementById('certificate_type');
    const dynamicFields = document.getElementById('dynamic_fields');
    const requestForMinorWrap = document.getElementById('request_for_minor_wrap');
    const requestForMinorSelect = document.getElementById('request_for_member_id');
    const minorEligibleCertificateTypes = @json($minorEligibleCertificateTypes ?? []);
    const oldValues = @json(old());
    const errors = @json($errors->toArray());

    const certificateFields = {
        residency: [
            { name: 'purpose', type: 'select-purpose', label: 'Purpose' },
            { name: 'residency_start_year', type: 'select-year', label: 'Start Year of Residency' },
            { name: 'valid_id', type: 'file', label: 'Upload valid government ID (PDF, JPG, or PNG)' }
        ],
        indigency: [
            { name: 'purpose', type: 'select-indigency-purpose', label: 'Purpose' },
            { name: 'monthly_income', type: 'select-income', label: 'Monthly Income' },
            { name: 'valid_id', type: 'file', label: 'Upload valid government ID (PDF, JPG, or PNG)' }
        ],
        clearance: [
            { name: 'purpose', type: 'select-purpose', label: 'Purpose' },
            { name: 'valid_id', type: 'file', label: 'Upload valid government ID (PDF, JPG, or PNG)' }
        ],
        certificate: [
            { name: 'purpose', type: 'select-purpose', label: 'Purpose' },
            { name: 'valid_id', type: 'file', label: 'Upload valid government ID (PDF, JPG, or PNG)' }
        ]
    };

    const purposeOptions = [
        'School requirements (enrollment, scholarship)',
        'Job application',
        'Pagkuha ng government ID',
        'Legal documents',
        'Others'
    ];

    const indigencyPurposeOptions = [
        'Medical Assistance',
        'Financial Assistance',
        'Scholarship',
        'Burial Assistance',
        'Others'
    ];

    const incomeOptions = [
        'Below ₱5,000',
        '₱5,000 – ₱9,999',
        '₱10,000 – ₱14,999',
        '₱15,000 – ₱19,999',
        '₱20,000 and above',
        'No Income'
    ];

    function mapTypeKey(value) {
        const normalized = (value || '').toLowerCase();
        if (normalized.includes('residency')) return 'residency';
        if (normalized.includes('indigency')) return 'indigency';
        if (normalized.includes('clearance')) return 'clearance';
        return 'certificate';
    }

    function inputClasses(hasError) {
        return 'block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 ' +
            (hasError ? 'border-red-400 ring-red-100 focus:border-red-400' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500/20');
    }

    function computeResidencyText(startYear) {
        const year = Number(startYear || 0);
        if (!year) return '';
        const currentYear = new Date().getFullYear();
        const years = Math.max(1, currentYear - year + 1);
        return years === 1 ? '1 year up to present' : `${years} years up to present`;
    }

    function buildYearOptions(selectedYear) {
        const currentYear = new Date().getFullYear();
        const selected = Number(selectedYear || 0);
        let options = '<option value="">Select start year</option>';
        for (let year = currentYear; year >= 1900; year--) {
            const isSelected = year === selected ? 'selected' : '';
            options += `<option value="${year}" ${isSelected}>${year}</option>`;
        }
        return options;
    }

    function buildPurposeOptions(selectedPurpose) {
        const selected = String(selectedPurpose || '');
        let options = '<option value="">Select purpose</option>';
        purposeOptions.forEach((option) => {
            const isSelected = option === selected ? 'selected' : '';
            options += `<option value="${option}" ${isSelected}>${option}</option>`;
        });
        return options;
    }

    function buildIndigencyPurposeOptions(selectedPurpose) {
        const selected = String(selectedPurpose || '');
        let options = '<option value="">Select purpose</option>';
        indigencyPurposeOptions.forEach((option) => {
            const isSelected = option === selected ? 'selected' : '';
            options += `<option value="${option}" ${isSelected}>${option}</option>`;
        });
        return options;
    }

    function buildIncomeOptions(selectedIncome) {
        const selected = String(selectedIncome || '');
        let options = '<option value="">Select monthly income</option>';
        incomeOptions.forEach((option) => {
            const isSelected = option === selected ? 'selected' : '';
            options += `<option value="${option}" ${isSelected}>${option}</option>`;
        });
        return options;
    }

    function renderDynamicFields() {
        const key = mapTypeKey(certificateType.value);
        const fields = certificateFields[key] || [];
        dynamicFields.innerHTML = '';

        fields.forEach((field) => {
            const wrapper = document.createElement('div');
            const hasError = Array.isArray(errors[field.name]) && errors[field.name].length > 0;
            const isFile = field.type === 'file';
            const isSelectYear = field.type === 'select-year';
            const isSelectPurpose = field.type === 'select-purpose';
            const isSelectIndigencyPurpose = field.type === 'select-indigency-purpose';
            const isSelectIncome = field.type === 'select-income';
            const oldValue = oldValues[field.name] || '';

            let inputHtml = '';
            if (isFile) {
                inputHtml = `<input type="file" name="${field.name}" class="${inputClasses(hasError)}" accept=".pdf,.jpg,.jpeg,.png" required>`;
            } else if (isSelectYear) {
                inputHtml = `<select name="${field.name}" id="${field.name}" class="${inputClasses(hasError)}" required>${buildYearOptions(oldValue)}</select>`;
            } else if (isSelectPurpose) {
                inputHtml = `<select name="${field.name}" id="${field.name}" class="${inputClasses(hasError)}" required>${buildPurposeOptions(oldValue)}</select>`;
            } else if (isSelectIndigencyPurpose) {
                inputHtml = `<select name="${field.name}" id="${field.name}" class="${inputClasses(hasError)}" required>${buildIndigencyPurposeOptions(oldValue)}</select>`;
            } else if (isSelectIncome) {
                inputHtml = `<select name="${field.name}" id="${field.name}" class="${inputClasses(hasError)}" required>${buildIncomeOptions(oldValue)}</select>`;
            } else {
                inputHtml = `<input type="${field.type}" name="${field.name}" value="${String(oldValue).replace(/"/g, '&quot;')}" class="${inputClasses(hasError)}" required>`;
            }

            wrapper.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    ${field.label} <span class="text-red-500">*</span>
                </label>
                ${inputHtml}
                ${hasError ? `<p class="mt-1.5 text-xs text-red-600">${errors[field.name][0]}</p>` : ''}
            `;

            dynamicFields.appendChild(wrapper);
        });

        const purposeSelect = document.getElementById('purpose');
        if (purposeSelect) {
            const hasOtherError = Array.isArray(errors.purpose_other) && errors.purpose_other.length > 0;
            const purposeOtherWrap = document.createElement('div');
            purposeOtherWrap.id = 'purpose_other_wrap';
            purposeOtherWrap.className = 'space-y-1.5';
            purposeOtherWrap.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Please specify purpose <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="purpose_other"
                    id="purpose_other"
                    value="${String(oldValues.purpose_other || '').replace(/"/g, '&quot;')}"
                    class="${inputClasses(hasOtherError)}"
                >
                ${hasOtherError ? `<p class="mt-1.5 text-xs text-red-600">${errors.purpose_other[0]}</p>` : ''}
            `;
            dynamicFields.appendChild(purposeOtherWrap);

            const purposeOtherInput = purposeOtherWrap.querySelector('#purpose_other');
            const togglePurposeOther = () => {
                const isOthers = purposeSelect.value === 'Others';
                purposeOtherWrap.classList.toggle('hidden', !isOthers);
                if (purposeOtherInput) {
                    purposeOtherInput.required = isOthers;
                }
            };

            purposeSelect.addEventListener('change', togglePurposeOther);
            togglePurposeOther();
        }

        if (key === 'residency') {
            const previewWrap = document.createElement('div');
            const previewText = computeResidencyText(oldValues.residency_start_year || '');
            previewWrap.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Computed Length of Residency</label>
                <input type="text" id="residency_years_preview" readonly value="${previewText}" class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-gray-900">
            `;
            dynamicFields.appendChild(previewWrap);

            const yearSelect = document.getElementById('residency_start_year');
            const previewInput = document.getElementById('residency_years_preview');
            if (yearSelect && previewInput) {
                yearSelect.addEventListener('change', function () {
                    previewInput.value = computeResidencyText(this.value);
                });
            }
        }
    }

    function toggleRequestForMinor() {
        if (!requestForMinorWrap || !requestForMinorSelect) {
            return;
        }

        const selectedType = String(certificateType.value || '').trim();
        const shouldShow = minorEligibleCertificateTypes.includes(selectedType);
        requestForMinorWrap.classList.toggle('hidden', !shouldShow);
        requestForMinorSelect.required = false;

        if (!shouldShow) {
            requestForMinorSelect.value = '';
        }
    }

    certificateType.addEventListener('change', function () {
        renderDynamicFields();
        toggleRequestForMinor();
    });
    renderDynamicFields();
    toggleRequestForMinor();
});
</script>
@endsection
