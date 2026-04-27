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
                    @error('committee')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @php $committeeMap = $positions->mapWithKeys(fn ($p) => [$p->id => \App\Support\OfficialCommittees::forPositionName($p->name)]); @endphp
                <div id="committee-field-wrap" class="hidden space-y-1">
                    <label for="committee_select" class="block text-sm font-medium text-gray-700 mb-1">Committee</label>
                    <select id="committee_select" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select committee...</option>
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
                    <button type="submit" class="ui-btn ui-btn-primary rounded-lg">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const officialCommitteeMap = @json($committeeMap);
    const oldCommittee = @json(old('committee', $official->committee));

    const positionSelect = document.getElementById('position_id');
    const committeeWrap = document.getElementById('committee-field-wrap');
    const committeeSelect = document.getElementById('committee_select');

    function syncCommitteeSelect() {
        const posId = positionSelect.value;
        const opts = officialCommitteeMap[posId] || {};
        const keys = Object.keys(opts);
        if (keys.length === 0) {
            committeeWrap.classList.add('hidden');
            committeeSelect.removeAttribute('name');
            committeeSelect.required = false;
            committeeSelect.innerHTML = '<option value="">Select committee...</option>';
            return;
        }
        committeeWrap.classList.remove('hidden');
        committeeSelect.setAttribute('name', 'committee');
        committeeSelect.required = true;
        committeeSelect.innerHTML = '<option value="">Select committee...</option>';
        keys.forEach(function (k) {
            const o = document.createElement('option');
            o.value = k;
            o.textContent = opts[k];
            if (oldCommittee === k) {
                o.selected = true;
            }
            committeeSelect.appendChild(o);
        });
    }

    positionSelect.addEventListener('change', syncCommitteeSelect);
    syncCommitteeSelect();
});
</script>
@endsection
