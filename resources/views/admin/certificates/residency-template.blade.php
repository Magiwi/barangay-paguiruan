@extends('layouts.admin')

@section('title', 'Residency Template - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Certificate of Residency Template</h1>
                <p class="mt-1 text-sm text-gray-500">Edit auto-filled fields before printing (approved requests only).</p>
            </div>
            <a href="{{ route('admin.certificates.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Back to Certificates
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="error">
                <ul class="list-disc ml-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:col-span-2">
                <form method="POST" action="{{ route('admin.certificates.residency-template.update', $certificate) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="certificate_name_override" class="mb-1.5 block text-sm font-medium text-gray-700">Resident Name Override</label>
                        <input
                            type="text"
                            id="certificate_name_override"
                            name="certificate_name_override"
                            value="{{ old('certificate_name_override', $certificate->certificate_name_override) }}"
                            placeholder="{{ trim(($certificate->user?->first_name ?? '') . ' ' . ($certificate->user?->middle_name ?? '') . ' ' . ($certificate->user?->last_name ?? '')) }}"
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">Leave blank to use resident profile full name automatically.</p>
                    </div>

                    <div>
                        <label for="certificate_address_override" class="mb-1.5 block text-sm font-medium text-gray-700">Address Override</label>
                        <input
                            type="text"
                            id="certificate_address_override"
                            name="certificate_address_override"
                            value="{{ old('certificate_address_override', $certificate->certificate_address_override) }}"
                            placeholder="{{ $certificate->user?->address ?: 'Barangay Paguiruan, Floridablanca, Pampanga' }}"
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-xs text-gray-500">Leave blank to use resident profile address.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="residency_years_text" class="mb-1.5 block text-sm font-medium text-gray-700">Length of Residency <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="residency_years_text"
                                name="residency_years_text"
                                value="{{ old('residency_years_text', $certificate->residency_years_text) }}"
                                required
                                placeholder="e.g., 1 year up to present"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <div>
                            <label for="certificate_issued_on" class="mb-1.5 block text-sm font-medium text-gray-700">Issued Date <span class="text-red-500">*</span></label>
                            <input
                                type="date"
                                id="certificate_issued_on"
                                name="certificate_issued_on"
                                value="{{ old('certificate_issued_on', optional($certificate->certificate_issued_on)->format('Y-m-d') ?: now()->format('Y-m-d')) }}"
                                required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="purpose" class="mb-1.5 block text-sm font-medium text-gray-700">Purpose <span class="text-red-500">*</span></label>
                        <textarea
                            id="purpose"
                            name="purpose"
                            rows="3"
                            required
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >{{ old('purpose', $certificate->purpose) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                        <button type="submit" class="ui-btn ui-btn-primary rounded-lg">
                            Save Template Details
                        </button>
                        <a href="{{ route('admin.certificates.residency-template.print', $certificate) }}" target="_blank" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100">
                            Print Template
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Request Snapshot</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Request ID</dt>
                        <dd class="font-medium text-gray-900">#{{ $certificate->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="font-medium text-green-700">Approved</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Resident</dt>
                        <dd class="font-medium text-gray-900">{{ trim(($certificate->user?->first_name ?? '') . ' ' . ($certificate->user?->middle_name ?? '') . ' ' . ($certificate->user?->last_name ?? '')) ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Purok</dt>
                        <dd class="font-medium text-gray-900">{{ $certificate->user?->purokRelation?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Type</dt>
                        <dd class="font-medium text-gray-900">{{ $certificate->certificate_type }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>
@endsection
