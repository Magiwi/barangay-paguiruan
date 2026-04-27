@extends($layout ?? 'layouts.admin')

@section('title', 'Blotter Requests - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Page header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Blotter Requests</h1>
            <p class="mt-1 text-sm text-gray-600">Manage blotter record requests from residents.</p>
        </div>

        {{-- Flash messages --}}
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

        {{-- Statistics cards --}}
        <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats->total ?? 0) }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1">Total</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-amber-700">{{ number_format($stats->pending_count ?? 0) }}</p>
                <p class="text-xs font-medium text-amber-600 mt-1">Pending</p>
            </div>
            <div class="rounded-2xl border border-green-200 bg-green-50 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-green-700">{{ number_format($stats->approved_count ?? 0) }}</p>
                <p class="text-xs font-medium text-green-600 mt-1">Approved</p>
            </div>
            <div class="rounded-2xl border border-red-200 bg-red-50 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-red-700">{{ number_format($stats->rejected_count ?? 0) }}</p>
                <p class="text-xs font-medium text-red-600 mt-1">Rejected</p>
            </div>
            <div class="rounded-2xl border border-blue-200 bg-blue-50 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-blue-700">{{ number_format($stats->released_count ?? 0) }}</p>
                <p class="text-xs font-medium text-blue-600 mt-1">Released</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route($rp . '.blotter-requests.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Resident name or blotter #"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    <option value="released" @selected(request('status') === 'released')>Released</option>
                </select>
            </div>
            <button type="submit" class="ui-btn ui-btn-primary rounded-lg">
                Filter
            </button>
            @if (request()->hasAny(['search', 'status']))
                <a href="{{ route($rp . '.blotter-requests.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Clear
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @if ($blotterRequests->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-600">No blotter requests found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blotter #</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($blotterRequests as $req)
                                <tr class="hover:bg-gray-50 transition">
                                    {{-- Resident --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if ($req->user)
                                            <p class="text-sm font-medium text-gray-900">{{ $req->user->first_name }} {{ $req->user->last_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $req->user->email }}</p>
                                        @else
                                            <span class="text-sm text-gray-400">--</span>
                                        @endif
                                    </td>

                                    {{-- Blotter number --}}
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900 font-mono">{{ $req->blotter->blotter_number ?? '—' }}</span>
                                    </td>

                                    {{-- Purpose --}}
                                    <td class="px-5 py-4">
                                        <p class="text-sm text-gray-700 max-w-xs" title="{{ $req->purpose }}">{{ Str::limit($req->purpose, 60) }}</p>
                                    </td>

                                    {{-- Status badge --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        @if ($req->status === 'pending')
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                        @elseif ($req->status === 'approved')
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                        @elseif ($req->status === 'rejected')
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                        @elseif ($req->status === 'released')
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-800">Released</span>
                                        @endif
                                    </td>

                                    {{-- Remarks --}}
                                    <td class="px-5 py-4">
                                        @if ($req->remarks)
                                            @if ($req->rejectionReasonLabel())
                                                <p class="text-xs font-semibold text-red-600 mb-1">{{ $req->rejectionReasonLabel() }}</p>
                                            @endif
                                            <p class="text-sm text-gray-600 max-w-xs" title="{{ $req->remarks }}">{{ Str::limit($req->remarks, 40) }}</p>
                                            @if ($req->processedBy)
                                                <p class="text-xs text-gray-400 mt-0.5">By {{ $req->processedBy->first_name }}</p>
                                            @endif
                                        @elseif ($req->rejectionReasonLabel())
                                            <p class="text-xs font-semibold text-red-600">{{ $req->rejectionReasonLabel() }}</p>
                                        @else
                                            <span class="text-xs text-gray-400">--</span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $req->created_at->format('M d, Y') }}
                                        <br><span class="text-xs text-gray-400">{{ $req->created_at->format('h:i A') }}</span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        @if ($req->status === 'pending')
                                            <div class="flex flex-col items-center gap-2">
                                                {{-- Approve (admin only) --}}
                                                @if (in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                                    <form method="POST" action="{{ route($rp . '.blotter-requests.update', $req) }}" class="inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition"
                                                                onclick="return confirm('Approve this blotter request?')">
                                                            Approve
                                                        </button>
                                                    </form>

                                                    {{-- Reject (admin only, with remarks) --}}
                                                    <button type="button" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700 transition"
                                                            onclick="openRejectModal({{ $req->id }})">
                                                        Reject
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400">Awaiting admin</span>
                                                @endif
                                            </div>
                                        @elseif ($req->status === 'approved')
                                            {{-- Release (staff or admin) --}}
                                            <form method="POST" action="{{ route($rp . '.blotter-requests.update', $req) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="released">
                                                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm rounded-lg"
                                                        onclick="return confirm('Mark as released?')">
                                                    Release
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">No action</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($blotterRequests->hasPages())
                    <div class="border-t border-gray-200 px-5 py-3">
                        {{ $blotterRequests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>

{{-- Reject modal --}}
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40" onclick="closeRejectModal()"></div>
        <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Blotter Request</h3>
            <form id="rejectForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="rejected">
                <div class="mb-4">
                    <label for="reject_reason_code" class="block text-sm font-medium text-gray-700 mb-1">
                        Rejection Template <span class="text-red-600">*</span>
                    </label>
                    <select name="rejection_reason_code" id="reject_reason_code" required
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                        <option value="">Select reason</option>
                        @foreach (($rejectionReasonOptions ?? []) as $code => $label)
                            <option value="{{ $code }}" @selected(old('rejection_reason_code') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('rejection_reason_code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="reject_remarks" class="block text-sm font-medium text-gray-700 mb-1">
                        Additional Details <span id="reject_details_required" class="text-red-600 hidden">*</span>
                    </label>
                    <textarea name="remarks" id="reject_remarks" rows="3"
                              placeholder="Add specific details (required only when 'Others' is selected)..."
                              class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20"></textarea>
                    @error('remarks')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRejectModal(requestId) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        form.action = '{{ url(($routePrefix ?? "admin") . "/blotter-requests") }}/' + requestId;
        document.getElementById('reject_reason_code').value = '';
        document.getElementById('reject_remarks').value = '';
        toggleRejectDetailsRequired();
        modal.classList.remove('hidden');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeRejectModal();
    });

    function toggleRejectDetailsRequired() {
        const reasonSelect = document.getElementById('reject_reason_code');
        const remarks = document.getElementById('reject_remarks');
        const requiredIndicator = document.getElementById('reject_details_required');
        const isOthers = reasonSelect.value === 'others';

        remarks.required = isOthers;
        requiredIndicator.classList.toggle('hidden', !isOthers);
    }

    document.getElementById('reject_reason_code').addEventListener('change', toggleRejectDetailsRequired);
    document.addEventListener('DOMContentLoaded', toggleRejectDetailsRequired);

    // Auto-open reject modal if rejection validation failed
    @if ($errors->has('remarks') || $errors->has('rejection_reason_code'))
        document.addEventListener('DOMContentLoaded', function() {
            // Re-open the modal so the user sees the error
            var modal = document.getElementById('rejectModal');
            modal.classList.remove('hidden');
            document.getElementById('reject_reason_code').value = @json(old('rejection_reason_code', ''));
            document.getElementById('reject_remarks').value = @json(old('remarks', ''));
            toggleRejectDetailsRequired();
        });
    @endif
</script>
@endsection
