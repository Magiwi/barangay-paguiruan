@extends('layouts.resident')

@section('title', 'SK Officials - e-Governance System')

@section('content')
<div class="max-w-5xl mx-auto px-4 md:px-6 py-8">
    <section class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 via-blue-700 to-green-600 px-6 py-8 shadow-lg ring-1 ring-blue-900/20">
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-rule=&quot;evenodd&quot;%3E%3Cpath d=&quot;M0 40L40 0H20L0 20M40 40V20L20 40&quot;/%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative">
            <p class="text-xs uppercase tracking-wider text-blue-100">Republic of the Philippines</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Barangay Paguiruan, Floridablanca</h1>
            <p class="mt-1 text-blue-100/90">Sangguniang Kabataan Officials</p>
        </div>
    </section>

    {{-- SK Chairman --}}
    <section class="mb-10">
        <h2 class="mb-6 text-xs uppercase tracking-wider text-gray-500">SK Chairman</h2>

        @if ($skChairman)
            <div class="mx-auto max-w-md text-center">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="relative mx-auto mb-4">
                        <img src="{{ $skChairman->photoUrl() }}"
                             alt="{{ $skChairman->user->full_name }}"
                             class="mx-auto h-48 w-48 rounded-full border-2 border-gray-200 bg-gray-100 object-cover transition-all duration-200 hover:border-blue-400">
                    </div>
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 ring-1 ring-inset ring-blue-600/20">
                        SK Chairman
                    </span>
                    <h3 class="mt-3 text-xl font-semibold tracking-tight text-gray-800">{{ $skChairman->user->full_name }}</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        @if ($skChairman->term_start || $skChairman->term_end)
                            Term: {{ optional($skChairman->term_start)->format('M Y') }}@if ($skChairman->term_end) &mdash; {{ $skChairman->term_end->format('M Y') }} @endif
                        @else
                            <span class="text-gray-500">Term not specified</span>
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/>
                </svg>
                <p class="mt-3 text-sm text-gray-600">No SK Chairman currently assigned.</p>
            </div>
        @endif
    </section>

    {{-- SK Kagawad Members --}}
    <section class="mb-12">
        <h2 class="mb-6 text-xs uppercase tracking-wider text-gray-500">SK Kagawad Members</h2>

        @if ($skKagawads->isEmpty())
            <div class="mx-auto max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                <p class="text-sm text-gray-600">No SK Kagawad members currently assigned.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($skKagawads as $kagawad)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-sm transition-all duration-200 hover:shadow-md">
                        <img src="{{ $kagawad->photoUrl() }}"
                            alt="{{ $kagawad->user->full_name }}"
                            class="mx-auto h-28 w-28 rounded-full border-2 border-gray-200 object-cover bg-gray-100 transition-all duration-200 hover:border-blue-400">
                        <span class="mt-3 inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">
                            SK Kagawad
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
