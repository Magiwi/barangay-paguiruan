@extends($layout ?? 'layouts.admin')

@section('title', 'Household Timeline - e-Governance')

@section('content')
@php
    $rp = $routePrefix ?? 'admin';
    $actionLabels = [
        'family_member_added' => 'Member Added',
        'family_member_updated' => 'Member Updated',
        'family_member_removed' => 'Member Removed',
        'family_member_linked_existing' => 'Existing Resident Linked',
    ];
    $selectedHeadId = request('head_id');
    $selectedHeadName = $selectedHeadId ? ($heads->firstWhere('id', $selectedHeadId)?->full_name ?? '') : '';
@endphp
<section class="px-4 py-8 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.report-topbar
            title="Household Timeline"
            subtitle="Audit trail of add, update, remove, and link actions for family members."
            :back-url="route($rp . '.reports.index')"
            class="no-print"
        >
            <x-slot:actions>
                <x-ui.export-toolbar
                    :pdf-url="route($rp . '.reports.households.timeline.export.pdf', request()->query())"
                    filter-label="Exports include current filters"
                    :filter-value="'Action: ' . (request('action') ?: 'All') . ' | Range: ' . (request('date_from') ?: 'Any') . ' to ' . (request('date_to') ?: 'Any')"
                />
            </x-slot:actions>
        </x-ui.report-topbar>

        <x-ui.report-filter-bar
            :action="route($rp . '.reports.households.timeline')"
            :reset-url="route($rp . '.reports.households.timeline')"
            fields-class="grid grid-cols-1 gap-4 md:grid-cols-5"
            submit-label="Apply Filters"
        >
                <div class="relative" id="head-typeahead-wrap">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Head of Family</label>
                    <input type="hidden" name="head_id" id="head_id_input" value="{{ $selectedHeadId }}">
                    <input type="text"
                           id="head_typeahead_input"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Type to search or All Heads"
                           autocomplete="off"
                           value="{{ e($selectedHeadName) }}">
                    <div id="head_typeahead_dropdown" class="absolute z-10 mt-1 hidden w-full rounded-lg border border-gray-200 bg-white py-1 shadow-lg" style="max-height: 220px; overflow-y: auto;"></div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Action</label>
                    <select name="action" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $actionLabels[$action] ?? $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Description or performer"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
        </x-ui.report-filter-bar>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <x-ui.stats-card
                label="Added"
                :value="number_format($summary['added'])"
                wrapper-class="rounded-2xl border border-green-100 bg-white p-4 shadow-sm"
                value-class="mt-1 text-2xl font-bold text-green-700"
            />
            <x-ui.stats-card
                label="Updated"
                :value="number_format($summary['updated'])"
                wrapper-class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm"
                value-class="mt-1 text-2xl font-bold text-blue-700"
            />
            <x-ui.stats-card
                label="Removed"
                :value="number_format($summary['removed'])"
                wrapper-class="rounded-2xl border border-red-100 bg-white p-4 shadow-sm"
                value-class="mt-1 text-2xl font-bold text-red-700"
            />
            <x-ui.stats-card
                label="Linked Existing"
                :value="number_format($summary['linked'])"
                wrapper-class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm"
                value-class="mt-1 text-2xl font-bold text-amber-700"
            />
        </div>

        <x-ui.report-table-card
            title="Timeline Entries"
            body-class="divide-y divide-gray-100"
        >
                @forelse ($logs as $log)
                    @php
                        $badgeClass = match($log->action) {
                            'family_member_added' => 'bg-green-100 text-green-700',
                            'family_member_updated' => 'bg-blue-100 text-blue-700',
                            'family_member_removed' => 'bg-red-100 text-red-700',
                            'family_member_linked_existing' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2">
                                <x-ui.status-badge class="{{ $badgeClass }}">
                                    {{ $actionLabels[$log->action] ?? $log->action }}
                                </x-ui.status-badge>
                                <span class="text-xs text-gray-500">
                                    {{ optional($log->created_at)->format('M d, Y h:i A') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500">
                                By: <span class="font-medium text-gray-700">{{ $log->user?->full_name ?? 'System' }}</span>
                            </p>
                        </div>
                        <p class="mt-2 text-sm text-gray-700">{{ $log->description ?: 'No description provided.' }}</p>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-500">
                        <x-ui.empty-state message="No household timeline records found for the selected filters." text-class="text-sm text-gray-500" />
                    </div>
                @endforelse
            <x-slot:footer>
                {{ $logs->links() }}
            </x-slot:footer>
        </x-ui.report-table-card>
    </div>
</section>

@push('scripts')
<script>
(function () {
    const heads = @json($heads->map(fn ($h) => ['id' => $h->id, 'name' => $h->full_name])->values());
    const wrap = document.getElementById('head-typeahead-wrap');
    const hiddenInput = document.getElementById('head_id_input');
    const textInput = document.getElementById('head_typeahead_input');
    const dropdown = document.getElementById('head_typeahead_dropdown');

    if (!wrap || !hiddenInput || !textInput || !dropdown) return;

    let highlightedIndex = -1;

    function normalize(s) {
        return (s || '').toLowerCase().trim().replace(/\s+/g, ' ');
    }

    function filterHeads(q) {
        const nq = normalize(q);
        if (!nq) return heads;
        return heads.filter(function (h) {
            return normalize(h.name).indexOf(nq) !== -1;
        });
    }

    function render(list) {
        dropdown.innerHTML = '';
        const allHeadsBtn = document.createElement('button');
        allHeadsBtn.type = 'button';
        allHeadsBtn.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-gray-100' + (hiddenInput.value === '' ? ' bg-blue-50 text-blue-700' : '');
        allHeadsBtn.textContent = 'All Heads';
        allHeadsBtn.addEventListener('click', function () {
            pick('', 'All Heads');
            dropdown.classList.add('hidden');
        });
        dropdown.appendChild(allHeadsBtn);

        list.forEach(function (head, i) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-gray-100';
            btn.textContent = head.name;
            btn.dataset.index = i;
            btn.addEventListener('click', function () {
                pick(head.id, head.name);
                dropdown.classList.add('hidden');
            });
            dropdown.appendChild(btn);
        });

        if (list.length === 0 && textInput.value.trim()) {
            const empty = document.createElement('p');
            empty.className = 'px-3 py-2 text-sm text-gray-500';
            empty.textContent = 'No heads match.';
            dropdown.appendChild(empty);
        }
        highlightedIndex = -1;
    }

    function pick(id, name) {
        hiddenInput.value = id || '';
        textInput.value = name || '';
    }

    function openDropdown() {
        const list = filterHeads(textInput.value);
        render(list);
        dropdown.classList.remove('hidden');
    }

    textInput.addEventListener('focus', openDropdown);
    textInput.addEventListener('input', openDropdown);
    textInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
            return;
        }
        const items = dropdown.querySelectorAll('button');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = Math.min(highlightedIndex + 1, items.length - 1);
            items[highlightedIndex] && items[highlightedIndex].scrollIntoView({ block: 'nearest' });
            return;
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = Math.max(highlightedIndex - 1, -1);
            if (highlightedIndex >= 0 && items[highlightedIndex]) items[highlightedIndex].scrollIntoView({ block: 'nearest' });
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && items[highlightedIndex]) {
                const btn = items[highlightedIndex];
                if (highlightedIndex === 0) {
                    pick('', 'All Heads');
                } else {
                    const head = filterHeads(textInput.value)[highlightedIndex - 1];
                    if (head) pick(head.id, head.name);
                }
                dropdown.classList.add('hidden');
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) dropdown.classList.add('hidden');
    });
})();
</script>
@endpush
@endsection
