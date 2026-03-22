@extends('layouts.resident')

@section('title', 'My Complaints - e-Governance System')

@section('content')
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-semibold tracking-tight text-gray-800 text-xl">My Complaints</h1>
            <a href="{{ route('resident.issues.create') }}" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                Submit Complaint
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        <div class="space-y-3">
            @if ($issues->isEmpty())
                <div class="ui-surface-card p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="mt-3 text-gray-600">You have not submitted any complaints yet.</p>
                    <a href="{{ route('resident.issues.create') }}" class="ui-focus-ring mt-2 inline-block rounded-md text-sm font-medium text-blue-600 hover:text-blue-700">Submit a complaint</a>
                </div>
            @else
                @foreach ($issues as $issue)
                    <a href="{{ route('resident.issues.show', $issue) }}" class="block">
                    <article class="ui-surface-card ui-surface-card-hover overflow-hidden">
                        <div class="p-5 relative">
                            {{-- Status badge top-right --}}
                            <div class="absolute top-5 right-5">
                                @if ($issue->status === 'pending')
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
                                @elseif ($issue->status === 'in_progress')
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700">In Progress</span>
                                @elseif ($issue->status === 'resolved')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Resolved</span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-600">Closed</span>
                                @endif
                            </div>

                            {{-- Subject + Category --}}
                            <div class="flex flex-wrap items-center gap-2 pr-24">
                                <h2 class="font-semibold tracking-tight text-gray-800">{{ $issue->subject }}</h2>
                                @if ($issue->category)
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">{{ $issue->category }}</span>
                                @endif
                            </div>

                            {{-- Description preview --}}
                            <p class="mt-2 text-gray-600 text-sm line-clamp-2">{{ $issue->description }}</p>

                            {{-- Location with pin icon --}}
                            @if ($issue->location)
                                <p class="mt-2 text-sm text-gray-600 flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $issue->location }}
                                </p>
                            @endif

                            {{-- Date + attachment row --}}
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-xs text-gray-500">
                                <span>{{ $issue->created_at->format('M d, Y h:i A') }}</span>
                                @if ($issue->attachment_path)
                                    <a href="{{ asset('storage/' . $issue->attachment_path) }}" target="_blank" onclick="event.stopPropagation()" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 font-medium">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        View Attachment
                                    </a>
                                @endif
                            </div>

                            {{-- Remarks in subtle box --}}
                            @if ($issue->remarks)
                                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
                                    <span class="text-xs uppercase tracking-wider text-gray-500">Remarks:</span> {{ $issue->remarks }}
                                </div>
                            @endif
                        </div>
                    </article>
                    </a>
                @endforeach
            @endif
        </div>

        @if ($issues->hasPages())
            <div class="mt-6">
                {{ $issues->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
