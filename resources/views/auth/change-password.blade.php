@extends('layouts.auth')

@section('title', 'Change Password - e-Governance System')

@section('content')
<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        <div class="mb-6">
            <a href="{{ route('profile.show') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
            <div class="text-center mt-4">
                <h1 class="text-xl font-bold text-gray-900">Change Password</h1>
                <p class="mt-1 text-sm text-gray-600">Update your account password securely.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/profile/password') }}" class="space-y-5">
            @csrf
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                    Current password
                </label>
                <input type="password"
                       name="current_password"
                       id="current_password"
                       required
                       autocomplete="current-password"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('current_password') border-red-500 @enderror">
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    New password
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       required
                       autocomplete="new-password"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm new password
                </label>
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       required
                       autocomplete="new-password"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            </div>
            <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-red-600 to-red-700 py-3 px-4 font-semibold text-white shadow-md hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                Update Password
            </button>
        </form>
    </div>
</div>
@endsection

