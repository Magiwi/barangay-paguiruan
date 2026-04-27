@extends('layouts.admin')

@section('title', 'Announcement Labels - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Announcement Labels</h1>
            <p class="text-sm text-gray-600 mt-1">Create and manage labels for categorizing announcements.</p>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </x-ui.alert>
        @endif

        {{-- Add Label Card --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Add New Label</h2>
            </div>
            <form method="POST" action="{{ route('admin.announcement-labels.store') }}" class="px-6 py-4">
                @csrf
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Label Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="e.g. Health, Disaster, Event"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder:text-gray-400"
                            required
                        >
                    </div>
                    <button type="submit" class="ui-btn ui-btn-primary rounded-lg px-5 py-2 shadow-sm">
                        Add Label
                    </button>
                </div>
            </form>
        </div>

        {{-- Labels Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-800">All Labels</h2>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                    {{ $labels->count() }} {{ Str::plural('label', $labels->count()) }}
                </span>
            </div>

            @if ($labels->isEmpty())
                <div class="px-6 py-12 text-center text-gray-500">
                    <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <p class="text-sm">No labels created yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Announcements</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($labels as $label)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $label->color }}">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ $label->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 font-mono">{{ $label->slug }}</code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if ($label->announcements_count > 0)
                                            <span class="inline-flex items-center rounded-full bg-green-50 border border-green-200 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                {{ $label->announcements_count }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if ($label->announcements_count > 0)
                                            <span class="text-xs text-gray-400 italic" title="Cannot delete — label is in use">In use</span>
                                        @else
                                            <form method="POST" action="{{ route('admin.announcement-labels.destroy', $label) }}" class="inline" onsubmit="return confirm('Delete the label \'{{ $label->name }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 transition">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Back link --}}
        <div class="mt-6">
            <a href="{{ route('admin.announcements.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition">
                &larr; Back to Announcements
            </a>
        </div>
    </div>
</section>
@endsection
