@extends('layouts.resident')

@section('title', 'Request Documents - e-Governance System')

@section('content')
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Request Documents</h1>
            <a href="{{ route('resident.certificates.create') }}" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                New Request
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        <div class="ui-surface-card overflow-hidden">
            @if ($requests->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <svg class="h-14 w-14 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    <p class="mt-3 text-gray-600 font-medium">You have no certificate requests yet.</p>
                    <a href="{{ route('resident.certificates.create') }}" class="ui-focus-ring mt-2 rounded-md text-sm font-medium text-blue-600 hover:text-blue-700">Submit your first request</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Certificate Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Purpose</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Requested</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($requests as $req)
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $req->certificate_type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">{{ Str::limit($req->purpose, 80) }}</td>
                                    <td class="px-6 py-4">
                                        @if ($req->status === 'pending')
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pending</span>
                                        @elseif ($req->status === 'approved')
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Approved</span>
                                        @elseif ($req->status === 'released')
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-700">Released</span>
                                        @else
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium bg-red-100 text-red-600">Rejected</span>
                                        @endif
                                        @if ($req->status === 'released' && $req->released_at)
                                            <p class="text-xs text-gray-500 mt-1">Released: {{ $req->released_at->format('M d, Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $req->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $req->remarks ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
