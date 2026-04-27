@extends('layouts.admin')

@section('title', 'Edit Purok - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-xl">
        <div class="mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Edit Purok</h1>
            <p class="text-sm text-gray-600 mt-1">Update purok information</p>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error">
                {{ $errors->first() }}
            </x-ui.alert>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <form method="POST" action="{{ route('admin.puroks.update', $purok) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Purok Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $purok->name) }}" required
                           placeholder="e.g. Purok 1, Purok Mabini"
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3"
                              placeholder="Optional description or notes about this purok"
                              class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">{{ old('description', $purok->description) }}</textarea>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-700">Linked Streets</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($purok->streets as $street)
                            <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $street->name }}</span>
                        @empty
                            <span class="text-xs text-gray-500">No streets linked yet.</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label for="street_ids" class="block text-sm font-medium text-gray-700 mb-1">
                        Link Existing Streets
                    </label>
                    <select name="street_ids[]" id="street_ids" multiple
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                        @foreach (($streets ?? collect()) as $street)
                            <option value="{{ $street->id }}" @selected(collect(old('street_ids', $purok->streets->pluck('id')->all()))->contains($street->id))>{{ $street->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Selected streets will be linked to this purok.</p>
                </div>

                <div>
                    <label for="new_street_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Add New Street (Optional)
                    </label>
                    <input type="text" name="new_street_name" id="new_street_name" value="{{ old('new_street_name') }}"
                           placeholder="e.g. Don Alfredo"
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                </div>

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Status</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if ($purok->is_active)
                                    This purok is currently <span class="text-green-600 font-medium">active</span>
                                @else
                                    This purok is currently <span class="text-gray-600 font-medium">inactive</span>
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('admin.puroks.toggle-status', $purok) }}" class="inline">
                            @csrf
                            @if ($purok->is_active)
                                <button type="submit" class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 transition">
                                    Deactivate
                                </button>
                            @else
                                <button type="submit" class="rounded-lg border border-green-300 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 transition">
                                    Activate
                                </button>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.puroks.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="ui-btn ui-btn-primary rounded-lg">
                        Update Purok
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
