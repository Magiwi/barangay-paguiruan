<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Governance Staff Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        @media print {
            .staff-sidebar, .staff-topbar, .staff-overlay, .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .staff-main { margin-left: 0 !important; padding-top: 0 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #d1d5db !important; padding: 6px 10px !important; font-size: 11px !important; }
            th { background-color: #f3f4f6 !important; }
            .rounded-xl, .rounded-lg, .rounded-2xl { border-radius: 0 !important; }
            .shadow-sm, .shadow-lg { box-shadow: none !important; }
            .overflow-hidden, .overflow-x-auto { overflow: visible !important; }
            @page { margin: 1.5cm; size: A4 landscape; }
        }
    </style>
    @include('partials.ui-foundation-styles')
    @include('partials.a11y-skip-link-styles')
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 antialiased">
    @include('partials.a11y-skip-link')

@php
    $authUser = auth()->user();
    $isAdmin = $authUser->role === 'admin';
@endphp

{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay" class="staff-overlay fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm hidden lg:hidden" onclick="toggleSidebar()"></div>

{{-- ============================================================ --}}
{{-- SIDEBAR --}}
{{-- ============================================================ --}}
<aside id="staff-sidebar" class="staff-sidebar fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out lg:z-30 lg:translate-x-0">

    {{-- Brand header --}}
    <a href="{{ route('staff.dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 px-5 group">
        <img src="{{ asset('images/logo1.png') }}" alt="Barangay Paguiruan official seal" class="h-9 w-auto shrink-0 object-contain">
        <span class="truncate text-sm font-semibold tracking-tight text-gray-800 group-hover:text-gray-900 transition">Barangay Paguiruan, Floridablanca</span>
    </a>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">

        {{-- Dashboard --}}
        <a href="{{ route('staff.dashboard') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('staff.dashboard') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('staff.dashboard') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        {{-- Registration Management --}}
        @if ($authUser->canAccess('registrations'))
            <div class="px-3 mt-6 mb-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Registrations</p>
            </div>

            @php $isRegActive = request()->routeIs('staff.pending-registrations.*') || request()->routeIs('staff.approval-history.*') || request()->routeIs('staff.verifications.*'); @endphp
            <a href="{{ route('staff.pending-registrations.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ $isRegActive ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="h-5 w-5 shrink-0 {{ $isRegActive ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Registration Mgmt
            </a>

            @if ($isRegActive)
                <div class="ml-8 space-y-0.5 border-l-2 border-gray-200 pl-3 mb-1">
                    <a href="{{ route('staff.pending-registrations.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.pending-registrations.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Pending
                    </a>
                    <a href="{{ route('staff.approval-history.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.approval-history.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Approval History
                    </a>
                    <a href="{{ route('staff.verifications.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.verifications.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Verifications
                    </a>
                </div>
            @endif
        @endif

        {{-- e-Blotter --}}
        @if ($authUser->canAccess('blotter'))
            <div class="px-3 mt-6 mb-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">e-Blotter</p>
            </div>

            @php $isBlotterActive = request()->routeIs('staff.blotters.*') || request()->routeIs('staff.blotter-requests.*'); @endphp
            <a href="{{ route('staff.blotters.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ $isBlotterActive ? 'ui-nav-blotter-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="h-5 w-5 shrink-0 {{ $isBlotterActive ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                e-Blotter
            </a>

            @if ($isBlotterActive)
                <div class="ml-8 space-y-0.5 border-l-2 border-red-200 pl-3 mb-1">
                    <a href="{{ route('staff.blotters.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.blotters.*') ? 'ui-nav-blotter-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Records
                    </a>
                    <a href="{{ route('staff.blotter-requests.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.blotter-requests.*') ? 'ui-nav-blotter-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Requests
                    </a>
                </div>
            @endif
        @endif

        {{-- Announcements --}}
        @if ($authUser->canAccess('announcements'))
            <div class="px-3 mt-6 mb-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Announcements</p>
            </div>

            <a href="{{ route('staff.announcements.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('staff.announcements.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('staff.announcements.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                All Announcements
            </a>
        @endif

        {{-- Complaints --}}
        @if ($authUser->canAccess('complaints'))
            <div class="px-3 mt-6 mb-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Complaints</p>
            </div>

            <a href="{{ route('staff.issues.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('staff.issues.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('staff.issues.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                Issue Reports
            </a>
        @endif

        {{-- Reports --}}
        @if ($authUser->canAccess('reports'))
            <div class="px-3 mt-6 mb-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Analytics</p>
            </div>

            <a href="{{ route('staff.reports.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('staff.reports.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('staff.reports.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reports & Analytics
            </a>

            @if (request()->routeIs('staff.reports.*'))
                <div class="ml-8 space-y-0.5 border-l-2 border-gray-200 pl-3 mb-1">
                    <a href="{{ route('staff.reports.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.reports.index') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Overview
                    </a>
                    <a href="{{ route('staff.reports.population') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.reports.population') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Population
                    </a>
                    <a href="{{ route('staff.reports.classification') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.reports.classification') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Classification
                    </a>
                    <a href="{{ route('staff.reports.services') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.reports.services') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Services
                    </a>
                    <a href="{{ route('staff.reports.households') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.reports.households') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Households
                    </a>
                    <a href="{{ route('staff.reports.blotter') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('staff.reports.blotter*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Blotter
                    </a>
                </div>
            @endif
        @endif

        {{-- Admin-only sections --}}
        @if ($isAdmin)
            <div class="px-3 mt-6 mb-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Administration</p>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                Admin Dashboard
            </a>

            <a href="{{ route('admin.residents.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                All Residents
            </a>

            <a href="{{ route('admin.officials.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Officials
            </a>

            <a href="{{ route('admin.puroks.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Purok Management
            </a>

            <a href="{{ route('admin.audit.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Audit Log
            </a>

            <a href="{{ route('admin.certificates.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Certificates
            </a>

            <a href="{{ route('admin.permits.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                Permits
            </a>
        @endif

    </nav>

    {{-- User card at bottom --}}
    <div class="shrink-0 border-t border-gray-200 p-4">
        <div class="flex items-center gap-3">
            <div class="ui-avatar-circle h-9 w-9 shrink-0">
                {{ strtoupper(substr($authUser->first_name, 0, 1)) }}{{ strtoupper(substr($authUser->last_name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-gray-900">{{ $authUser->first_name }} {{ $authUser->last_name }}</p>
                <p class="truncate text-xs text-gray-500">{{ ucfirst($authUser->role) }}{{ $authUser->position ? ' — ' . $authUser->position->name : '' }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- ============================================================ --}}
{{-- TOP NAVBAR --}}
{{-- ============================================================ --}}
<header class="staff-topbar fixed top-0 right-0 left-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 lg:left-64 lg:px-6">

    {{-- Left: Mobile hamburger + page title --}}
    <div class="flex items-center gap-3">
        <button type="button" onclick="toggleSidebar()" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition lg:hidden" aria-label="Open navigation menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <h1 class="text-lg font-semibold text-gray-900 truncate">
            @yield('page_title', 'Staff Dashboard')
        </h1>
    </div>

    {{-- Right: user info + actions --}}
    <div class="flex items-center gap-3">
        @if ($authUser->position)
            <span class="hidden sm:inline-flex ui-position-badge">
                {{ $authUser->position->name }}
            </span>
        @endif

        <span class="hidden md:block text-sm font-medium text-gray-600">
            {{ $authUser->first_name }} {{ $authUser->last_name }}
        </span>

        <div class="hidden sm:block h-6 w-px bg-gray-200"></div>

        <a href="{{ route('resident.dashboard') }}" class="hidden sm:inline-flex text-sm text-gray-500 hover:text-gray-700 transition" title="Resident View">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </a>

        <a href="{{ url('/profile') }}" class="hidden sm:inline-flex text-sm text-gray-500 hover:text-gray-700 transition" title="Profile">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 shadow-sm hover:bg-red-50 hover:border-red-200 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="hidden sm:inline">Logout</span>
            </button>
        </form>
    </div>
</header>

{{-- ============================================================ --}}
{{-- MAIN CONTENT --}}
{{-- ============================================================ --}}
<main id="main-content" class="staff-main min-h-screen pt-16 lg:ml-64">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="staff-main lg:ml-64 border-t border-gray-200 bg-white py-5">
    <div class="mx-auto max-w-7xl px-4 md:px-6 text-center text-sm text-gray-500">
        <p class="font-medium text-gray-700">Barangay Paguiruan, Floridablanca</p>
        <p class="mt-0.5">&copy; {{ date('Y') }} e-Governance System. All rights reserved.</p>
    </div>
</footer>

{{-- Sidebar toggle script --}}
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('staff-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        }
    }
</script>
@stack('scripts')
</body>
</html>
