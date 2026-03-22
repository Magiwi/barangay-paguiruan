@extends('layouts.admin')

@section('title', 'Edit Official - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Edit Official</h1>
                <p class="text-sm text-gray-600">{{ $official->user->full_name }} — {{ $official->position->name }}</p>
            </div>
            <a href="{{ route('admin.officials.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">Back to list</a>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error">
                {{ $errors->first() }}
            </x-ui.alert>
        @endif

        @if ($official->isExpired())
            <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                This official's term has expired. Updating the term end date or deactivating will trigger permission revocation.
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <form method="POST" action="{{ route('admin.officials.update', $official) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Official</label>
                    <p class="px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg border border-gray-200">
                        {{ $official->user->full_name }} ({{ ucfirst($official->user->role) }})
                    </p>
                </div>

                <div>
                    <label for="position_id" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                    <select name="position_id" id="position_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($positions as $position)
                            @php $remaining = $position->remainingSeats($official->user_id); @endphp
                            <option value="{{ $position->id }}"
                                @selected(old('position_id', $official->position_id) == $position->id)
                                @disabled($remaining <= 0 && $official->position_id != $position->id)>
                                {{ $position->name }} ({{ $remaining }}/{{ $position->max_seats }} {{ Str::plural('seat', $remaining) }} left)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="term_start" class="block text-sm font-medium text-gray-700 mb-1">Term Start</label>
                        <input type="date" name="term_start" id="term_start" value="{{ old('term_start', $official->term_start->toDateString()) }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="term_end" class="block text-sm font-medium text-gray-700 mb-1">Term End <span class="text-gray-400">(optional)</span></label>
                        <input type="date" name="term_end" id="term_end" value="{{ old('term_end', optional($official->term_end)->toDateString()) }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <label for="term_template" class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">Quick Term Template</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <select id="term_template" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700">
                            <option value="">Select template...</option>
                            <option value="6m">6 months</option>
                            <option value="1y">1 year</option>
                            <option value="3y">3 years</option>
                        </select>
                        <button type="button" id="apply_term_template" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700 hover:bg-blue-100">
                            Apply
                        </button>
                        <span class="text-xs text-gray-500">Uses selected term start date.</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                    @if ($official->photo)
                        <div class="flex items-center gap-4 mb-3" id="current-photo">
                            <img src="{{ $official->photoUrl() }}" alt="Current photo" class="h-20 w-20 rounded-lg object-cover ring-1 ring-gray-200">
                            <div>
                                <p class="text-sm text-gray-600">Current photo</p>
                                <button type="button" onclick="document.getElementById('current-photo').style.display='none'; document.getElementById('photo_removed').value='1';"
                                        class="mt-1 text-xs font-medium text-red-600 hover:text-red-700">Remove photo</button>
                            </div>
                        </div>
                        <input type="hidden" name="photo_removed" id="photo_removed" value="0">
                    @endif
                    <input type="file" name="photo" id="photo" accept="image/png,image/jpeg"
                           class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-400">PNG or JPG, max 2MB. Upload to replace current photo.</p>
                    @error('photo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.officials.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateSelect = document.getElementById('term_template');
    const applyButton = document.getElementById('apply_term_template');
    const termStartInput = document.getElementById('term_start');
    const termEndInput = document.getElementById('term_end');

    function toDateInputValue(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');

        return `${yyyy}-${mm}-${dd}`;
    }

    function parseOrToday(value) {
        if (!value) {
            return new Date();
        }
        const parsed = new Date(value + 'T00:00:00');
        return Number.isNaN(parsed.getTime()) ? new Date() : parsed;
    }

    applyButton.addEventListener('click', function () {
        const template = templateSelect.value;
        if (!template) {
            return;
        }

        const start = parseOrToday(termStartInput.value);
        const end = new Date(start.getTime());

        if (template === '6m') {
            end.setMonth(end.getMonth() + 6);
        } else if (template === '1y') {
            end.setFullYear(end.getFullYear() + 1);
        } else if (template === '3y') {
            end.setFullYear(end.getFullYear() + 3);
        }

        termStartInput.value = toDateInputValue(start);
        termEndInput.value = toDateInputValue(end);
    });
});
</script>
@endsection
