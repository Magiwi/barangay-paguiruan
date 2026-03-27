@extends('layouts.resident')

@section('title', 'Barangay Council - e-Governance System')

@section('content')
<div class="max-w-5xl mx-auto px-4 md:px-6 py-8">
    {{-- Page header (clean, no heavy gradient) --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 via-blue-700 to-green-600 px-6 py-8 mb-8 shadow-lg ring-1 ring-blue-900/20">
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-rule=&quot;evenodd&quot;%3E%3Cpath d=&quot;M0 40L40 0H20L0 20M40 40V20L20 40&quot;/%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative">
            <p class="text-xs uppercase tracking-wider text-blue-100">Republic of the Philippines</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Barangay Paguiruan, Floridablanca</h1>
            <p class="mt-1 text-blue-100/90">Official Council Members</p>
        </div>
    </section>

    {{-- Barangay Chairman --}}
    <section class="mb-10">
        <h2 class="text-xs uppercase tracking-wider text-gray-500 mb-6">Barangay Chairman</h2>

        @if ($chairman)
            <div class="max-w-md mx-auto text-center">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6 hover:shadow-md transition-all duration-200">
                    <div class="relative mx-auto mb-4">
                        <img src="{{ $chairman->photoUrl() }}"
                             alt="{{ $chairman->user->full_name }}"
                             class="mx-auto h-48 w-48 rounded-full border-2 border-gray-200 object-cover bg-gray-100 hover:border-blue-400 transition-all duration-200">
                    </div>
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 ring-1 ring-inset ring-blue-600/20">
                        Punong Barangay
                    </span>
                    <h3 class="mt-3 text-xl font-semibold tracking-tight text-gray-800">{{ $chairman->user->full_name }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        @if ($chairman->term_start || $chairman->term_end)
                            Term: {{ optional($chairman->term_start)->format('M Y') }}@if ($chairman->term_end) &mdash; {{ $chairman->term_end->format('M Y') }} @endif
                        @else
                            <span class="text-gray-500">Term not specified</span>
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="max-w-md mx-auto rounded-2xl border border-gray-200 bg-white shadow-sm p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/>
                </svg>
                <p class="mt-3 text-sm text-gray-600">No Barangay Chairman currently assigned.</p>
            </div>
        @endif
    </section>

    {{-- Secretary & Treasurer --}}
    @if ($secretary || $treasurer || $investigator)
        <section class="mb-10">
            <h2 class="text-xs uppercase tracking-wider text-gray-500 mb-6">Executive Officers</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
                @foreach ([$secretary, $treasurer, $investigator] as $officer)
                    @if ($officer)
                        @php
                            $roleLabel = str_starts_with($officer->position->name, 'Barangay ')
                                ? substr($officer->position->name, 9)
                                : $officer->position->name;
                        @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6 text-center hover:shadow-md transition-all duration-200">
                            <img src="{{ $officer->photoUrl() }}"
                                 alt="{{ $officer->user->full_name }}"
                                 class="mx-auto h-32 w-32 rounded-full border-2 border-gray-200 object-cover bg-gray-100 hover:border-blue-400 transition-all duration-200">
                            <span class="mt-4 inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">
                                {{ $roleLabel }}
                            </span>
                            <h3 class="mt-2 text-base font-semibold tracking-tight text-gray-800">{{ $officer->user->full_name }}</h3>
                            <p class="mt-0.5 text-xs text-gray-600">
                                @if ($officer->term_start || $officer->term_end)
                                    {{ optional($officer->term_start)->format('M Y') }}@if ($officer->term_end) &ndash; {{ $officer->term_end->format('M Y') }}@endif
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- Sangguniang Barangay Members --}}
    <section class="mb-12">
        <h2 class="text-xs uppercase tracking-wider text-gray-500 mb-6">Sangguniang Barangay Members</h2>

        @if ($kagawads->isEmpty())
            <div class="max-w-md mx-auto rounded-2xl border border-gray-200 bg-white shadow-sm p-8 text-center">
                <p class="text-sm text-gray-600">No Kagawad members currently assigned.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($kagawads as $kagawad)
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 text-center hover:shadow-md transition-all duration-200">
                        <img src="{{ $kagawad->photoUrl() }}"
                             alt="{{ $kagawad->user->full_name }}"
                             class="mx-auto h-28 w-28 rounded-full border-2 border-gray-200 object-cover bg-gray-100 hover:border-blue-400 transition-all duration-200">
                        <span class="mt-3 inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">
                            Kagawad
                        </span>
                        <h3 class="mt-2 text-sm font-semibold tracking-tight text-gray-800">{{ $kagawad->user->full_name }}</h3>
                        <p class="mt-0.5 text-xs text-gray-600">
                            @if ($kagawad->term_start || $kagawad->term_end)
                                {{ optional($kagawad->term_start)->format('M Y') }}@if ($kagawad->term_end) &ndash; {{ $kagawad->term_end->format('M Y') }}@endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
