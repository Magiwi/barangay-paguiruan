@extends('layouts.auth')

@section('title', 'Create your login - e-Governance System')

@section('content')
<div class="w-full max-w-md">
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('images/logo1.png') }}" alt="Barangay Paguiruan" class="h-20 md:h-24 w-auto object-contain mb-3">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Create your login</h1>
        <p class="mt-1 text-center text-sm text-gray-500">This is for {{ $user->full_name }}. Choose the email and password you will use next time.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account.claim.complete') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" autocomplete="new-password"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm">
            </div>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full rounded-lg py-3 shadow-sm">Create account</button>
        </form>
    </div>
</div>
@endsection
