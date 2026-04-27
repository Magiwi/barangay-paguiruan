@extends('layouts.auth')

@section('title', 'Login - e-Governance System')

@section('content')
<div class="w-full max-w-md">

    {{-- Logo & Branding --}}
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('images/logo1.png') }}"
             alt="Barangay Paguiruan"
             class="h-20 md:h-24 w-auto object-contain mb-3">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Barangay Paguiruan, Floridablanca</h1>
        <p class="mt-1 text-sm text-gray-500">Barangay Management Portal</p>
    </div>

    {{-- Login Card --}}
    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email"
                       placeholder="you@example.com"
                       class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('email') border-red-400 @else border-gray-300 @enderror">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password with eye toggle --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <div class="relative">
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           autocomplete="current-password"
                           placeholder="Enter your password"
                           class="block w-full rounded-lg border px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('password') border-red-400 @else border-gray-300 @enderror">
                    <button type="button" onclick="togglePassword('password', this)"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition"
                            aria-label="Show password" aria-pressed="false">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember Me + Forgot Password --}}
            <div class="flex items-center justify-between">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-[var(--brand-700)] focus:ring-[var(--brand-100)]">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="ui-link text-xs transition">
                    Forgot your password?
                </a>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="ui-btn ui-btn-primary ui-btn-lg w-full rounded-lg py-3 shadow-sm">
                Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="ui-link font-medium transition">Register</a>
        </p>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    var input = document.getElementById(fieldId);
    var showLabel = fieldId === 'password_confirmation' ? 'Show confirm password' : 'Show password';
    var hideLabel = fieldId === 'password_confirmation' ? 'Hide confirm password' : 'Hide password';
    if (input.type === 'password') {
        input.type = 'text';
        btn.setAttribute('aria-label', hideLabel);
        btn.setAttribute('aria-pressed', 'true');
        btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>';
    } else {
        input.type = 'password';
        btn.setAttribute('aria-label', showLabel);
        btn.setAttribute('aria-pressed', 'false');
        btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
    }
}
</script>
@endsection
