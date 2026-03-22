<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Governance Admin Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        @media print {
            .admin-sidebar, .admin-topbar, .admin-overlay, .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .admin-main { margin-left: 0 !important; padding-top: 0 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #d1d5db !important; padding: 6px 10px !important; font-size: 11px !important; }
            th { background-color: #f3f4f6 !important; }
            tfoot td { background-color: #f3f4f6 !important; font-weight: 700 !important; }
            .rounded-xl, .rounded-lg { border-radius: 0 !important; }
            .shadow-sm, .shadow-lg { box-shadow: none !important; }
            .ring-1, .ring-2 { box-shadow: none !important; }
            .overflow-hidden, .overflow-x-auto { overflow: visible !important; }
            @page { margin: 1.5cm; size: A4 landscape; }
        }
    </style>
    @include('partials.ui-foundation-styles')
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 antialiased">

@php
    $authUser = auth()->user();
    $rp = $routePrefix ?? 'admin';
    $canManageRegistrations = $authUser?->canAccess('registrations') ?? false;
@endphp

{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay" class="admin-overlay fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm hidden lg:hidden" onclick="toggleSidebar()"></div>

{{-- ============================================================ --}}
{{-- SIDEBAR --}}
{{-- ============================================================ --}}
<aside id="admin-sidebar" class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out lg:z-30 lg:translate-x-0">

    {{-- Brand header --}}
    <a href="{{ route('admin.dashboard') }}" class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 px-5 group">
        <img src="{{ asset('images/logo1.png') }}" alt="Logo" class="h-9 w-auto shrink-0 object-contain">
        <span class="truncate text-sm font-semibold tracking-tight text-gray-800 group-hover:text-gray-900 transition">Barangay Paguiruan, Floridablanca</span>
    </a>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4">

        {{-- ===================== GOVERNANCE ===================== --}}
        <div class="px-3 mb-2">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Governance</p>
        </div>

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.dashboard') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.dashboard') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        {{-- All Residents --}}
        <a href="{{ route('admin.residents.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.residents.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.residents.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            All Residents
        </a>

        @if ($canManageRegistrations)
            {{-- Pending Registrations --}}
            <a href="{{ route('admin.pending-registrations.index') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.pending-registrations.*') || request()->routeIs('admin.approval-history.*') || request()->routeIs('admin.verifications.*') || request()->routeIs('admin.head-transfer-requests.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.pending-registrations.*') || request()->routeIs('admin.approval-history.*') || request()->routeIs('admin.verifications.*') || request()->routeIs('admin.head-transfer-requests.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Registration Mgmt
            </a>

            {{-- Sub-links for Registration (visible when section is active) --}}
            @if (request()->routeIs('admin.pending-registrations.*') || request()->routeIs('admin.approval-history.*') || request()->routeIs('admin.verifications.*') || request()->routeIs('admin.head-transfer-requests.*'))
                <div class="ml-8 space-y-0.5 border-l-2 border-gray-200 pl-3 mb-1">
                    <a href="{{ route('admin.pending-registrations.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.pending-registrations.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Pending
                    </a>
                    <a href="{{ route('admin.approval-history.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.approval-history.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Approval History
                    </a>
                    <a href="{{ route('admin.verifications.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.verifications.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Verifications
                    </a>
                    <a href="{{ route('admin.head-transfer-requests.index') }}"
                       class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.head-transfer-requests.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                        Head Transfers
                    </a>
                </div>
            @endif
        @endif

        {{-- Officials Management --}}
        <a href="{{ route('admin.officials.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.officials.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.officials.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Officials & Staff
        </a>

        {{-- Purok Management --}}
        <a href="{{ route('admin.puroks.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.puroks.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.puroks.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Purok Management
        </a>

        {{-- Household --}}
        <a href="{{ route('admin.reports.households') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.reports.households') || request()->routeIs('admin.reports.households.view*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.reports.households') || request()->routeIs('admin.reports.households.view*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5l9-7.5 9 7.5M5.25 9.75V20.25h13.5V9.75M9 20.25V14.25h6v6"/>
            </svg>
            Household
        </a>

        {{-- ===================== SERVICES ===================== --}}
        <div class="px-3 mt-6 mb-2">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Services</p>
        </div>

        {{-- Certificate Requests --}}
        <a href="{{ route('admin.certificates.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.certificates.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.certificates.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Certificates
        </a>

        {{-- Permit Requests --}}
        <a href="{{ route('admin.permits.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.permits.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.permits.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
            </svg>
            Permits
        </a>

        {{-- Issue Reports / Complaints --}}
        <a href="{{ route('admin.issues.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.issues.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.issues.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            Complaints
        </a>

        {{-- ===================== OPERATIONS ===================== --}}
        <div class="px-3 mt-6 mb-2">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Operations</p>
        </div>

        {{-- e-Blotter --}}
        @php $isBlotterActive = request()->routeIs('admin.blotters.*') || request()->routeIs('admin.blotter-requests.*'); @endphp
        <a href="{{ route('admin.blotters.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ $isBlotterActive ? 'ui-nav-blotter-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ $isBlotterActive ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            e-Blotter
        </a>

        @if ($isBlotterActive)
            <div class="ml-8 space-y-0.5 border-l-2 border-red-200 pl-3 mb-1">
                <a href="{{ route('admin.blotters.index') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.blotters.*') ? 'ui-nav-blotter-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Records
                </a>
                <a href="{{ route('admin.blotter-requests.index') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.blotter-requests.*') ? 'ui-nav-blotter-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Requests
                </a>
            </div>
        @endif

        {{-- Announcements --}}
        @php $isAnnouncementsActive = request()->routeIs('admin.announcements.*') || request()->routeIs('admin.announcement-labels.*'); @endphp
        <a href="{{ route('admin.announcements.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ $isAnnouncementsActive ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ $isAnnouncementsActive ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            Announcements
        </a>

        @if ($isAnnouncementsActive)
            <div class="ml-8 space-y-0.5 border-l-2 border-gray-200 pl-3 mb-1">
                <a href="{{ route('admin.announcements.index') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.announcements.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    All Announcements
                </a>
                <a href="{{ route('admin.announcement-labels.index') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.announcement-labels.*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Manage Labels
                </a>
            </div>
        @endif

        {{-- ===================== ANALYTICS ===================== --}}
        <div class="px-3 mt-6 mb-2">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Analytics</p>
        </div>

        {{-- Reports --}}
        <a href="{{ route('admin.reports.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.reports.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.reports.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Reports & Analytics
        </a>

        @if (request()->routeIs('admin.reports.*'))
            <div class="ml-8 space-y-0.5 border-l-2 border-gray-200 pl-3 mb-1">
                <a href="{{ route('admin.reports.index') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.reports.index') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Overview
                </a>
                <a href="{{ route('admin.reports.population') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.reports.population') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Population
                </a>
                <a href="{{ route('admin.reports.classification') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.reports.classification') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Classification
                </a>
                <a href="{{ route('admin.reports.services') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.reports.services') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Services
                </a>
                <a href="{{ route('admin.reports.households') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.reports.households') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Households
                </a>
                <a href="{{ route('admin.reports.blotter') }}"
                   class="block rounded-md px-2 py-1.5 text-[13px] transition {{ request()->routeIs('admin.reports.blotter*') ? 'ui-nav-sub-active' : 'text-gray-500 hover:text-gray-700' }}">
                    Blotter
                </a>
            </div>
        @endif

        {{-- ===================== SYSTEM ===================== --}}
        <div class="px-3 mt-6 mb-2">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">System</p>
        </div>

        {{-- Audit Log --}}
        <a href="{{ route('admin.audit.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.audit.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.audit.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Audit Log
        </a>

        {{-- Login Activity --}}
        <a href="{{ route('admin.login-activities.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.login-activities.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.login-activities.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            Login Activity
        </a>

        {{-- SMS Management --}}
        <a href="{{ route('admin.sms.index') }}"
           class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150
                  {{ request()->routeIs('admin.sms.*') ? 'ui-nav-active' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('admin.sms.*') ? '' : 'text-gray-400 group-hover:text-gray-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9h6.75m-6.75 3h4.5m-6.375 8.25h10.5c1.242 0 2.25-1.008 2.25-2.25V6.75A2.25 2.25 0 0017.25 4.5H6.75A2.25 2.25 0 004.5 6.75V18A2.25 2.25 0 006.75 20.25z"/>
            </svg>
            SMS Management
        </a>

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
<header class="admin-topbar fixed top-0 right-0 left-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 lg:left-64 lg:px-6">

    {{-- Left: Mobile hamburger + page title --}}
    <div class="flex items-center gap-3">
        {{-- Mobile hamburger --}}
        <button type="button" onclick="toggleSidebar()" class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Page title --}}
        <h1 class="text-lg font-semibold text-gray-900 truncate">
            @yield('page_title', 'Dashboard')
        </h1>
    </div>

    {{-- Right: user info + actions --}}
    <div class="flex items-center gap-3">
        {{-- Position badge --}}
        @if ($authUser->position)
            <span class="hidden sm:inline-flex ui-position-badge">
                {{ $authUser->position->name }}
            </span>
        @endif

        {{-- User name (desktop) --}}
        <span class="hidden md:block text-sm font-medium text-gray-600">
            {{ $authUser->first_name }} {{ $authUser->last_name }}
        </span>

        {{-- Divider --}}
        <div class="hidden sm:block h-6 w-px bg-gray-200"></div>

        {{-- Quick links --}}
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

        {{-- Logout --}}
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
<main class="admin-main min-h-screen pt-16 lg:ml-64">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="admin-main lg:ml-64 border-t border-gray-200 bg-white py-5">
    <div class="mx-auto max-w-7xl px-4 md:px-6 text-center text-sm text-gray-500">
        <p class="font-medium text-gray-700">Barangay Paguiruan, Floridablanca</p>
        <p class="mt-0.5">&copy; {{ date('Y') }} e-Governance System. All rights reserved.</p>
    </div>
</footer>

{{-- Sidebar toggle script --}}
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
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
