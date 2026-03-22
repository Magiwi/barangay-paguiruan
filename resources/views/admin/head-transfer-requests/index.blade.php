@extends('layouts.admin')

@section('title', 'Head Transfer Requests - e-Governance')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        @php
            $activeStatus = $status ?? 'pending';
            $baseQuery = array_filter([
                'q' => $search ?? '',
                'from' => $from ?? '',
                'to' => $to ?? '',
            ], fn ($value) => $value !== '');
        @endphp
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h1 class="text-xl font-semibold tracking-tight text-gray-900">Head Transfer Requests</h1>
            <p class="mt-1 text-sm text-gray-500">Review resident-submitted head of family reassignment requests before applying household changes.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pending Queue</p>
                <p class="mt-2 text-2xl font-bold text-amber-900">{{ $metrics['pending_total'] ?? 0 }}</p>
            </article>
            <article class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Approved Today</p>
                <p class="mt-2 text-2xl font-bold text-emerald-900">{{ $metrics['approved_today'] ?? 0 }}</p>
            </article>
            <article class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Rejected Today</p>
                <p class="mt-2 text-2xl font-bold text-rose-900">{{ $metrics['rejected_today'] ?? 0 }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Overdue Pending (3+ Days)</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $metrics['overdue_pending'] ?? 0 }}</p>
            </article>
        </div>

        <form method="GET" action="{{ route('admin.head-transfer-requests.index') }}" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <input type="hidden" name="status" value="{{ $activeStatus }}">
            <div class="grid gap-3 md:grid-cols-4">
                <label class="block text-xs font-medium uppercase tracking-wide text-gray-600 md:col-span-2">
                    Search
                    <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Resident, requested head, email, notes" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-medium uppercase tracking-wide text-gray-600">
                    From Date
                    <input type="date" name="from" value="{{ $from ?? '' }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </label>
                <label class="block text-xs font-medium uppercase tracking-wide text-gray-600">
                    To Date
                    <input type="date" name="to" value="{{ $to ?? '' }}" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                </label>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="submit" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">Apply Filters</button>
                <a href="{{ route('admin.head-transfer-requests.index', ['status' => $activeStatus]) }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.head-transfer-requests.index', array_merge($baseQuery, ['status' => 'pending'])) }}"
               class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $activeStatus === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Pending ({{ $counts['pending'] ?? 0 }})
            </a>
            <a href="{{ route('admin.head-transfer-requests.index', array_merge($baseQuery, ['status' => 'approved'])) }}"
               class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $activeStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Approved ({{ $counts['approved'] ?? 0 }})
            </a>
            <a href="{{ route('admin.head-transfer-requests.index', array_merge($baseQuery, ['status' => 'rejected'])) }}"
               class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $activeStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Rejected ({{ $counts['rejected'] ?? 0 }})
            </a>
            <a href="{{ route('admin.head-transfer-requests.index', array_merge($baseQuery, ['status' => 'all'])) }}"
               class="rounded-lg px-3 py-2 text-sm font-medium transition {{ $activeStatus === 'all' ? 'bg-slate-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                All
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">Resident</th>
                            <th class="px-4 py-3">Current Head</th>
                            <th class="px-4 py-3">Requested Head</th>
                            <th class="px-4 py-3">Reason</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($requests as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900">{{ $row->requester?->full_name ?? 'Unknown resident' }}</p>
                                    <p class="text-xs text-gray-500">{{ $row->requester?->email ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $row->currentHead?->full_name ?? 'None' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $row->newHead?->full_name ?? 'Unknown' }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-800">{{ $row->reasonLabel() }}</p>
                                    @if ($row->details)
                                        <p class="text-xs text-gray-500">{{ $row->details }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row->status === 'pending')
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Pending</span>
                                    @elseif ($row->status === 'approved')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">Approved</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-800">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($row->created_at)->format('M d, Y h:i A') }}</td>
                                <td class="px-4 py-3">
                                    @if ($row->status === 'pending')
                                        <div class="flex flex-wrap items-center gap-2">
                                            <form method="POST" action="{{ route('admin.head-transfer-requests.approve', $row) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                <input type="text" name="review_note" maxlength="255" placeholder="Optional note" class="w-36 rounded-md border border-gray-300 px-2 py-1 text-xs">
                                                <button type="submit" class="rounded-md bg-emerald-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-emerald-700">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.head-transfer-requests.reject', $row) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                <input type="text" name="review_note" maxlength="255" required placeholder="Rejection reason (required)" class="w-44 rounded-md border border-rose-300 px-2 py-1 text-xs">
                                                <button type="submit" class="rounded-md bg-rose-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-rose-700">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">
                                            <p>Reviewed by: {{ $row->processedBy?->full_name ?? '—' }}</p>
                                            @if ($row->review_note)
                                                <p class="mt-1">{{ $row->review_note }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">No head transfer requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-4 py-3">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</section>
@endsection
