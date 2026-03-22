@extends('layouts.admin')

@section('title', 'System Backups - e-Governance Admin Panel')
@section('page_title', 'System Backups')

@section('content')
<section class="px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Backup & Recovery</h1>
                <p class="text-sm text-gray-500">Create, download, and manage system backups.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.backups.run') }}">
                    @csrf
                    <button type="submit" onclick="this.disabled=true; this.innerHTML='<svg class=\'animate-spin h-4 w-4 mr-1.5 inline\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\' fill=\'none\'/><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z\'/></svg>Running...'; this.form.submit();"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>
                        </svg>
                        DB Backup
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.backups.run-full') }}">
                    @csrf
                    <button type="submit" onclick="this.disabled=true; this.innerHTML='<svg class=\'animate-spin h-4 w-4 mr-1.5 inline\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\' fill=\'none\'/><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z\'/></svg>Running...'; this.form.submit();"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        Full Backup
                    </button>
                </form>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Info card --}}
        <div class="rounded-xl bg-blue-50 ring-1 ring-blue-200/60 p-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium">Backup Information</p>
                    <ul class="mt-1 space-y-0.5 text-blue-700">
                        <li><strong>DB Backup</strong> — Database only (fast, recommended for daily use)</li>
                        <li><strong>Full Backup</strong> — Database + uploaded files (larger, for full recovery)</li>
                        <li>Automatic DB backups run daily at 2:00 AM</li>
                        <li>Backups older than 7 days are automatically cleaned up</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Backups table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-900">Available Backups</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ count($files) }} backup{{ count($files) !== 1 ? 's' : '' }} found</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Size</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($files as $file)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                        </svg>
                                        <span class="font-medium text-gray-900 font-mono text-xs">{{ $file['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    @php
                                        $bytes = $file['size'];
                                        if ($bytes >= 1048576) {
                                            $sizeStr = number_format($bytes / 1048576, 2) . ' MB';
                                        } elseif ($bytes >= 1024) {
                                            $sizeStr = number_format($bytes / 1024, 1) . ' KB';
                                        } else {
                                            $sizeStr = $bytes . ' B';
                                        }
                                    @endphp
                                    {{ $sizeStr }}
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    @php $date = \Carbon\Carbon::createFromTimestamp($file['last_modified']); @endphp
                                    {{ $date->format('M d, Y') }}
                                    <span class="block text-xs text-gray-400">{{ $date->format('h:i:s A') }} ({{ $date->diffForHumans() }})</span>
                                </td>
                                <td class="px-5 py-3 text-sm whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.backups.download', $file['name']) }}"
                                           class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                            </svg>
                                            Download
                                        </a>
                                        <form method="POST" action="{{ route('admin.backups.destroy', $file['name']) }}"
                                              onsubmit="return confirm('Delete this backup permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                        </svg>
                                        <p class="text-sm font-medium text-gray-500">No backups yet</p>
                                        <p class="text-xs text-gray-400">Click "DB Backup" to create your first backup.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>
@endsection
