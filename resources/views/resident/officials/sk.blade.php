@extends('layouts.resident')

@section('title', 'SK Officials - e-Governance System')

@section('content')
<div class="bg-gradient-to-b from-emerald-700 to-emerald-800 px-4 py-12 sm:px-6 lg:px-8 text-center">
    <div class="mx-auto max-w-4xl">
        <p class="text-sm font-semibold uppercase tracking-widest text-emerald-300">Republic of the Philippines</p>
        <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Barangay Paguiruan, Floridablanca</h1>
        <p class="mt-1 text-sm text-emerald-200">Sangguniang Kabataan Officials</p>
    </div>
</div>

{{-- SK Chairman --}}
<section class="px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <h2 class="text-center text-xs font-semibold uppercase tracking-widest text-gray-400 mb-8">SK Chairman</h2>

        @if ($skChairman)
            <div class="mx-auto max-w-md text-center">
                <img src="{{ $skChairman->photoUrl() }}"
                     alt="{{ $skChairman->user->full_name }}"
                     class="mx-auto h-48 w-48 rounded-full border-4 border-emerald-500 object-cover shadow-lg bg-gray-100">
                <span class="mt-5 inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
                    SK Chairman
                </span>
                <h3 class="mt-3 text-xl font-bold text-gray-900">{{ $skChairman->user->full_name }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Term: {{ $skChairman->term_start->format('M Y') }}
                    @if ($skChairman->term_end) &mdash; {{ $skChairman->term_end->format('M Y') }} @endif
                </p>
            </div>
        @else
            <div class="mx-auto max-w-md rounded-xl bg-gray-50 p-8 text-center ring-1 ring-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/>
                </svg>
                <p class="mt-3 text-sm text-gray-500">No SK Chairman currently assigned.</p>
            </div>
        @endif
    </div>
</section>

{{-- SK Secretary & Treasurer --}}
@if ($skSecretary || $skTreasurer)
    <section class="px-4 pb-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            <h2 class="text-center text-xs font-semibold uppercase tracking-widest text-gray-400 mb-8">SK Executive Officers</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-2xl mx-auto">
                @foreach ([$skSecretary, $skTreasurer] as $officer)
                    @if ($officer)
                        <div class="group rounded-xl bg-white p-6 text-center shadow-sm ring-1 ring-gray-200/60 transition hover:shadow-md">
                            <img src="{{ $officer->photoUrl() }}"
                                 alt="{{ $officer->user->full_name }}"
                                 class="mx-auto h-32 w-32 rounded-full border-2 border-gray-200 object-cover bg-gray-100 transition group-hover:border-emerald-400">
                            <span class="mt-4 inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-[11px] font-semibold text-teal-700">
                                {{ $officer->position->name }}
                            </span>
                            <h3 class="mt-2 text-base font-semibold text-gray-900">{{ $officer->user->full_name }}</h3>
                            <p class="mt-0.5 text-xs text-gray-400">
                                {{ $officer->term_start->format('M Y') }}@if ($officer->term_end) &ndash; {{ $officer->term_end->format('M Y') }}@endif
                            </p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- SK Kagawad Members --}}
<section class="px-4 pb-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <h2 class="text-center text-xs font-semibold uppercase tracking-widest text-gray-400 mb-8">SK Kagawad Members</h2>

        @if ($skKagawads->isEmpty())
            <div class="mx-auto max-w-md rounded-xl bg-gray-50 p-8 text-center ring-1 ring-gray-200">
                <p class="text-sm text-gray-500">No SK Kagawad members currently assigned.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($skKagawads as $kagawad)
                    <div class="group rounded-xl bg-white p-5 text-center shadow-sm ring-1 ring-gray-200/60 transition hover:shadow-md hover:-translate-y-0.5">
                        <img src="{{ $kagawad->photoUrl() }}"
                             alt="{{ $kagawad->user->full_name }}"
                             class="mx-auto h-28 w-28 rounded-full border-2 border-gray-200 object-cover bg-gray-100 transition group-hover:border-emerald-400">
                        <span class="mt-3 inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                            SK Kagawad
                        </span>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ $kagawad->user->full_name }}</h3>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ $kagawad->term_start->format('M Y') }}@if ($kagawad->term_end) &ndash; {{ $kagawad->term_end->format('M Y') }}@endif
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
