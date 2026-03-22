@extends($layout ?? 'layouts.admin')

@section('title', 'Reports & Analytics - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Reports & Analytics</h1>
            <p class="mt-1 text-sm text-gray-500">Overview of barangay statistics and key metrics</p>
        </div>

        {{-- Section: Key Metrics --}}
        <div class="mb-8">
            <x-ui.section-heading title="Key Metrics" />
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Total Residents --}}
                <a
                    href="{{ route($rp . '.reports.population') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View population report from Total Residents metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">Total Residents</p>
                            <p class="ui-kpi-value">{{ number_format($stats['total_residents']) }}</p>
                        </div>
                    </div>
                </a>

                {{-- Active Residents --}}
                <a
                    href="{{ route($rp . '.reports.population') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View population report from Active Residents metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">Active</p>
                            <p class="ui-kpi-value">{{ number_format($stats['approved_residents']) }}</p>
                        </div>
                    </div>
                </a>

                {{-- PWD --}}
                <a
                    href="{{ route($rp . '.reports.classification') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View classification report from PWD metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">PWD</p>
                            <p class="ui-kpi-value">{{ number_format($stats['total_pwd']) }}</p>
                        </div>
                    </div>
                </a>

                {{-- Senior Citizens --}}
                <a
                    href="{{ route($rp . '.reports.classification') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View classification report from Senior Citizens metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">Senior</p>
                            <p class="ui-kpi-value">{{ number_format($stats['total_senior']) }}</p>
                        </div>
                    </div>
                </a>

                {{-- Permits --}}
                <a
                    href="{{ route($rp . '.reports.services') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View services report from Permits metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">Permits</p>
                            <p class="ui-kpi-value">{{ number_format($stats['total_permits']) }}</p>
                        </div>
                    </div>
                </a>

                {{-- Total Households --}}
                <a
                    href="{{ route($rp . '.reports.households') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View household report from Total Households metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">Households</p>
                            <p class="ui-kpi-value">{{ number_format($stats['total_households']) }}</p>
                        </div>
                    </div>
                </a>

                {{-- Avg Household Size --}}
                <a
                    href="{{ route($rp . '.reports.households') }}"
                    class="group ui-surface-card ui-surface-card-hover ui-focus-ring block p-4"
                    aria-label="View household report from Average Household Size metric"
                >
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="ui-kpi-label truncate">Avg HH Size</p>
                            <p class="ui-kpi-value">{{ $stats['avg_household_size'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Section: Registration Pipeline --}}
        <div class="mb-8">
            <x-ui.section-heading title="Registration Pipeline" />
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-ui.metric-icon-card
                    label="Pending"
                    :value="number_format($stats['pending_registrations'] ?? 0)"
                    wrapper-class="ui-surface-card ui-surface-card-hover border-amber-200 bg-amber-50/35 p-5"
                    icon-wrapper-class="ui-icon-chip bg-amber-100 text-amber-700"
                    label-class="ui-kpi-label text-amber-700"
                    value-class="mt-1 text-3xl font-bold text-amber-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>

                <x-ui.metric-icon-card
                    label="Rejected"
                    :value="number_format($stats['rejected_registrations'] ?? 0)"
                    wrapper-class="ui-surface-card ui-surface-card-hover border-red-200 bg-red-50/35 p-5"
                    icon-wrapper-class="ui-icon-chip bg-red-100 text-red-700"
                    label-class="ui-kpi-label text-red-700"
                    value-class="mt-1 text-3xl font-bold text-red-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>

                <x-ui.metric-icon-card
                    label="Approved Today"
                    :value="number_format($stats['approved_today_registrations'] ?? 0)"
                    wrapper-class="ui-surface-card ui-surface-card-hover border-emerald-200 bg-emerald-50/35 p-5"
                    icon-wrapper-class="ui-icon-chip bg-emerald-100 text-emerald-700"
                    label-class="ui-kpi-label text-emerald-700"
                    value-class="mt-1 text-3xl font-bold text-emerald-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>

                <x-ui.metric-icon-card
                    label="Avg Approval Time"
                    :value="number_format($stats['avg_approval_hours'] ?? 0, 1) . 'h'"
                    wrapper-class="ui-surface-card ui-surface-card-hover border-blue-200 bg-blue-50/35 p-5"
                    icon-wrapper-class="ui-icon-chip bg-blue-100 text-blue-700"
                    label-class="ui-kpi-label text-blue-700"
                    value-class="mt-1 text-3xl font-bold text-blue-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
            </div>
        </div>

        {{-- Section: Service Status --}}
        <div class="mb-8">
            <x-ui.section-heading title="Service Status" />
            <div class="grid gap-4 md:grid-cols-3">
                {{-- Certificate Requests --}}
                <div class="ui-surface-card ui-surface-card-hover p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Certificate Requests</h3>
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.certificates.index') }}" class="text-xs text-blue-600 hover:text-blue-700">View all</a>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 rounded-lg bg-amber-50/60 p-3 text-center">
                            <p class="text-2xl font-bold text-amber-700">{{ number_format($stats['pending_certificates']) }}</p>
                            <p class="text-xs text-amber-600">Pending</p>
                        </div>
                        <div class="flex-1 rounded-lg bg-blue-50/60 p-3 text-center">
                            <p class="text-2xl font-bold text-blue-700">{{ number_format($stats['released_certificates']) }}</p>
                            <p class="text-xs text-blue-600">Released</p>
                        </div>
                    </div>
                </div>

                {{-- Permit Applications --}}
                <div class="ui-surface-card ui-surface-card-hover p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Permit Applications</h3>
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.permits.index') }}" class="text-xs text-blue-600 hover:text-blue-700">View all</a>
                        @endif
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1 rounded-lg bg-emerald-50/60 p-3 text-center">
                            <p class="text-2xl font-bold text-emerald-700">{{ number_format($stats['pending_permits']) }}</p>
                            <p class="text-xs text-emerald-600">Pending</p>
                        </div>
                    </div>
                </div>

                {{-- Issue Reports --}}
                <div class="ui-surface-card ui-surface-card-hover p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Issue Reports</h3>
                        <a href="{{ route($rp . '.issues.index') }}" class="text-xs text-blue-600 hover:text-blue-700">View all</a>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1 rounded-lg bg-red-50/60 p-3 text-center">
                            <p class="text-2xl font-bold text-red-700">{{ number_format($stats['pending_issues']) }}</p>
                            <p class="text-xs text-red-600">Open Issues</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Detailed Reports --}}
        <div>
            <x-ui.section-heading title="Detailed Reports" />
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                {{-- Population Reports --}}
                <a href="{{ route($rp . '.reports.population') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex h-full min-h-[220px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-blue-700">Population Reports</h3>
                    </div>
                    <p class="mt-3 min-h-[84px] text-sm leading-6 text-gray-500">Residents by purok, type, and activity status</p>
                    <p class="mt-auto flex items-center gap-1 pt-3 text-sm font-medium text-gray-600 transition group-hover:text-blue-700">
                        View report
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </p>
                </a>

                {{-- Classification Reports --}}
                <a href="{{ route($rp . '.reports.classification') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex h-full min-h-[220px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-blue-700">Classification Reports</h3>
                    </div>
                    <p class="mt-3 min-h-[84px] text-sm leading-6 text-gray-500">PWD and Senior Citizen classifications</p>
                    <p class="mt-auto flex items-center gap-1 pt-3 text-sm font-medium text-gray-600 transition group-hover:text-blue-700">
                        View report
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </p>
                </a>

                {{-- Service Reports --}}
                <a href="{{ route($rp . '.reports.services') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex h-full min-h-[220px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-blue-700">Service Reports</h3>
                    </div>
                    <p class="mt-3 min-h-[84px] text-sm leading-6 text-gray-500">Certificates, permits, and issue reports</p>
                    <p class="mt-auto flex items-center gap-1 pt-3 text-sm font-medium text-gray-600 transition group-hover:text-blue-700">
                        View report
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </p>
                </a>

                {{-- Household Reports --}}
                <a href="{{ route($rp . '.reports.households') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex h-full min-h-[220px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-blue-700">Household Reports</h3>
                    </div>
                    <p class="mt-3 min-h-[84px] text-sm leading-6 text-gray-500">Household count, members, and head of family data</p>
                    <p class="mt-auto flex items-center gap-1 pt-3 text-sm font-medium text-gray-600 transition group-hover:text-blue-700">
                        View report
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </p>
                </a>

                {{-- Blotter Reports --}}
                <a href="{{ route($rp . '.reports.blotter') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex h-full min-h-[220px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-blue-700">Blotter Reports</h3>
                    </div>
                    <p class="mt-3 min-h-[84px] text-sm leading-6 text-gray-500">Case trends, status distribution, and complaint categories</p>
                    <p class="mt-auto flex items-center gap-1 pt-3 text-sm font-medium text-gray-600 transition group-hover:text-blue-700">
                        View report
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </p>
                </a>

                {{-- Household Timeline --}}
                <a href="{{ route($rp . '.reports.households.timeline') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex h-full min-h-[220px] flex-col p-5">
                    <div class="flex items-center gap-3">
                        <div class="ui-icon-chip transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-900 transition group-hover:text-blue-700">Household Timeline</h3>
                    </div>
                    <p class="mt-3 min-h-[84px] text-sm leading-6 text-gray-500">Audit trail of family member actions per head household</p>
                    <p class="mt-auto flex items-center gap-1 pt-3 text-sm font-medium text-gray-600 transition group-hover:text-blue-700">
                        View timeline
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </p>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
