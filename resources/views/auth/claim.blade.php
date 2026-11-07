@extends('layouts.auth')

@section('title', 'Activate account - e-Governance System')

@section('content')
<div class="w-full max-w-md">
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('images/logo1.png') }}" alt="Barangay Paguiruan" class="h-20 md:h-24 w-auto object-contain mb-3">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Activate your account</h1>
        <p class="mt-1 text-center text-sm text-gray-500">Enter the code written down at the barangay office.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('account.claim.store') }}" class="space-y-6">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Activation code</label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" required autocomplete="one-time-code"
                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-center font-mono text-lg tracking-widest text-gray-900 shadow-sm">
            </div>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full rounded-lg py-3 shadow-sm">Continue</button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            <a href="{{ route('login') }}" class="ui-link font-medium">Back to login</a>
        </p>
    </div>
</div>
@endsection
