@extends('layouts.admin')

@section('title', 'Officials Management - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Officials Management</h1>
                <p class="text-sm text-gray-600">Assign officials by slot with separate shared terms for Barangay and SK, plus non-term staff slots.</p>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="error">
                {{ $errors->first() }}
            </x-ui.alert>
        @endif

        <form id="group-term-form" class="hidden">
            <input type="hidden" id="barangay_term_start_global" value="{{ old('term_start', $barangayTerm['term_start']) }}">
            <input type="hidden" id="barangay_term_end_global" value="{{ old('term_end', $barangayTerm['term_end']) }}">
            <input type="hidden" id="sk_term_start_global" value="{{ old('term_start', $skTerm['term_start']) }}">
            <input type="hidden" id="sk_term_end_global" value="{{ old('term_end', $skTerm['term_end']) }}">
        </form>

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
            <p class="font-medium">Term consistency rules</p>
            <p class="mt-1">All Barangay slots must share one term period. All SK slots must share a separate term period. Staff slots do not use terms.</p>
        </div>

        {{-- Barangay Officials --}}
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold tracking-tight text-gray-800">Barangay Officials</h2>
                <p class="text-sm text-gray-600">Shared term for Chairman, Secretary, Treasurer, and Kagawad slots.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <label for="barangay_term_start_input" class="block text-xs font-medium text-gray-600 mb-1">Barangay Term Start</label>
                    <input id="barangay_term_start_input" type="date" value="{{ old('term_start', $barangayTerm['term_start']) }}"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="barangay_term_end_input" class="block text-xs font-medium text-gray-600 mb-1">Barangay Term End</label>
                    <input id="barangay_term_end_input" type="date" value="{{ old('term_end', $barangayTerm['term_end']) }}"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($barangayPositions as $position)
                    @foreach (($slotsByPosition[$position->id] ?? []) as $slot)
                        @php $official = $slot['official']; @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    {{ strtoupper($position->name) }}@if($position->max_seats > 1) #{{ $slot['slot_index'] }}@endif
                                </h3>
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $official ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $official ? 'Assigned' : 'Unassigned' }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3">
                                <img src="{{ $official ? $official->photoUrl() : 'data:image/svg+xml,' . rawurlencode('<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%239ca3af\'><path d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z\'/></svg>') }}"
                                     class="h-12 w-12 rounded-full border border-gray-200 object-cover bg-white" alt="Official">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $official?->user?->full_name ?? 'NO RESIDENT ASSIGNED' }}</p>
                                    <p class="text-xs text-gray-500 truncate">
                                        @if (! $official)
                                            Assign a staff/admin user to this slot.
                                        @elseif ($official->term_start || $official->term_end)
                                            {{ optional($official->term_start)->format('M d, Y') }} - {{ optional($official->term_end)->format('M d, Y') }}
                                        @else
                                            No term dates set
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.officials.assign-slot') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                                @csrf
                                <input type="hidden" name="position_id" value="{{ $position->id }}">
                                <input type="hidden" name="slot_index" value="{{ $slot['slot_index'] }}">
                                <input type="hidden" name="official_id" value="{{ $official?->id }}">
                                <input type="hidden" name="term_start" class="js-term-start" data-group="barangay" value="{{ old('term_start', $barangayTerm['term_start']) }}">
                                <input type="hidden" name="term_end" class="js-term-end" data-group="barangay" value="{{ old('term_end', $barangayTerm['term_end']) }}">

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Assign / Update Resident</label>
                                    <select name="user_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select user</option>
                                        @foreach ($eligibleUsers as $user)
                                            <option value="{{ $user->id }}" @selected(old('user_id', $official?->user_id) == $user->id)>
                                                {{ $user->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Official Photo <span class="text-gray-400">(optional)</span></label>
                                    <input type="file" name="photo" accept=".png,.jpg,.jpeg"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-700 file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-1 text-[11px] text-gray-500">PNG/JPG up to 2MB.</p>
                                </div>

                                @if ($official && $official->photo)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="photo_removed" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="text-xs text-red-600">Remove current photo</span>
                                    </label>
                                @endif

                                <div class="flex items-center justify-start gap-2">
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition">
                                        Save Assignment
                                    </button>
                                </div>
                            </form>
                            @if ($official)
                                <form method="POST" action="{{ route('admin.officials.toggleActive', $official) }}" class="mt-2" onsubmit="return confirm('Deactivate this slot assignment?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">
                                        Deactivate
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- SK Officials --}}
        <div class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold tracking-tight text-gray-800">SK Officials</h2>
                <p class="text-sm text-gray-600">SK uses a separate shared term from Barangay officials.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div>
                    <label for="sk_term_start_input" class="block text-xs font-medium text-gray-600 mb-1">SK Term Start</label>
                    <input id="sk_term_start_input" type="date" value="{{ old('term_start', $skTerm['term_start']) }}"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="sk_term_end_input" class="block text-xs font-medium text-gray-600 mb-1">SK Term End</label>
                    <input id="sk_term_end_input" type="date" value="{{ old('term_end', $skTerm['term_end']) }}"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($skPositions as $position)
                    @foreach (($slotsByPosition[$position->id] ?? []) as $slot)
                        @php $official = $slot['official']; @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    {{ strtoupper($position->name) }}@if($position->max_seats > 1) #{{ $slot['slot_index'] }}@endif
                                </h3>
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $official ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $official ? 'Assigned' : 'Unassigned' }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50 p-3">
                                <img src="{{ $official ? $official->photoUrl() : 'data:image/svg+xml,' . rawurlencode('<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%239ca3af\'><path d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z\'/></svg>') }}"
                                     class="h-12 w-12 rounded-full border border-gray-200 object-cover bg-white" alt="Official">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $official?->user?->full_name ?? 'NO RESIDENT ASSIGNED' }}</p>
                                    <p class="text-xs text-gray-500 truncate">
                                        @if (! $official)
                                            Assign a staff/admin user to this slot.
                                        @elseif ($official->term_start || $official->term_end)
                                            {{ optional($official->term_start)->format('M d, Y') }} - {{ optional($official->term_end)->format('M d, Y') }}
                                        @else
                                            No term dates set
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.officials.assign-slot') }}" enctype="multipart/form-data" class="mt-3 space-y-3">
                                @csrf
                                <input type="hidden" name="position_id" value="{{ $position->id }}">
                                <input type="hidden" name="slot_index" value="{{ $slot['slot_index'] }}">
                                <input type="hidden" name="official_id" value="{{ $official?->id }}">
                                <input type="hidden" name="term_start" class="js-term-start" data-group="sk" value="{{ old('term_start', $skTerm['term_start']) }}">
                                <input type="hidden" name="term_end" class="js-term-end" data-group="sk" value="{{ old('term_end', $skTerm['term_end']) }}">

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Assign / Update Resident</label>
                                    <select name="user_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select user</option>
                                        @foreach ($eligibleUsers as $user)
                                            <option value="{{ $user->id }}" @selected(old('user_id', $official?->user_id) == $user->id)>
                                                {{ $user->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Official Photo <span class="text-gray-400">(optional)</span></label>
                                    <input type="file" name="photo" accept=".png,.jpg,.jpeg"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-700 file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-1 text-[11px] text-gray-500">PNG/JPG up to 2MB.</p>
                                </div>

                                @if ($official && $official->photo)
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="photo_removed" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="text-xs text-red-600">Remove current photo</span>
                                    </label>
                                @endif

                                <div class="flex items-center justify-start gap-2">
                                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition">
                                        Save Assignment
                                    </button>
                                </div>
                            </form>
                            @if ($official)
                                <form method="POST" action="{{ route('admin.officials.toggleActive', $official) }}" class="mt-2" onsubmit="return confirm('Deactivate this slot assignment?')">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">
                                        Deactivate
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const barangayStart = document.getElementById('barangay_term_start_input');
    const barangayEnd = document.getElementById('barangay_term_end_input');
    const skStart = document.getElementById('sk_term_start_input');
    const skEnd = document.getElementById('sk_term_end_input');

    function syncGroupTerms(group) {
        const start = group === 'barangay' ? barangayStart.value : skStart.value;
        const end = group === 'barangay' ? barangayEnd.value : skEnd.value;

        document.querySelectorAll(`.js-term-start[data-group="${group}"]`).forEach((el) => { el.value = start; });
        document.querySelectorAll(`.js-term-end[data-group="${group}"]`).forEach((el) => { el.value = end; });
    }

    [barangayStart, barangayEnd].forEach((el) => {
        el.addEventListener('change', () => syncGroupTerms('barangay'));
    });
    [skStart, skEnd].forEach((el) => {
        el.addEventListener('change', () => syncGroupTerms('sk'));
    });

    syncGroupTerms('barangay');
    syncGroupTerms('sk');
});
</script>
@endsection
