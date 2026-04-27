@extends($layout ?? 'layouts.admin')

@section('title', 'Applicant preview - Registration')

@section('content')
@php
    $rp = $routePrefix ?? 'admin';
    $idTypeLabels = [
        'national_id' => 'National ID',
        'passport' => 'Passport',
        'drivers_license' => "Driver's License",
        'umid' => 'UMID',
        'philhealth' => 'PhilHealth ID',
        'postal_id' => 'Postal ID',
        'voters_id' => "Voter's ID",
        'other' => 'Other Government ID',
    ];
    $idLabel = $idTypeLabels[$user->government_id_type] ?? ($user->government_id_type ?: '—');
@endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-900">Applicant preview</h1>
                <p class="mt-1 text-sm text-gray-600">Read-only summary before you approve or reject this registration.</p>
            </div>
            <a href="{{ route($rp . '.pending-registrations.index', ['status' => $user->status]) }}"
               class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Back to registration list
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-800">{{ $user->last_name }}, {{ $user->first_name }} @if($user->middle_name)<span class="font-normal text-gray-500">{{ $user->middle_name }}</span>@endif</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>
            </div>
            <dl class="grid gap-4 px-6 py-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($user->status) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Registered</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Contact</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->contact_number ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Birthdate / Age</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if ($user->birthdate)
                            {{ $user->birthdate->format('M d, Y') }} ({{ $user->birthdate->age }} yrs)
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Gender</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->gender ? ucfirst($user->gender) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Civil status</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->civil_status ? ucfirst(str_replace('_', ' ', $user->civil_status)) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Purok</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->purokRelation?->name ?? $user->purok ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ trim(implode(', ', array_filter([$user->house_no, $user->street_name, $user->sitio_subdivision]))) ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Resident type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->resident_type ? ucfirst(str_replace('-', ' ', $user->resident_type)) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Head of family</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->head_of_family ? ucfirst($user->head_of_family) : '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Government ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $idLabel }}
                        @if ($user->government_id_path)
                            — <a href="{{ asset('storage/' . ltrim($user->government_id_path, '/')) }}" target="_blank" rel="noopener" class="font-medium text-blue-600 hover:text-blue-800">Open proof</a>
                        @else
                            <span class="text-red-600"> — no file uploaded</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        @if (in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN], true) && $user->status === \App\Models\User::STATUS_APPROVED)
            <p class="text-sm text-gray-600">
                For household linking, permissions, and full history, open the
                <a href="{{ route('admin.residents.show', $user) }}" class="font-medium text-blue-600 hover:text-blue-800">full resident profile</a>.
            </p>
        @endif
    </div>
</section>
@endsection
