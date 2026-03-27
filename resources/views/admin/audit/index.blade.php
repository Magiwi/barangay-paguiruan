@extends('layouts.admin')

@section('title', 'Audit Log - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Audit Log</h1>
            <p class="text-sm text-gray-600">Track all administrative actions performed in the system.</p>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.audit.index') }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="action" class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                    <select name="action" id="action" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>
                                {{ str_replace('_', ' ', ucfirst($action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user_id" class="block text-xs font-medium text-gray-600 mb-1">Performed By</label>
                    <select name="user_id" id="user_id" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Users</option>
                        @foreach ($performers as $performer)
                            <option value="{{ $performer->id }}" @selected(request('user_id') == $performer->id)>
                                {{ $performer->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-blue-700 transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.audit.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Results count --}}
        <div class="text-sm text-gray-500">
            Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Performed By</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    {{ $log->created_at->format('M d, Y') }}
                                    <span class="block text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                                    @if ($log->user)
                                        <a href="{{ route('admin.residents.show', $log->user) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                            {{ $log->user->full_name }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">System</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    @php
                                        $badgeColor = match(true) {
                                            str_contains($log->action, 'approved') || str_contains($log->action, 'restored') || str_contains($log->action, 'unsuspended') => 'bg-green-100 text-green-800',
                                            str_contains($log->action, 'rejected') || str_contains($log->action, 'archived') || str_contains($log->action, 'suspended') => 'bg-red-100 text-red-800',
                                            str_contains($log->action, 'released') => 'bg-blue-100 text-blue-800',
                                            str_contains($log->action, 'updated') => 'bg-amber-100 text-amber-800',
                                            str_contains($log->action, 'uploaded') => 'bg-purple-100 text-purple-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeColor }}">
                                        {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    @if ($log->target_type)
                                        <span class="text-gray-500">{{ $log->target_type }}</span>
                                        <span class="text-gray-400">#{{ $log->target_id }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate" title="{{ $log->description }}">
                                    {{ $log->description ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">No audit log entries found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($logs->hasPages())
            <div class="flex justify-center">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
