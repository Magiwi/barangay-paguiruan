@extends('layouts.resident')

@section('title', $announcement->title . ' - e-Governance System')

@php $isEmergency = $announcement->labels->contains('slug', 'emergency'); @endphp

@section('content')
<article class="pb-12">
    {{-- Hero image --}}
    @if ($announcement->image)
        <div class="relative w-full h-56 sm:h-72 md:h-80 lg:h-96 bg-gray-900">
            <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="h-full w-full object-cover object-center {{ $isEmergency ? 'opacity-80' : 'opacity-90' }}">
            @if ($isEmergency)
                <div class="absolute inset-0 bg-gradient-to-t from-red-900/60 to-transparent"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/50 to-transparent"></div>
            @endif
        </div>
    @endif

    {{-- Emergency banner --}}
    @if ($isEmergency)
        <div class="bg-red-600">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex items-center justify-center gap-2 text-white">
                    <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold uppercase tracking-wider">Emergency Notice</span>
                </div>
            </div>
        </div>
    @endif

    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 pt-8">
        {{-- Back link --}}
        <div class="mb-6">
            <a href="{{ route('resident.announcements.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium {{ $isEmergency ? 'text-red-700 hover:text-red-800' : 'text-blue-600 hover:text-blue-700' }} transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Announcements
            </a>
        </div>

        {{-- Main content card --}}
        <div class="overflow-hidden rounded-xl shadow-sm {{ $isEmergency ? 'bg-red-50 ring-2 ring-red-300' : 'bg-white ring-1 ring-gray-200' }}">
            <div class="px-6 py-8 sm:px-8 sm:py-10">
                {{-- Labels --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @if ($isEmergency)
                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Emergency
                        </span>
                    @endif
                    @foreach ($announcement->labels as $label)
                        @if ($label->slug !== 'emergency')
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $label->color }}">{{ $label->name }}</span>
                        @endif
                    @endforeach
                </div>

                {{-- Title --}}
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight {{ $isEmergency ? 'text-red-900' : 'text-gray-900' }}">
                    {{ $announcement->title }}
                </h1>

                {{-- Meta --}}
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm {{ $isEmergency ? 'text-red-700/70' : 'text-gray-500' }}">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $announcement->published_at ? $announcement->published_at->format('F d, Y') : $announcement->created_at->format('F d, Y') }}
                    </div>
                    @if ($announcement->user)
                        <div class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $announcement->user->first_name ?? '' }} {{ $announcement->user->last_name ?? '' }}
                        </div>
                    @endif
                </div>

                {{-- Divider --}}
                <div class="my-6 border-t {{ $isEmergency ? 'border-red-200' : 'border-gray-200' }}"></div>

                {{-- Content --}}
                <div class="prose prose-sm sm:prose max-w-none {{ $isEmergency ? 'prose-red' : 'prose-gray' }}">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>
        </div>

        {{-- Bottom back link --}}
        <div class="mt-8 text-center">
            <a href="{{ route('resident.announcements.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border {{ $isEmergency ? 'border-red-300 text-red-700 hover:bg-red-50' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }} px-5 py-2.5 text-sm font-medium transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Announcements
            </a>
        </div>
    </div>
</article>
@endsection
