@extends('layouts.resident')

@section('title', 'My Blotter Requests - e-Governance System')

@section('content')
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">

        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="font-semibold tracking-tight text-gray-800 text-xl">My Blotter Requests</h1>
                <p class="mt-1 text-gray-600 text-sm">Track the status of your blotter record requests.</p>
            </div>
            <a href="{{ route('resident.blotter-requests.create') }}" class="ui-focus-ring inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Request
            </a>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Requests list --}}
        @if ($requests->isEmpty())
            <div class="ui-surface-card p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-3 text-gray-600">No blotter requests yet.</p>
                <p class="mt-1 text-sm text-gray-500">Kapag may naka-link na blotter sa account mo, maaari kang mag-submit ng request para sa kopya. Kung wala pang lumalabas na case, bisitahin ang barangay hall para sa tulong sa linkage o dokumentasyon.</p>
                <a href="{{ route('resident.blotter-requests.create') }}" class="ui-focus-ring mt-4 inline-flex text-sm font-medium text-blue-600 hover:text-blue-800">Mag-request ng blotter record →</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($requests as $req)
                    <article class="ui-surface-card ui-surface-card-hover overflow-hidden">
                        <div class="p-5">
                            {{-- Blotter number + status badge --}}
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="font-mono text-sm font-semibold text-gray-800">{{ $req->blotter->blotter_number ?? '—' }}</span>
                                @if ($req->blotter?->complainant_name)
                                    <span class="text-sm font-medium text-gray-700">{{ $req->blotter->complainant_name }}</span>
                                @endif
                                @if ($req->status === 'pending')
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
                                @elseif ($req->status === 'approved')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Approved</span>
                                @elseif ($req->status === 'rejected')
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-red-100 text-red-600">Rejected</span>
                                @elseif ($req->status === 'released')
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700">Released</span>
                                @endif
                            </div>

                            {{-- Purpose --}}
                            <p class="mt-2 text-gray-600 text-sm leading-relaxed">{{ Str::limit($req->purpose, 150) }}</p>

                            {{-- Date --}}
                            <p class="mt-2 text-xs text-gray-500">Requested {{ $req->created_at->format('M d, Y h:i A') }}</p>

                            <div class="mt-3 flex items-center gap-2 text-[11px] text-gray-500">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 {{ $req->status !== 'pending' ? 'text-gray-700' : '' }}">Pending</span>
                                <span>→</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 {{ in_array($req->status, ['approved', 'released'], true) ? 'text-green-700' : '' }}">Approved</span>
                                <span>→</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 {{ $req->status === 'released' ? 'text-blue-700' : '' }}">Released</span>
                            </div>

                            {{-- Remarks in subtle gray box --}}
                            @if ($req->remarks)
                                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
                                    <span class="text-xs uppercase tracking-wider text-gray-500">Remarks</span>
                                    @if ($req->rejectionReasonLabel())
                                        <p class="mt-0.5 text-xs font-semibold text-red-600">{{ $req->rejectionReasonLabel() }}</p>
                                    @endif
                                    <p class="mt-0.5">{{ $req->remarks }}</p>
                                    @if ($req->processedBy)
                                        <p class="mt-1 text-xs text-gray-500">
                                            By {{ $req->processedBy->first_name }} {{ $req->processedBy->last_name }}
                                            @if ($req->processed_at)
                                                &middot; {{ $req->processed_at->format('M d, Y h:i A') }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @elseif ($req->rejectionReasonLabel())
                                <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                                    <span class="text-xs uppercase tracking-wider text-red-600">Reason</span>
                                    <p class="mt-0.5">{{ $req->rejectionReasonLabel() }}</p>
                                </div>
                            @endif

                            @if ($req->status === 'released')
                                <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                                    Your request is released. Please proceed to the barangay hall to claim your blotter record.
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($requests->hasPages())
                <div class="mt-6">
                    {{ $requests->links() }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
