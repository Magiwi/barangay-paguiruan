@extends('layouts.resident')

@section('title', 'Complaint #' . $issue->id . ' - e-Governance System')

@section('content')
<section class="py-8">
    <div class="max-w-4xl mx-auto px-4 md:px-6">
        <a href="{{ route('resident.issues.index') }}" class="ui-focus-ring mb-6 inline-flex items-center gap-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
            <span>&larr;</span> Back to my complaints
        </a>

        <div class="ui-surface-card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                <h1 class="font-semibold tracking-tight text-gray-800 text-xl">Complaint #{{ $issue->id }}</h1>
                <div>
                    @if ($issue->status === 'pending')
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                    @elseif ($issue->status === 'in_progress')
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800">In Progress</span>
                    @elseif ($issue->status === 'resolved')
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">Resolved</span>
                    @else
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-gray-200 text-gray-700">Closed</span>
                    @endif
                </div>
            </div>

            <div class="p-6 space-y-6">
                {{-- Subject + Category --}}
                <div>
                    <h2 class="font-semibold text-gray-800 text-lg">{{ $issue->subject }}</h2>
                    @if ($issue->category)
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 mt-1">{{ $issue->category }}</span>
                    @endif
                </div>

                {{-- Description --}}
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Description</p>
                    <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $issue->description }}</div>
                </div>

                {{-- Location --}}
                @if ($issue->location)
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Location</p>
                        <p class="text-sm text-gray-900 flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $issue->location }}
                        </p>
                    </div>
                @endif

                {{-- Attachment --}}
                @if ($issue->attachment_path)
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">Attachment</p>
                        @php $ext = strtolower(pathinfo($issue->attachment_path, PATHINFO_EXTENSION)); $isImage = in_array($ext, ['jpg', 'jpeg', 'png']); @endphp
                        @if ($isImage)
                            <img src="{{ asset('storage/' . $issue->attachment_path) }}" alt="Complaint attachment" class="max-w-md rounded-lg border border-gray-200 shadow-sm">
                        @endif
                        <a href="{{ asset('storage/' . $issue->attachment_path) }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            View attachment
                        </a>
                    </div>
                @endif

                {{-- Resolution transparency (when resolved) --}}
                @if ($issue->status === 'resolved' || $issue->status === 'closed')
                    <div class="rounded-xl border border-green-200 bg-green-50 p-5">
                        <h3 class="font-semibold text-green-900 mb-3 flex items-center gap-2">
                            <span>Resolved</span>
                        </h3>
                        <div class="space-y-4 text-sm">
                            @if ($issue->resolution_notes)
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase mb-1">Resolution Notes</p>
                                    <p class="text-gray-700 whitespace-pre-line">{{ $issue->resolution_notes }}</p>
                                </div>
                            @elseif ($issue->remarks)
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase mb-1">Resolution Summary</p>
                                    <p class="text-gray-700 whitespace-pre-line">{{ $issue->remarks }}</p>
                                </div>
                            @endif
                            @if ($issue->action_taken)
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase mb-1">Action Taken</p>
                                    <p class="text-gray-900">{{ ucfirst($issue->action_taken) }}</p>
                                </div>
                            @endif
                            @if ($issue->after_photo_path)
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase mb-1">After Photo</p>
                                    @php $aext = strtolower(pathinfo($issue->after_photo_path, PATHINFO_EXTENSION)); $aisImg = in_array($aext, ['jpg', 'jpeg', 'png']); @endphp
                                    @if ($aisImg)
                                        <img src="{{ asset('storage/' . $issue->after_photo_path) }}" alt="After resolution" class="mt-1 max-w-sm rounded-lg border border-green-200 shadow-sm">
                                    @endif
                                    <a href="{{ asset('storage/' . $issue->after_photo_path) }}" target="_blank" class="mt-2 inline-block text-sm text-green-700 hover:text-green-800 font-medium">View / Download</a>
                                </div>
                            @endif
                            @if ($issue->resolvedBy)
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase mb-1">Resolved By</p>
                                    <p class="text-gray-900">{{ $issue->resolvedBy->first_name }} {{ $issue->resolvedBy->last_name }}</p>
                                </div>
                            @endif
                            @if ($issue->resolved_at)
                                <div>
                                    <p class="text-xs font-medium text-green-700 uppercase mb-1">Date Resolved</p>
                                    <p class="text-gray-900">{{ $issue->resolved_at->format('M d, Y h:i A') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Meta --}}
                <div class="pt-4 border-t border-gray-200 text-xs text-gray-500">
                    <p>Filed {{ $issue->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
