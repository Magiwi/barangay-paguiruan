@extends($layout ?? 'layouts.admin')

@section('title', 'Registration Management - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Registration Management</h1>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif
        @if ($errors->has('rejection_reason_code') || $errors->has('rejection_reason_details'))
            <x-ui.alert type="error">
                {{ $errors->first('rejection_reason_code') ?: $errors->first('rejection_reason_details') }}
            </x-ui.alert>
        @endif
        @if ($errors->has('trigger_reason'))
            <x-ui.alert type="error">
                {{ $errors->first('trigger_reason') }}
            </x-ui.alert>
        @endif

        @include('admin.pending-registrations.partials.tabs')

        @if ($status === 'registered')
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="{{ route($rp . '.pending-registrations.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="status" value="registered">
                    <label for="walk-in-search" class="sr-only">Search name</label>
                    <input id="walk-in-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search name"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm sm:w-64">
                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">Search</button>
                    @if (request('q'))
                        <a href="{{ route($rp . '.pending-registrations.index', ['status' => 'registered']) }}" class="text-sm text-gray-600 hover:text-gray-900">Clear</a>
                    @endif
                </form>
                <a href="{{ route($rp . '.pending-registrations.create') }}"
                   class="inline-flex rounded-lg bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">
                    Register Resident
                </a>
            </div>
        @endif

        @if ($status === 'rejected')
            <x-ui.alert type="info" class="mb-4">
                Rejected applicants are removed from the database. Entries below come from the approval log (name, email, and reason are stored when the rejection happens).
            </x-ui.alert>
        @endif

        @if ($status === 'pending')
        @endif

        {{-- Bulk actions (pending tab only) --}}
        @if ($status === 'pending' && $users->isNotEmpty())
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <label class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 md:hidden">
                    <input type="checkbox" id="select-all-phone" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select all">
                    Select all
                </label>
                <button type="submit" form="bulk-form-inline" formaction="{{ route($rp . '.pending-registrations.bulk-approve') }}"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                    Bulk Approve
                </button>
                <button type="button"
                        onclick="openRejectModal('bulk')"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                    Bulk Reject
                </button>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                @if ($status === 'rejected' && $rejectionLogs)
                <table class="phone-stack min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rejected by</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($rejectionLogs as $log)
                            <tr>
                                <td class="phone-title whitespace-nowrap px-6 py-4 text-sm text-gray-600" data-label="Rejected">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700" data-label="Details">
                                    <div class="max-w-2xl whitespace-pre-wrap">{{ $log->remarks ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900" data-label="Rejected by">
                                    @if ($log->performer)
                                        {{ trim($log->performer->first_name . ' ' . ($log->performer->middle_name ?? '') . ' ' . $log->performer->last_name) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No rejection records yet. When you reject a pending registration, it will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @else
                @if ($status === 'pending' && $users->isNotEmpty())
                    <form method="POST" action="{{ route($rp . '.pending-registrations.bulk-approve') }}" id="bulk-form-inline">
                        @csrf
                @endif
                <table class="phone-stack min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if ($status === 'pending')
                                <th class="px-4 py-3 text-left">
                                    @if ($users->isNotEmpty())
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" aria-label="Select all">
                                            <span class="text-xs font-medium text-gray-500 uppercase">Select</span>
                                        </label>
                                    @else
                                        <span class="text-xs font-medium text-gray-500 uppercase">Select</span>
                                    @endif
                                </th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Age Check</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proof Snapshot</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Government ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($users as $user)
                            @php
                                $rowAge = $user->birthdate ? $user->birthdate->age : null;
                                $rowIsAdult = is_int($rowAge) && $rowAge >= 18;
                                $rowIdTypeLabels = [
                                    'national_id' => 'National ID',
                                    'passport' => 'Passport',
                                    'drivers_license' => "Driver's License",
                                    'umid' => 'UMID',
                                    'philhealth' => 'PhilHealth ID',
                                    'postal_id' => 'Postal ID',
                                    'voters_id' => "Voter's ID",
                                    'other' => 'Other Government ID',
                                ];
                                $rowIdTypeLabel = $rowIdTypeLabels[$user->government_id_type] ?? ($user->government_id_type ?: '—');
                                $rowPendingHours = $user->status === 'pending' ? (int) $user->created_at->diffInHours(now()) : null;
                            @endphp
                            <tr>
                                @if ($status === 'pending')
                                    <td class="px-4 py-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="bulk-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" form="bulk-form-inline">
                                            <span class="phone-only text-xs font-medium text-gray-500">Select</span>
                                        </label>
                                    </td>
                                @endif
                                <td class="phone-title px-6 py-4 text-sm text-gray-900">
                                    <p>
                                        {{ $user->last_name }}, {{ $user->first_name }}
                                        @if ($user->middle_name)
                                            <span class="font-normal text-gray-500">{{ $user->middle_name }}</span>
                                        @endif
                                    </p>
                                    <details class="phone-fold mt-2">
                                        <summary class="cursor-pointer text-xs font-medium text-green-800">Age, proof, and ID</summary>
                                        <div class="mt-2 space-y-1.5 rounded-lg bg-gray-50 p-3 text-xs text-gray-700">
                                            <p><span class="font-medium text-gray-900">Email:</span> {{ $user->email ?: 'Not activated yet' }}</p>
                                            <p>
                                                <span class="font-medium text-gray-900">Age:</span>
                                                @if ($rowAge === null)
                                                    No birthdate
                                                @elseif ($rowIsAdult)
                                                    18+ verified ({{ $rowAge }})
                                                @else
                                                    Underage ({{ $rowAge }})
                                                @endif
                                            </p>
                                            <p>
                                                <span class="font-medium text-gray-900">PWD:</span>
                                                @if ($user->is_pwd)
                                                    {{ $user->pwd_proof_path ? 'proof uploaded' : 'claimed, no proof' }}
                                                @else
                                                    not claimed
                                                @endif
                                            </p>
                                            <p>
                                                <span class="font-medium text-gray-900">Senior:</span>
                                                @if ($user->is_senior)
                                                    {{ $user->senior_proof_path ? 'proof uploaded' : 'claimed, no proof' }}
                                                @else
                                                    not claimed
                                                @endif
                                            </p>
                                            <p>
                                                <span class="font-medium text-gray-900">Government ID:</span>
                                                {{ $rowIdTypeLabel }} —
                                                @if ($user->government_id_path)
                                                    uploaded
                                                    @php $rowIdStoragePath = ltrim((string) $user->government_id_path, '/'); @endphp
                                                    <button type="button"
                                                            class="ml-1 rounded-md border border-blue-200 bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 hover:bg-blue-100"
                                                            onclick="openIdPreview('{{ e($rowIdStoragePath) }}', '{{ e($user->full_name) }}')">
                                                        View ID
                                                    </button>
                                                @else
                                                    missing
                                                @endif
                                            </p>
                                            <p>
                                                <span class="font-medium text-gray-900">Registered:</span>
                                                {{ $user->created_at->format('M d, Y H:i') }}
                                                @if ($rowPendingHours !== null && $rowPendingHours >= 48)
                                                    <span class="ml-1 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">Overdue {{ $rowPendingHours }}h</span>
                                                @elseif ($rowPendingHours !== null && $rowPendingHours >= 24)
                                                    <span class="ml-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Due soon {{ $rowPendingHours }}h</span>
                                                @endif
                                            </p>
                                        </div>
                                    </details>
                                </td>
                                <td class="phone-desktop-only px-6 py-4 text-sm text-gray-900">
                                    {{ $user->email ?: 'Not activated yet' }}
                                </td>
                                <td class="phone-desktop-only px-6 py-4 text-sm">
                                    @php
                                        $age = $user->birthdate ? $user->birthdate->age : null;
                                        $isAdult = is_int($age) && $age >= 18;
                                    @endphp
                                    @if ($age === null)
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">No birthdate</span>
                                    @elseif ($isAdult)
                                        <div class="space-y-1">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">18+ verified</span>
                                            <p class="text-xs text-gray-500">Age: {{ $age }}</p>
                                        </div>
                                    @else
                                        <div class="space-y-1">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Underage</span>
                                            <p class="text-xs text-gray-500">Age: {{ $age }}</p>
                                        </div>
                                    @endif
                                </td>
                                <td class="phone-desktop-only px-6 py-4 text-sm">
                                    <div class="space-y-1 text-xs">
                                        <div>
                                            <span class="font-medium text-gray-700">PWD:</span>
                                            @if ($user->is_pwd)
                                                @if ($user->pwd_proof_path)
                                                    <span class="text-green-700">proof uploaded</span>
                                                @else
                                                    <span class="text-amber-700">claimed, no proof</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">not claimed</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Senior:</span>
                                            @if ($user->is_senior)
                                                @if ($user->senior_proof_path)
                                                    <span class="text-green-700">proof uploaded</span>
                                                @else
                                                    <span class="text-amber-700">claimed, no proof</span>
                                                @endif
                                            @else
                                                <span class="text-gray-500">not claimed</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="phone-desktop-only px-6 py-4 text-sm">
                                    @php
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
                                        $idTypeLabel = $idTypeLabels[$user->government_id_type] ?? ($user->government_id_type ?: '—');
                                    @endphp
                                    <div class="space-y-1 text-xs">
                                        <div>
                                            <span class="font-medium text-gray-700">Type:</span>
                                            <span class="text-gray-700">{{ $idTypeLabel }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Proof:</span>
                                            @if ($user->government_id_path)
                                                <span class="text-green-700">uploaded</span>
                                                @php
                                                    $idStoragePath = ltrim((string) $user->government_id_path, '/');
                                                @endphp
                                                <button type="button"
                                                        class="ml-2 rounded-md border border-blue-200 bg-blue-50 px-2 py-1 text-[11px] font-medium text-blue-700 hover:bg-blue-100"
                                                        onclick="openIdPreview('{{ e($idStoragePath) }}', '{{ e($user->full_name) }}')">
                                                    View ID
                                                </button>
                                            @else
                                                <span class="text-red-700">missing</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="phone-desktop-only px-6 py-4 text-sm text-gray-600">
                                    <div class="space-y-1">
                                        <p>{{ $user->created_at->format('M d, Y H:i') }}</p>
                                        @if ($user->status === 'pending')
                                            @php
                                                $pendingHours = (int) $user->created_at->diffInHours(now());
                                            @endphp
                                            @if ($pendingHours >= 48)
                                                <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-semibold text-red-700">
                                                    Overdue {{ $pendingHours }}h
                                                </span>
                                            @elseif ($pendingHours >= 24)
                                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700">
                                                    Due soon {{ $pendingHours }}h
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($user->status === 'pending')
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                    @elseif ($user->status === 'approved')
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                    @elseif ($user->status === 'suspended')
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-gray-200 text-gray-800">Suspended</span>
                                    @else
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route($rp . '.pending-registrations.preview', $user) }}"
                                           class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-800 hover:bg-gray-50">
                                            View profile
                                        </a>
                                        @if ($user->hasUnclaimedAccount())
                                            <form method="POST" action="{{ route($rp . '.pending-registrations.claim-code', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-900 hover:bg-amber-100">
                                                    New code
                                                </button>
                                            </form>
                                        @endif
                                        {{-- PENDING: Approve + Reject --}}
                                        @if ($user->status === 'pending')
                                            <form method="POST" action="{{ route($rp . '.pending-registrations.approve', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        @disabled(isset($age) && $age < 18)
                                                        class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600"
                                                        title="{{ isset($age) && $age < 18 ? 'Cannot approve under-18 registration.' : '' }}">
                                                    Approve
                                                </button>
                                            </form>
                                            <button type="button"
                                                    class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition"
                                                    onclick="openRejectModal('single', '{{ route($rp . '.pending-registrations.reject', $user) }}')">
                                                Reject
                                            </button>

                                        {{-- APPROVED: Suspend --}}
                                        @elseif ($user->status === 'approved')
                                            <form method="POST" action="{{ route($rp . '.pending-registrations.suspend', $user) }}" class="inline"
                                                  onsubmit="return confirm('Are you sure you want to suspend this user? They will no longer be able to log in.')">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-gray-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-800 transition">
                                                    Suspend
                                                </button>
                                            </form>

                                        {{-- SUSPENDED: Unsuspend --}}
                                        @elseif ($user->status === 'suspended')
                                            <form method="POST" action="{{ route($rp . '.pending-registrations.unsuspend', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm rounded-lg">
                                                    Unsuspend
                                                </button>
                                            </form>

                                        {{-- REJECTED: Badge only --}}
                                        @elseif ($user->status === 'rejected')
                                            <div class="space-y-1">
                                                <span class="text-xs text-gray-400 italic">No actions available</span>
                                                @if ($user->registrationRejectionReasonLabel())
                                                    <p class="text-xs font-medium text-red-700">{{ $user->registrationRejectionReasonLabel() }}</p>
                                                @endif
                                                @if ($user->rejection_reason_details)
                                                    <p class="text-xs text-gray-600">{{ $user->rejection_reason_details }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $status === 'pending' ? 9 : 8 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No registrations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($status === 'pending' && $users->isNotEmpty())
                    </form>
                @endif
                @endif
            </div>
            @if ($status === 'rejected' && $rejectionLogs && $rejectionLogs->hasPages())
                <div class="border-t border-gray-200 px-6 py-3">
                    {{ $rejectionLogs->links() }}
                </div>
            @elseif ($users->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</section>

@if ($status === 'pending' && $users->isNotEmpty())
<script>
(function () {
    var selectAll = document.getElementById('select-all');
    var selectAllPhone = document.getElementById('select-all-phone');
    var checkboxes = document.querySelectorAll('.bulk-checkbox');
    function syncSelectAll(checked, indeterminate) {
        [selectAll, selectAllPhone].forEach(function (box) {
            if (!box) return;
            box.checked = checked;
            box.indeterminate = indeterminate;
        });
    }
    function bindSelectAll(box) {
        if (!box) return;
        box.addEventListener('change', function () {
            checkboxes.forEach(function (cb) { cb.checked = box.checked; });
            syncSelectAll(box.checked, false);
        });
    }
    if (checkboxes.length) {
        bindSelectAll(selectAll);
        bindSelectAll(selectAllPhone);
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                var all = Array.prototype.every.call(checkboxes, function (c) { return c.checked; });
                var none = Array.prototype.every.call(checkboxes, function (c) { return !c.checked; });
                syncSelectAll(all, !all && !none);
            });
        });
    }
})();
</script>
@endif

{{-- Government ID Preview Modal --}}
<div id="idPreviewModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeIdPreview()"></div>
    <div class="relative mx-auto mt-8 w-[94%] max-w-4xl rounded-2xl bg-white shadow-2xl ring-1 ring-gray-200 sm:mt-12">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h3 id="idPreviewTitle" class="text-sm font-semibold text-gray-900">Government ID Preview</h3>
            <button type="button" onclick="closeIdPreview()" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="max-h-[72vh] overflow-auto px-5 py-4">
            <img id="idPreviewImage" src="" alt="Government ID preview" class="mx-auto hidden max-h-[66vh] w-auto rounded-lg border border-gray-200 object-contain">
            <iframe id="idPreviewPdf" src="" class="hidden h-[66vh] w-full rounded-lg border border-gray-200" title="Government ID PDF preview"></iframe>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button type="button" onclick="closeIdPreview()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closeRejectModal()"></div>
    <div class="relative mx-auto mt-20 w-[94%] max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-gray-200">
        <form id="rejectForm" method="POST" action="">
            @csrf
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <h3 class="text-sm font-semibold text-gray-900">Reject Registration</h3>
                <button type="button" onclick="closeRejectModal()" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-4 px-5 py-4">
                <p id="rejectModalHint" class="text-xs text-gray-500"></p>
                <div>
                    <label for="rejection_reason_code" class="mb-1 block text-sm font-medium text-gray-700">Reason <span class="text-red-600">*</span></label>
                    <select id="rejection_reason_code" name="rejection_reason_code" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select reason</option>
                        @foreach (($rejectionReasonOptions ?? []) as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="rejection_reason_details" class="mb-1 block text-sm font-medium text-gray-700">
                        Details <span id="rejectDetailsRequired" class="hidden text-red-600">*</span>
                    </label>
                    <textarea id="rejection_reason_details" name="rejection_reason_details" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Add details (required when reason is Other)."></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-5 py-3">
                <button type="button" onclick="closeRejectModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openIdPreview(governmentIdPath, fullName) {
    var modal = document.getElementById('idPreviewModal');
    var title = document.getElementById('idPreviewTitle');
    var img = document.getElementById('idPreviewImage');
    var pdf = document.getElementById('idPreviewPdf');
    if (!modal || !img || !pdf || !title) return;

    var normalizedPath = String(governmentIdPath || '').replace(/^\/+/, '');
    if (!normalizedPath) return;
    var fileUrl = '/storage/' + normalizedPath;
    var lowerPath = normalizedPath.toLowerCase();

    if (lowerPath.endsWith('.pdf')) {
        window.open(fileUrl, '_blank');
        return;
    }

    title.textContent = 'Government ID Preview - ' + fullName;
    img.src = fileUrl;
    img.classList.remove('hidden');
    pdf.src = '';
    pdf.classList.add('hidden');

    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
}

function closeIdPreview() {
    var modal = document.getElementById('idPreviewModal');
    var img = document.getElementById('idPreviewImage');
    var pdf = document.getElementById('idPreviewPdf');
    if (!modal || !img || !pdf) return;

    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    img.src = '';
    pdf.src = '';
    document.body.classList.remove('overflow-hidden');
}
</script>

@if ($status === 'pending')
<script>
(function () {
    var modal = document.getElementById('rejectModal');
    var form = document.getElementById('rejectForm');
    var hint = document.getElementById('rejectModalHint');
    var reason = document.getElementById('rejection_reason_code');
    var details = document.getElementById('rejection_reason_details');
    var detailsRequired = document.getElementById('rejectDetailsRequired');
    var bulkActionUrl = @json(route($rp . '.pending-registrations.bulk-reject'));
    var mode = null;

    function toggleDetailsRequired() {
        if (!reason || !details || !detailsRequired) return;
        var isOther = reason.value === 'other';
        details.required = isOther;
        detailsRequired.classList.toggle('hidden', !isOther);
    }

    function clearBulkIds() {
        if (!form) return;
        form.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
            input.remove();
        });
    }

    window.openRejectModal = function (nextMode, actionUrl) {
        if (!modal || !form || !reason || !details || !hint) return;
        mode = nextMode;
        clearBulkIds();
        reason.value = '';
        details.value = '';
        toggleDetailsRequired();

        if (mode === 'single') {
            form.action = actionUrl || '';
            hint.textContent = 'Choose a reason before rejecting this registration.';
        } else {
            form.action = bulkActionUrl;
            hint.textContent = 'The selected reason will be applied to all checked registrations.';
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    window.closeRejectModal = function () {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    reason.addEventListener('change', toggleDetailsRequired);

    form.addEventListener('submit', function (event) {
        if (mode !== 'bulk') return;
        var checked = document.querySelectorAll('.bulk-checkbox:checked');
        if (!checked.length) {
            event.preventDefault();
            alert('Select at least one pending registration for bulk reject.');
            return;
        }

        clearBulkIds();
        checked.forEach(function (cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            form.appendChild(input);
        });
    });

})();
</script>
@endif
@endsection
