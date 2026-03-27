@extends('layouts.admin')

@section('title', 'Login Activity - e-Governance Admin Panel')
@section('page_title', 'Login Activity')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">

        <div>
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Login Activity</h1>
            <p class="text-sm text-gray-500">Monitor all authentication attempts across the system.</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Total Attempts</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-green-600">Successful</p>
                <p class="mt-1 text-2xl font-bold text-green-700">{{ number_format($stats['success']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-red-600">Failed</p>
                <p class="mt-1 text-2xl font-bold text-red-700">{{ number_format($stats['failed']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium text-amber-600">Blocked</p>
                <p class="mt-1 text-2xl font-bold text-amber-700">{{ number_format($stats['blocked']) }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.login-activities.index') }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="user_id" class="block text-xs font-medium text-gray-600 mb-1">User</label>
                    <select name="user_id" id="user_id" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Users</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>
                                {{ $u->full_name }} ({{ ucfirst($u->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="success" @selected(request('status') === 'success')>Success</option>
                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                        <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
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
                    <a href="{{ route('admin.login-activities.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Results count --}}
        <div class="text-sm text-gray-500">
            Showing {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }} entries
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Browser</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($activities as $activity)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                    {{ $activity->created_at->format('M d, Y') }}
                                    <span class="block text-xs text-gray-400">{{ $activity->created_at->format('h:i:s A') }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    @if ($activity->user)
                                        <div>
                                            <a href="{{ route('admin.residents.show', $activity->user) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                                {{ $activity->user->full_name }}
                                            </a>
                                            <span class="block text-xs text-gray-400">{{ $activity->email_attempted }}</span>
                                        </div>
                                    @else
                                        <div>
                                            <span class="text-gray-500">Unknown</span>
                                            <span class="block text-xs text-gray-400">{{ $activity->email_attempted }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">
                                    @php
                                        $statusClasses = match($activity->status) {
                                            'success' => 'bg-green-50 text-green-700 ring-green-600/20',
                                            'failed'  => 'bg-red-50 text-red-700 ring-red-600/20',
                                            'blocked' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                            default   => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                        {{ ucfirst($activity->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate" title="{{ $activity->user_agent }}">
                                    @php
                                        $ua = $activity->user_agent ?? '';
                                        $browser = 'Unknown';
                                        if (str_contains($ua, 'Edg/')) $browser = 'Edge';
                                        elseif (str_contains($ua, 'Chrome/')) $browser = 'Chrome';
                                        elseif (str_contains($ua, 'Firefox/')) $browser = 'Firefox';
                                        elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome')) $browser = 'Safari';
                                        elseif (str_contains($ua, 'Opera') || str_contains($ua, 'OPR/')) $browser = 'Opera';

                                        $os = 'Unknown';
                                        if (str_contains($ua, 'Windows')) $os = 'Windows';
                                        elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) $os = 'macOS';
                                        elseif (str_contains($ua, 'Linux')) $os = 'Linux';
                                        elseif (str_contains($ua, 'Android')) $os = 'Android';
                                        elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) $os = 'iOS';
                                    @endphp
                                    <span class="text-gray-700 font-medium">{{ $browser }}</span>
                                    <span class="text-gray-400">/ {{ $os }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">No login activity found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($activities->hasPages())
            <div class="flex justify-center">
                {{ $activities->links() }}
            </div>
        @endif

    </div>
</section>
@endsection
