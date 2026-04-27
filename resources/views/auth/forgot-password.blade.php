@extends('layouts.auth')

@section('title', 'Forgot Password - e-Governance System')

@section('content')
<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">Forgot Password</h1>
            <p class="mt-1 text-sm text-gray-600">Enter your email address to request a password reset link.</p>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email address
                </label>
                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus">
            </div>
            <button type="submit"
                    class="ui-btn ui-btn-primary ui-btn-lg w-full rounded-lg py-3 shadow-sm">
                Send Reset Link
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Remembered your password?
            <a href="{{ route('login') }}" class="ui-link font-medium">Back to login</a>
        </p>
    </div>
</div>
@endsection

