@extends('layouts.staff')

@section('title', 'Staff Dashboard - e-Governance System')
@section('page_title', 'Staff Dashboard')

@section('content')
{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Welcome back, {{ $user->first_name }}
                </h1>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    @if ($user->position)
                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-0.5 text-sm font-medium text-white">
                            {{ $user->position->name }}
                        </span>
                    @endif
                    <span class="text-sm text-white/70">{{ ucfirst($user->role) }} Panel</span>
                </div>
                @if ($user->activeOfficial && $user->activeOfficial->term_end)
                    <p class="mt-2 text-xs text-white/50">
                        Term: {{ $user->activeOfficial->term_start->format('M d, Y') }} &mdash; {{ $user->activeOfficial->term_end->format('M d, Y') }}
                        @if ($user->activeOfficial->isExpired())
                            <span class="ml-1 inline-flex rounded-full bg-red-500 px-2 py-0.5 text-xs font-medium text-white">Expired</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-4">Overview</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {{-- Total Residents (always visible) --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Total Residents</p>
                        <p class="text-xl font-bold text-gray-900">{{ number_format($stats['total_residents'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            @if ($user->canAccess('registrations') && isset($stats['pending_registrations']))
                <a href="{{ route('staff.pending-registrations.index') }}" class="group rounded-2xl border border-gray-200 bg-white shadow-sm p-5 hover:ring-amber-300 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-amber-600">Pending Registrations</p>
                            <p class="text-xl font-bold text-amber-700">{{ number_format($stats['pending_registrations']) }}</p>
                        </div>
                    </div>
                </a>
            @endif

            @if ($user->canAccess('blotter') && isset($stats['pending_blotter_requests']))
                <a href="{{ route('staff.blotter-requests.index') }}" class="group rounded-2xl border border-gray-200 bg-white shadow-sm p-5 hover:ring-red-300 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50">
                            <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-red-600">Pending Blotter Requests</p>
                            <p class="text-xl font-bold text-red-700">{{ number_format($stats['pending_blotter_requests']) }}</p>
                        </div>
                    </div>
                </a>
            @endif

            @if ($user->canAccess('announcements') && isset($stats['pending_announcements']))
                <a href="{{ route('staff.announcements.index') }}" class="group rounded-2xl border border-gray-200 bg-white shadow-sm p-5 hover:ring-purple-300 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-purple-600">Pending Announcements</p>
                            <p class="text-xl font-bold text-purple-700">{{ number_format($stats['pending_announcements']) }}</p>
                        </div>
                    </div>
                </a>
            @endif

            @if ($user->canAccess('complaints') && isset($stats['pending_issues']))
                <a href="{{ route('staff.issues.index') }}" class="group rounded-2xl border border-gray-200 bg-white shadow-sm p-5 hover:ring-orange-300 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                            <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-orange-600">Open Issues</p>
                            <p class="text-xl font-bold text-orange-700">{{ number_format($stats['pending_issues'] + ($stats['in_progress_issues'] ?? 0)) }}</p>
                        </div>
                    </div>
                </a>
            @endif

            @if ($user->canAccess('reports') && isset($stats['pending_certificates']))
                <a href="{{ route('staff.reports.index') }}" class="group rounded-2xl border border-gray-200 bg-white shadow-sm p-5 hover:ring-green-300 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 group-hover:text-green-600">Pending Certificates</p>
                            <p class="text-xl font-bold text-green-700">{{ number_format($stats['pending_certificates']) }}</p>
                        </div>
                    </div>
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="px-4 pb-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Recent Activity</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Latest updates from your accessible modules</p>
                </div>
            </div>
            <div class="p-5">
                @if ($recentActivity->isEmpty())
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <svg class="h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm text-gray-500">No recent activity to display.</p>
                        <p class="text-xs text-gray-400 mt-1">Activity will appear here based on your module access.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($recentActivity as $activity)
                            <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="mt-0.5">
                                    @if ($activity['type'] === 'issue')
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-orange-100 text-orange-600">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                        </div>
                                    @else
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-100 text-red-600">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $activity['title'] }}</p>
                                    <p class="text-xs text-gray-500">
                                        By {{ $activity['by'] }}
                                        <span class="mx-1 text-gray-300">&middot;</span>
                                        @php
                                            $statusColor = match($activity['status']) {
                                                'pending' => 'bg-amber-100 text-amber-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'released' => 'bg-blue-100 text-blue-800',
                                                'in_progress' => 'bg-blue-100 text-blue-800',
                                                'resolved' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-1.5 py-0.5 text-xs font-medium {{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $activity['status'])) }}</span>
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ $activity['date']->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Module Permissions Overview --}}
<div class="px-4 pb-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Your Module Permissions</h3>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                @php
                    $modules = [
                        ['key' => 'registrations', 'label' => 'Registrations', 'enabled' => 'bg-amber-50 border-amber-200 text-amber-800', 'dot' => 'bg-amber-500'],
                        ['key' => 'blotter', 'label' => 'e-Blotter', 'enabled' => 'bg-red-50 border-red-200 text-red-800', 'dot' => 'bg-red-500'],
                        ['key' => 'announcements', 'label' => 'Announcements', 'enabled' => 'bg-purple-50 border-purple-200 text-purple-800', 'dot' => 'bg-purple-500'],
                        ['key' => 'complaints', 'label' => 'Complaints', 'enabled' => 'bg-orange-50 border-orange-200 text-orange-800', 'dot' => 'bg-orange-500'],
                        ['key' => 'reports', 'label' => 'Reports', 'enabled' => 'bg-green-50 border-green-200 text-green-800', 'dot' => 'bg-green-500'],
                    ];
                @endphp
                @foreach ($modules as $mod)
                    @php $has = $user->canAccess($mod['key']); @endphp
                    <div class="rounded-lg border p-3 text-center {{ $has ? $mod['enabled'] : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-center gap-1.5 mb-1">
                            <span class="inline-block h-2 w-2 rounded-full {{ $has ? $mod['dot'] : 'bg-gray-300' }}"></span>
                            <span class="text-xs font-semibold {{ $has ? '' : 'text-gray-400' }}">{{ $has ? 'ON' : 'OFF' }}</span>
                        </div>
                        <p class="text-sm font-medium {{ $has ? '' : 'text-gray-400' }}">{{ $mod['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
