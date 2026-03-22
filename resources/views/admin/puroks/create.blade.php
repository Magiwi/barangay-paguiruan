@extends('layouts.admin')

@section('title', 'Add Purok - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-xl">
        <div class="mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Add Purok</h1>
            <p class="text-sm text-gray-600 mt-1">Create a new purok for the barangay</p>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error">
                {{ $errors->first() }}
            </x-ui.alert>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <form method="POST" action="{{ route('admin.puroks.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Purok Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="e.g. Purok 1, Purok Mabini"
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3"
                              placeholder="Optional description or notes about this purok"
                              class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="street_ids" class="block text-sm font-medium text-gray-700 mb-1">
                        Link Existing Streets
                    </label>
                    <select name="street_ids[]" id="street_ids" multiple
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                        @foreach (($streets ?? collect()) as $street)
                            <option value="{{ $street->id }}" @selected(collect(old('street_ids', []))->contains($street->id))>{{ $street->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Hold Cmd/Ctrl to select multiple streets.</p>
                </div>

                <div>
                    <label for="new_street_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Add New Street (Optional)
                    </label>
                    <input type="text" name="new_street_name" id="new_street_name" value="{{ old('new_street_name') }}"
                           placeholder="e.g. Don Alfredo"
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.puroks.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                        Create Purok
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
