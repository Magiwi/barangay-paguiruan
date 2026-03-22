@extends($layout ?? 'layouts.admin')

@section('title', 'Dashboard - e-Governance Admin Panel')
@section('page_title', 'Dashboard')

@section('content')
{{-- Welcome banner --}}
<div class="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h2 class="text-xl font-bold text-white sm:text-2xl">
            Welcome back, {{ auth()->user()->first_name }}
        </h2>
        <p class="mt-1 text-sm text-blue-100">
            Manage residents, requests, and barangay operations from your dashboard.
        </p>
    </div>
</div>

{{-- Statistics cards --}}
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-4">Overview</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

            {{-- Total Residents --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500">Total Residents</p>
                        <p class="text-xl font-bold text-gray-900">{{ number_format($stats['total_residents']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Pending Requests --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                        <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500">Pending Requests</p>
                        <p class="text-xl font-bold text-amber-700">{{ number_format($stats['pending_requests']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Approved Certificates --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50">
                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500">Approved Certs</p>
                        <p class="text-xl font-bold text-green-700">{{ number_format($stats['approved_certificates']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Released Certificates --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50">
                        <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500">Released Certs</p>
                        <p class="text-xl font-bold text-indigo-700">{{ number_format($stats['released_certificates']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Pending Permits --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50">
                        <svg class="h-5 w-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500">Pending Permits</p>
                        <p class="text-xl font-bold text-teal-700">{{ number_format($stats['pending_permits']) }}</p>
                    </div>
                </div>
            </div>

            {{-- Reported Issues --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50">
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500">Reported Issues</p>
                        <p class="text-xl font-bold text-red-700">{{ number_format($stats['reported_issues']) }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Recent activity --}}
<section class="px-4 pb-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Recent certificate requests --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Recent Certificate Requests</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Latest requests from residents</p>
                    </div>
                    <a href="{{ route('admin.certificates.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">View all</a>
                </div>
                <div class="p-5">
                    @if ($recentCertificates->isEmpty())
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <svg class="h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm text-gray-400">No certificate requests yet.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($recentCertificates as $cert)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $cert->user->first_name }} {{ $cert->user->last_name }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $cert->certificate_type }}</p>
                                        </div>
                                        <div class="ml-3 flex flex-col items-end gap-1">
                                            @if ($cert->status === 'pending')
                                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending</span>
                                            @elseif ($cert->status === 'approved')
                                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Approved</span>
                                            @elseif ($cert->status === 'released')
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">Released</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Rejected</span>
                                            @endif
                                            <span class="text-[11px] text-gray-400">{{ $cert->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Recent issue reports --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Recent Issue Reports</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Issues reported by residents</p>
                    </div>
                    <a href="{{ route('admin.issues.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">View all</a>
                </div>
                <div class="p-5">
                    @if ($recentIssues->isEmpty())
                        <div class="flex flex-col items-center justify-center py-6 text-center">
                            <svg class="h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-sm text-gray-400">No issue reports yet.</p>
                        </div>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($recentIssues as $issue)
                                <li class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $issue->user->first_name }} {{ $issue->user->last_name }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $issue->subject }}</p>
                                        </div>
                                        <div class="ml-3 flex flex-col items-end gap-1">
                                            @if ($issue->status === 'pending')
                                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending</span>
                                            @elseif ($issue->status === 'in_progress')
                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">In Progress</span>
                                            @elseif ($issue->status === 'resolved')
                                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Resolved</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-medium text-gray-700 ring-1 ring-inset ring-gray-600/20">Closed</span>
                                            @endif
                                            <span class="text-[11px] text-gray-400">{{ $issue->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
