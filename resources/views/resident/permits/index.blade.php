@extends('layouts.resident')

@section('title', 'My Permits - e-Governance System')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">My Permits</h1>
            <a href="{{ route('resident.permits.create') }}" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                New Application
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        <div class="ui-surface-card overflow-hidden">
            @if ($permits->isEmpty())
                <div class="p-8 text-center text-gray-600">
                    <p>You have no permit applications yet.</p>
                    <a href="{{ route('resident.permits.create') }}" class="ui-focus-ring mt-2 inline-block rounded-md text-sm font-medium text-blue-600 hover:text-blue-700">Submit your first application</a>
                </div>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($permits as $permit)
                        <li class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $permit->permit_type }}</p>
                                <p class="text-sm text-gray-600 mt-0.5">{{ Str::limit($permit->purpose, 80) }}</p>
                                <p class="text-xs text-gray-500 mt-1">Submitted: {{ $permit->created_at->format('M d, Y H:i') }}</p>
                                @if ($permit->status === 'released' && $permit->released_at)
                                    <p class="text-xs text-blue-600 mt-1">Released: {{ $permit->released_at->format('M d, Y H:i') }}</p>
                                @endif
                                @if ($permit->remarks)
                                    <p class="text-xs text-gray-600 mt-1"><span class="font-medium">Remarks:</span> {{ $permit->remarks }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                @if ($permit->status === 'pending')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                @elseif ($permit->status === 'approved')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-emerald-100 text-emerald-800">Approved</span>
                                    <p class="text-xs text-gray-500 mt-1">Ready for pickup</p>
                                @elseif ($permit->status === 'released')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800">Released</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if ($permits->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $permits->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>
@endsection
