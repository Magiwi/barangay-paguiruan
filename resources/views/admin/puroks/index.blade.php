@extends('layouts.admin')

@section('title', 'Purok Management - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Purok Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage barangay puroks</p>
            </div>
            <a href="{{ route('admin.puroks.create') }}" class="ui-btn ui-btn-primary inline-flex rounded-lg">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Purok
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Residents</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Streets</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($puroks as $purok)
                            <tr class="{{ ! $purok->is_active ? 'bg-gray-50' : '' }}">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $purok->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $purok->description ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right tabular-nums">
                                    {{ number_format($purok->residents_count) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right tabular-nums">
                                    {{ number_format($purok->streets_count) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($purok->is_active)
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-gray-100 text-gray-600">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('admin.puroks.edit', $purok) }}" class="text-blue-600 hover:text-blue-700 font-medium">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.puroks.toggle-status', $purok) }}" class="inline">
                                            @csrf
                                            @if ($purok->is_active)
                                                <button type="submit" class="text-amber-600 hover:text-amber-700 font-medium">
                                                    Deactivate
                                                </button>
                                            @else
                                                <button type="submit" class="text-green-600 hover:text-green-700 font-medium">
                                                    Activate
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No puroks found. <a href="{{ route('admin.puroks.create') }}" class="text-blue-600 hover:text-blue-700">Add one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($puroks->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $puroks->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
