@extends($layout ?? 'layouts.admin')

@section('title', 'Resident Verifications - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    @php
        $verificationType = $verificationType ?? 'all';
        $verificationCounts = $verificationCounts ?? ['all' => $residents->count(), 'pwd' => 0, 'senior' => 0];
    @endphp
    <div class="mx-auto max-w-7xl">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-2">Resident Verifications</h1>
        <p class="text-sm text-gray-600 mb-6">Review and verify PWD and Senior Citizen classifications.</p>

        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route($rp . '.verifications.index', ['type' => 'all']) }}"
               class="rounded-2xl px-5 py-2.5 text-sm font-medium transition {{ $verificationType === 'all' ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                All
                <span class="ml-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full {{ $verificationType === 'all' ? 'bg-white/25 text-white' : 'bg-gray-300 text-gray-600' }} px-2 text-xs font-bold">{{ $verificationCounts['all'] ?? 0 }}</span>
            </a>
            <a href="{{ route($rp . '.verifications.index', ['type' => 'pwd']) }}"
               class="rounded-2xl px-5 py-2.5 text-sm font-medium transition {{ $verificationType === 'pwd' ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                PWD
                <span class="ml-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full {{ $verificationType === 'pwd' ? 'bg-white/25 text-white' : 'bg-gray-300 text-gray-600' }} px-2 text-xs font-bold">{{ $verificationCounts['pwd'] ?? 0 }}</span>
            </a>
            <a href="{{ route($rp . '.verifications.index', ['type' => 'senior']) }}"
               class="rounded-2xl px-5 py-2.5 text-sm font-medium transition {{ $verificationType === 'senior' ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Senior
                <span class="ml-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full {{ $verificationType === 'senior' ? 'bg-white/25 text-white' : 'bg-gray-300 text-gray-600' }} px-2 text-xs font-bold">{{ $verificationCounts['senior'] ?? 0 }}</span>
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        @if ($residents->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No pending verifications</h3>
                <p class="mt-1 text-sm text-gray-500">All classification submissions are currently verified.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($residents as $resident)
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="p-5">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                {{-- Resident Info --}}
                                <div class="flex-1">
                                    <h3 class="text-base font-semibold text-gray-900">
                                        {{ $resident->last_name }}, {{ $resident->first_name }}
                                        @if ($resident->middle_name)
                                            <span class="text-gray-500 font-normal">{{ $resident->middle_name }}</span>
                                        @endif
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">Purok:</span> {{ $resident->purok }}
                                        <span class="mx-2 text-gray-300">|</span>
                                        <span class="font-medium">Email:</span> {{ $resident->email }}
                                    </p>
                                </div>
                            </div>

                            {{-- Classifications --}}
                            <div class="mt-4 space-y-4">
                                {{-- PWD Verification --}}
                                @if ($verificationType !== 'senior' && $resident->is_pwd && $resident->pwd_status === 'pending')
                                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                                                        PWD
                                                    </span>
                                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                                        Pending
                                                    </span>
                                                </div>
                                                @if ($resident->pwd_proof_path)
                                                    <div class="mt-3">
                                                        <p class="text-xs font-medium text-gray-700 mb-2">Uploaded Proof:</p>
                                                        @php
                                                            $pwdExt = pathinfo($resident->pwd_proof_path, PATHINFO_EXTENSION);
                                                            $isPwdImage = in_array(strtolower($pwdExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                        @endphp
                                                        @if ($isPwdImage)
                                                            <a href="{{ asset('storage/' . $resident->pwd_proof_path) }}" target="_blank" class="inline-block">
                                                                <img src="{{ asset('storage/' . $resident->pwd_proof_path) }}" alt="PWD Proof" class="max-w-xs max-h-32 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition">
                                                            </a>
                                                        @else
                                                            <a href="{{ asset('storage/' . $resident->pwd_proof_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700">
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                                </svg>
                                                                View Document (PDF)
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p class="mt-2 text-xs text-gray-500 italic">No proof uploaded</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <form method="POST" action="{{ route($rp . '.verifications.pwd.approve', $resident) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route($rp . '.verifications.pwd.reject', $resident) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Senior Citizen Verification --}}
                                @if ($verificationType !== 'pwd' && $resident->is_senior && $resident->senior_status === 'pending')
                                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800">
                                                        Senior Citizen
                                                    </span>
                                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800">
                                                        Pending
                                                    </span>
                                                </div>
                                                @if ($resident->senior_proof_path)
                                                    <div class="mt-3">
                                                        <p class="text-xs font-medium text-gray-700 mb-2">Uploaded Proof:</p>
                                                        @php
                                                            $seniorExt = pathinfo($resident->senior_proof_path, PATHINFO_EXTENSION);
                                                            $isSeniorImage = in_array(strtolower($seniorExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                        @endphp
                                                        @if ($isSeniorImage)
                                                            <a href="{{ asset('storage/' . $resident->senior_proof_path) }}" target="_blank" class="inline-block">
                                                                <img src="{{ asset('storage/' . $resident->senior_proof_path) }}" alt="Senior Citizen Proof" class="max-w-xs max-h-32 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition">
                                                            </a>
                                                        @else
                                                            <a href="{{ asset('storage/' . $resident->senior_proof_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700">
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                                </svg>
                                                                View Document (PDF)
                                                            </a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <p class="mt-2 text-xs text-gray-500 italic">No proof uploaded</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <form method="POST" action="{{ route($rp . '.verifications.senior.approve', $resident) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route($rp . '.verifications.senior.reject', $resident) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
