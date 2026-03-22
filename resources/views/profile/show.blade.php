@extends('layouts.resident')

@section('title', 'My Profile - e-Governance System')

@section('content')
<section class="px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">

        {{-- Header --}}
        <div class="ui-surface-card mb-6 p-5 sm:p-6">
            <p class="ui-kpi-label">Resident Account</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">My Profile</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your personal details, family linkage, and verification claims.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Route to the correct tab when validation errors exist --}}
        @php
            $currentTab = $tab ?? 'info';
            if ($errors->any()) {
                if (session('_family_form')) {
                    $currentTab = 'family';
                } elseif (session('_family_transfer_form')) {
                    $currentTab = 'family';
                } elseif (session('_verification_form')) {
                    $currentTab = 'verification';
                } else {
                    $currentTab = 'info';
                }
            }
            // Prevent residents from accessing the edit tab directly
            if ($currentTab === 'edit') {
                $currentTab = 'info';
            }
        @endphp
        <div class="ui-surface-card mb-6 p-2">
            <nav class="flex flex-wrap gap-2">
                <a href="{{ route('profile.show', ['tab' => 'info']) }}"
                   class="ui-focus-ring rounded-lg px-3 py-2 text-sm font-medium transition {{ $currentTab === 'info' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                    Profile Information
                </a>
                <a href="{{ route('profile.show', ['tab' => 'family']) }}"
                   class="ui-focus-ring rounded-lg px-3 py-2 text-sm font-medium transition {{ $currentTab === 'family' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                    Head of the Family
                </a>
                <a href="{{ route('profile.show', ['tab' => 'verification']) }}"
                   class="ui-focus-ring rounded-lg px-3 py-2 text-sm font-medium transition {{ $currentTab === 'verification' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800' }}">
                    Verification & Claims
                </a>
                <a href="{{ route('password.edit') }}"
                   class="ui-focus-ring rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-800">
                    Change Password
                </a>
            </nav>
        </div>

        {{-- Tab Content --}}
        @if ($currentTab === 'info')
            @include('profile._tab_info')
        @elseif ($currentTab === 'family')
            @include('profile._tab_family')
        @elseif ($currentTab === 'verification')
            @include('profile._tab_verification')
        @endif

    </div>
</section>
@endsection
