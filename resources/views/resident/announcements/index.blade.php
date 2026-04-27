@extends('layouts.resident')

@section('title', 'Announcements - e-Governance System')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
        <h1 class="text-xl font-bold text-gray-900 mb-2">Announcements</h1>
        <p class="text-sm text-gray-600 mb-6">Latest updates from the barangay.</p>

        {{-- Label Filter --}}
        @if ($labels->isNotEmpty())
            <div class="mb-6 flex flex-wrap items-center gap-2">
                <a href="{{ route('resident.announcements.index') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium transition {{ !request('label') ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                    All
                </a>
                @foreach ($labels as $label)
                    <a href="{{ route('resident.announcements.index', ['label' => $label->slug]) }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium transition {{ request('label') === $label->slug ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                        {{ $label->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($announcements->isEmpty())
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-12 text-center text-gray-600">
                <p>No announcements at the moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($announcements as $announcement)
                    @php $isEmergency = $announcement->labels->contains('slug', 'emergency'); @endphp
                    <article class="relative flex flex-col overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-shadow {{ $isEmergency ? 'bg-red-50 ring-2 ring-red-400' : 'bg-white ring-1 ring-gray-200' }}">
                        {{-- Emergency ribbon --}}
                        @if ($isEmergency)
                            <div class="absolute top-3 right-0 z-10">
                                <div class="flex items-center gap-1 bg-red-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-l-full shadow-md">
                                    <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    EMERGENCY NOTICE
                                </div>
                            </div>
                        @endif
                        @if ($announcement->image)
                            <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="h-40 w-full object-cover object-center">
                        @else
                            <div class="h-2 {{ $isEmergency ? 'bg-gradient-to-r from-red-500 to-red-600' : 'bg-gradient-to-r from-blue-600 to-blue-700' }}" aria-hidden="true"></div>
                        @endif
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3">
                                @if ($isEmergency)
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Emergency
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800">Announcement</span>
                                @endif
                            </div>
                            <h2 class="font-semibold line-clamp-2 {{ $isEmergency ? 'text-red-900' : 'text-gray-900' }}">{{ $announcement->title }}</h2>
                            @if ($announcement->labels->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($announcement->labels as $label)
                                        @if ($label->slug !== 'emergency')
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $label->color }}">{{ $label->name }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            <p class="mt-2 text-sm line-clamp-3 flex-1 {{ $isEmergency ? 'text-red-800/80' : 'text-gray-600' }}">
                                {{ Str::limit(strip_tags($announcement->content), 120) }}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-xs {{ $isEmergency ? 'text-red-500' : 'text-gray-500' }}">
                                    Posted {{ $announcement->published_at ? $announcement->published_at->format('M d, Y') : $announcement->created_at->format('M d, Y') }}
                                </p>
                                <a href="{{ route('resident.announcements.show', $announcement) }}" class="inline-flex items-center gap-1 text-xs font-semibold {{ $isEmergency ? 'text-red-700 hover:text-red-800' : 'text-blue-600 hover:text-blue-700' }} transition">
                                    Read More
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $announcements->withQueryString()->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
