@extends('layouts.auth')

@section('title', 'Reset Password - e-Governance System')

@section('content')
<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">Reset Password</h1>
            <p class="mt-1 text-sm text-gray-600">Choose a new password for your account.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

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
                Reset Password
            </button>
        </form>
    </div>
</div>
@endsection

