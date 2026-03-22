@extends($layout ?? 'layouts.admin')

@section('title', 'Household Reports - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.report-topbar
            title="Household Reports & Analytics"
            subtitle="Insights, distribution, and exportable reports for household records."
            :back-url="route($rp . '.reports.index')"
            class="no-print"
            actions-class="flex justify-start md:justify-end"
        >
            <x-slot:actions>
                <x-ui.export-toolbar
                    :pdf-url="route($rp . '.reports.households.export.pdf', request()->query())"
                    :excel-url="route($rp . '.reports.households.export', request()->query())"
                    :csv-url="route($rp . '.reports.households.export.csv', request()->query())"
                    :print-url="route($rp . '.reports.households.export.print', request()->query())"
                    filter-label="Exports include current filters"
                    :filter-value="$allPuroks->firstWhere('id', $purokId)?->name ?? 'All Puroks'"
                />
            </x-slot:actions>
        </x-ui.report-topbar>

        <x-ui.report-filter-bar
            :action="route($rp . '.reports.households')"
            :reset-url="route($rp . '.reports.households')"
            fields-class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-5"
            submit-label="Apply Filters"
        >
                <div>
                    <label for="purok" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Purok</label>
                    <select id="purok" name="purok" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Puroks</option>
                        @foreach ($allPuroks as $p)
                            <option value="{{ $p->id }}" @selected($purokId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <label for="head_q" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Head of Family</label>
                    <input
                        type="text"
                        id="head_q"
                        name="head_q"
                        value="{{ $headQuery ?? '' }}"
                        placeholder="Type head name..."
                        autocomplete="off"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="headSuggestions"
                        aria-activedescendant=""
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <input type="hidden" id="head_id" name="head_id" value="{{ $selectedHeadId ?? '' }}">
                    <p id="headSearchLoading" class="mt-1 hidden text-xs text-gray-500" aria-live="polite">Searching...</p>
                    <div id="headSuggestions" role="listbox" class="absolute z-30 mt-1 hidden w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"></div>
                </div>
                <div>
                    <label for="sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sort By</label>
                    <select id="sort" name="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="head_name" @selected(($sort ?? 'head_name') === 'head_name')>Head Name</option>
                        <option value="purok" @selected(($sort ?? '') === 'purok')>Purok</option>
                        <option value="members" @selected(($sort ?? '') === 'members')>Members</option>
                        <option value="resident_type" @selected(($sort ?? '') === 'resident_type')>Resident Type</option>
                        <option value="status" @selected(($sort ?? '') === 'status')>Status</option>
                        <option value="created_at" @selected(($sort ?? '') === 'created_at')>Date Registered</option>
                    </select>
                </div>
                <div>
                    <label for="direction" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Direction</label>
                    <select id="direction" name="direction" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="asc" @selected(($direction ?? 'asc') === 'asc')>Ascending</option>
                        <option value="desc" @selected(($direction ?? '') === 'desc')>Descending</option>
                    </select>
                </div>
                <div>
                    <label for="resident_type" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Resident Type</label>
                    <select id="resident_type" name="resident_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="permanent" @selected(($residentType ?? '') === 'permanent')>Permanent</option>
                        <option value="non-permanent" @selected(($residentType ?? '') === 'non-permanent')>Non-permanent</option>
                    </select>
                </div>
                <div>
                    <label for="household_status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                    <select id="household_status" name="household_status" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="active" @selected(($statusFilter ?? '') === 'active')>Active</option>
                        <option value="suspended" @selected(($statusFilter ?? '') === 'suspended')>Suspended</option>
                    </select>
                </div>
                <div>
                    <label for="members_min" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Members Min</label>
                    <input id="members_min" name="members_min" type="number" min="1" value="{{ $membersMin ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="members_max" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Members Max</label>
                    <input id="members_max" name="members_max" type="number" min="1" value="{{ $membersMax ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="created_from" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Registered From</label>
                    <input id="created_from" name="created_from" type="date" value="{{ $createdFrom ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="created_to" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Registered To</label>
                    <input id="created_to" name="created_to" type="date" value="{{ $createdTo ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
        </x-ui.report-filter-bar>

        @php
            $activeFilterChips = array_filter([
                $purokId ? 'Purok: ' . ($allPuroks->firstWhere('id', $purokId)?->name ?? 'Unknown') : null,
                !empty($headQuery) ? 'Head: ' . $headQuery : null,
                !empty($residentType) ? 'Type: ' . ucfirst($residentType) : null,
                !empty($statusFilter) ? 'Status: ' . ucfirst($statusFilter) : null,
                !empty($membersMin) ? 'Members >= ' . $membersMin : null,
                !empty($membersMax) ? 'Members <= ' . $membersMax : null,
                !empty($createdFrom) ? 'From: ' . $createdFrom : null,
                !empty($createdTo) ? 'To: ' . $createdTo : null,
            ]);
        @endphp
        @if (!empty($activeFilterChips))
            <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white p-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Active Filters</span>
                @foreach ($activeFilterChips as $chip)
                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">{{ $chip }}</span>
                @endforeach
                <a href="{{ route($rp . '.reports.households') }}" class="ml-auto inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                    Clear All
                </a>
            </div>
        @endif

        <div id="filtered-household-records">
            <x-ui.report-table-card title="Filtered Household Records">
                @php
                    $hasHeadSearch = filled($selectedHeadId ?? null) || filled($headQuery ?? null);
                @endphp
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                @php
                                    $currentSort = $sort ?? 'head_name';
                                    $currentDirection = $direction ?? 'asc';
                                    $nextDirection = fn ($column) => $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';
                                @endphp
                                <x-ui.table-th padding-class="px-5 py-3">
                                    <a class="hover:text-gray-900" href="{{ route($rp . '.reports.households', array_merge(request()->query(), ['sort' => 'head_name', 'direction' => $nextDirection('head_name')])) }}">Head of Family</a>
                                </x-ui.table-th>
                                <x-ui.table-th padding-class="px-5 py-3">
                                    <a class="hover:text-gray-900" href="{{ route($rp . '.reports.households', array_merge(request()->query(), ['sort' => 'purok', 'direction' => $nextDirection('purok')])) }}">Purok</a>
                                </x-ui.table-th>
                                <x-ui.table-th padding-class="px-5 py-3" align="right">
                                    <a class="hover:text-gray-900" href="{{ route($rp . '.reports.households', array_merge(request()->query(), ['sort' => 'members', 'direction' => $nextDirection('members')])) }}">Members</a>
                                </x-ui.table-th>
                                <x-ui.table-th padding-class="px-5 py-3">
                                    <a class="hover:text-gray-900" href="{{ route($rp . '.reports.households', array_merge(request()->query(), ['sort' => 'resident_type', 'direction' => $nextDirection('resident_type')])) }}">Resident Type</a>
                                </x-ui.table-th>
                                <x-ui.table-th padding-class="px-5 py-3">
                                    <a class="hover:text-gray-900" href="{{ route($rp . '.reports.households', array_merge(request()->query(), ['sort' => 'status', 'direction' => $nextDirection('status')])) }}">Status</a>
                                </x-ui.table-th>
                                <x-ui.table-th padding-class="px-5 py-3">Actions</x-ui.table-th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($households as $hh)
                                @php
                                    $expandId = 'household-members-' . $hh->id;
                                    $linkedMembers = collect($hh->familyMembers ?? [])
                                        ->filter(fn ($member) => (int) ($member->household_id ?? 0) === (int) ($hh->household_id ?? 0))
                                        ->reject(fn ($member) => (int) ($member->id ?? 0) === (int) $hh->id)
                                        ->values();
                                    $linkedMemberIds = $linkedMembers->pluck('id')->map(fn ($id) => (int) $id)->all();
                                    $recordMembers = collect($hh->familyMemberRecords ?? [])
                                        ->filter(fn ($member) => (int) ($member->household_id ?? 0) === (int) ($hh->household_id ?? 0))
                                        ->reject(fn ($member) => ! empty($member->linked_user_id) && in_array((int) $member->linked_user_id, $linkedMemberIds, true))
                                        ->values();
                                    $displayMemberCount = $linkedMembers->count() + $recordMembers->count() + 1;
                                @endphp
                                <x-ui.table-row>
                                    <x-ui.table-td padding-class="px-5 py-3" size-class="" text-class="" align="left">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-900">{{ $hh->last_name }}, {{ $hh->first_name }}{{ $hh->middle_name ? ' ' . $hh->middle_name : '' }}</p>
                                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Head</span>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $hh->email }}</p>
                                    </x-ui.table-td>
                                    <x-ui.table-td padding-class="px-5 py-3" label="{{ $hh->purokRelation?->name ?? '—' }}" />
                                    <x-ui.table-td padding-class="px-5 py-3" align="right" text-class="text-gray-900" weight-class="font-semibold" label="{{ $displayMemberCount }}" />
                                    <x-ui.table-td padding-class="px-5 py-3" size-class="" text-class="">
                                        @if($hh->resident_type === 'permanent')
                                            <x-ui.status-badge class="bg-blue-100 text-blue-700">Permanent</x-ui.status-badge>
                                        @elseif($hh->resident_type === 'non-permanent')
                                            <x-ui.status-badge class="bg-amber-100 text-amber-700">Non-permanent</x-ui.status-badge>
                                        @else
                                            <x-ui.status-badge class="bg-gray-100 text-gray-600">—</x-ui.status-badge>
                                        @endif
                                    </x-ui.table-td>
                                    <x-ui.table-td padding-class="px-5 py-3" size-class="" text-class="">
                                        @if($hh->is_suspended)
                                            <x-ui.status-badge class="bg-red-100 text-red-700">Suspended</x-ui.status-badge>
                                        @elseif($hh->status === 'approved')
                                            <x-ui.status-badge class="bg-green-100 text-green-700">Active</x-ui.status-badge>
                                        @elseif($hh->status === 'pending')
                                            <x-ui.status-badge class="bg-yellow-100 text-yellow-700">Pending</x-ui.status-badge>
                                        @else
                                            <x-ui.status-badge class="bg-gray-100 text-gray-600">{{ ucfirst($hh->status ?? '—') }}</x-ui.status-badge>
                                        @endif
                                    </x-ui.table-td>
                                    <x-ui.table-td padding-class="px-5 py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button
                                                type="button"
                                                class="household-expand-toggle inline-flex items-center rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-100"
                                                data-target="{{ $expandId }}"
                                                aria-expanded="false"
                                                aria-controls="{{ $expandId }}"
                                            >
                                                <span class="household-expand-icon">▼</span>
                                            </button>
                                            @php
                                                $viewUrl = route($rp . '.reports.households.view', array_merge(request()->query(), ['head_id' => $hh->id, 'head_q' => $hh->last_name . ', ' . $hh->first_name, 'page' => 1]));
                                                $membersUrl = $rp === 'admin'
                                                    ? route('admin.residents.show', $hh) . '#family-linking'
                                                    : $viewUrl;
                                            @endphp
                                            <a
                                                href="{{ $viewUrl }}"
                                                class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100"
                                            >
                                                View
                                            </a>
                                            <a
                                                href="{{ $membersUrl }}"
                                                class="inline-flex items-center rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-700 hover:bg-violet-100"
                                            >
                                                Members
                                            </a>
                                        </div>
                                    </x-ui.table-td>
                                </x-ui.table-row>
                                <tr id="{{ $expandId }}" class="hidden bg-gray-50">
                                    <td colspan="6" class="px-5 py-4">
                                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Members:</p>
                                            @if ($linkedMembers->isEmpty() && $recordMembers->isEmpty())
                                                <p class="text-sm text-gray-500">(No members linked)</p>
                                            @else
                                                <ul class="space-y-2 pl-4">
                                                    @foreach ($linkedMembers as $member)
                                                        <li class="flex items-center justify-between border-b border-gray-100 pb-2 last:border-0 last:pb-0">
                                                            <div>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-sm font-medium text-gray-900">{{ $member->full_name }}</span>
                                                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">Member</span>
                                                                </div>
                                                                <p class="text-xs text-gray-500">{{ $member->relationship_to_head ?: '—' }}</p>
                                                            </div>
                                                            <a
                                                                href="{{ route('admin.residents.show', $member) }}"
                                                                class="text-xs font-medium text-blue-700 hover:text-blue-900"
                                                            >
                                                                View Full Profile
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                    @foreach ($recordMembers as $member)
                                                        <li class="border-b border-gray-100 pb-2 text-sm text-gray-700 last:border-0 last:pb-0">
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-medium text-gray-900">{{ $member->full_name }}</span>
                                                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">Member</span>
                                                            </div>
                                                            <p class="text-xs text-gray-500">{{ $member->relationship_to_head ?: '—' }}</p>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <x-ui.table-empty-row colspan="6" cell-class="px-5 py-12 text-center text-sm text-gray-500">
                                    <x-ui.empty-state message="{{ $hasHeadSearch ? 'No household found.' : 'No household records found.' }}" text-class="text-sm text-gray-500" />
                                </x-ui.table-empty-row>
                            @endforelse
                        </tbody>
                </table>
                <x-slot:footer>
                    {{ $households->links() }}
                </x-slot:footer>
            </x-ui.report-table-card>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stats-card
                label="Total Households"
                :value="number_format($totalHouseholds)"
                wrapper-class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-amber-700"
            />
            <x-ui.stats-card
                label="Total Members"
                :value="number_format($totalMembers)"
                wrapper-class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-blue-700"
            />
            <x-ui.stats-card
                label="Avg Household Size"
                :value="$avgSize"
                wrapper-class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-green-700"
            />
            <x-ui.summary-card title="Highlights">
                @if($largest)
                    <p class="mt-2 text-sm text-gray-700">
                        Largest: <span class="font-semibold">{{ $largest->last_name }}, {{ $largest->first_name }}</span>
                        <span class="text-gray-400">({{ $largest->family_members_count + ($largest->linked_members_count ?? 0) + 1 }})</span>
                    </p>
                @endif
                @if($smallest)
                    <p class="mt-1 text-sm text-gray-700">
                        Smallest: <span class="font-semibold">{{ $smallest->last_name }}, {{ $smallest->first_name }}</span>
                        <span class="text-gray-400">({{ $smallest->family_members_count + ($smallest->linked_members_count ?? 0) + 1 }})</span>
                    </p>
                @endif
            </x-ui.summary-card>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <x-ui.chart-panel title="Households vs Residents per Purok" wrapper-class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
                <canvas id="householdPurokChart"></canvas>
            </x-ui.chart-panel>
            <x-ui.chart-panel title="Head Status Distribution">
                <canvas id="householdStatusChart"></canvas>
            </x-ui.chart-panel>
        </div>

        <x-ui.chart-panel title="Household Size Distribution">
            <canvas id="householdSizeChart"></canvas>
        </x-ui.chart-panel>
    </div>
</section>
@endsection

<x-ui.chart-script />

@push('scripts')
    <script>
        (function () {
            const headInput = document.getElementById('head_q');
            const headIdInput = document.getElementById('head_id');
            const purokSelect = document.getElementById('purok');
            const suggestionsBox = document.getElementById('headSuggestions');
            const loadingText = document.getElementById('headSearchLoading');
            const suggestionsUrl = @json(route($rp . '.reports.households.head-suggestions'));
            const form = headInput?.closest('form') ?? null;
            const filterSubmitButton = form?.querySelector('button[type="submit"]') ?? null;
            const debounceMs = 300;
            let debounceTimer = null;
            let requestCounter = 0;
            let hadActiveHeadFilter = (headIdInput?.value ?? '') !== '' || (headInput?.value.trim() ?? '') !== '';
            let activeSuggestionIndex = -1;

            if (!headInput || !headIdInput || !suggestionsBox || !loadingText || !form) return;

            const escapeHtml = (value) => String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');

            const escapeRegExp = (value) => String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            const highlightMatch = (value, query) => {
                const safeText = escapeHtml(value);
                if (!query) return safeText;

                const re = new RegExp(`(${escapeRegExp(query)})`, 'ig');
                return safeText.replace(re, '<mark class="rounded bg-yellow-100 px-0.5">$1</mark>');
            };

            const closeSuggestions = () => {
                suggestionsBox.classList.add('hidden');
                suggestionsBox.innerHTML = '';
                activeSuggestionIndex = -1;
                headInput.setAttribute('aria-expanded', 'false');
                headInput.setAttribute('aria-activedescendant', '');
            };

            const setLoading = (active) => {
                loadingText.classList.toggle('hidden', !active);
            };

            const selectSuggestion = (item) => {
                headInput.value = item.name || '';
                headIdInput.value = item.id || '';
                hadActiveHeadFilter = true;
                closeSuggestions();
                form.requestSubmit();
            };

            const renderSuggestions = (items, query) => {
                if (!Array.isArray(items) || items.length === 0) {
                    suggestionsBox.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">No matching heads found.</div>';
                    suggestionsBox.classList.remove('hidden');
                    headInput.setAttribute('aria-expanded', 'true');
                    return;
                }

                const html = items.map((item) => {
                    const metaParts = [];
                    if (item.purok) metaParts.push(`Purok: ${escapeHtml(item.purok)}`);
                    if (item.house_id) metaParts.push(`House: ${escapeHtml(item.house_id)}`);
                    const optionId = `head-suggestion-${escapeHtml(item.id)}`;

                    return `
                        <button
                            type="button"
                            id="${optionId}"
                            role="option"
                            aria-selected="false"
                            class="head-suggestion flex w-full flex-col items-start px-3 py-2 text-left hover:bg-blue-50"
                            data-id="${escapeHtml(item.id)}"
                            data-name="${escapeHtml(item.name)}"
                        >
                            <span class="text-sm font-medium text-gray-900">${highlightMatch(item.name ?? '', query)}</span>
                            <span class="text-xs text-gray-500">${metaParts.join(' — ')}</span>
                        </button>
                    `;
                }).join('');

                suggestionsBox.innerHTML = html;
                suggestionsBox.classList.remove('hidden');
                headInput.setAttribute('aria-expanded', 'true');
                activeSuggestionIndex = -1;

                suggestionsBox.querySelectorAll('.head-suggestion').forEach((button) => {
                    button.addEventListener('click', () => {
                        selectSuggestion({
                            id: button.dataset.id,
                            name: button.dataset.name,
                        });
                    });
                });
            };

            const fetchSuggestions = async (query) => {
                const currentRequest = ++requestCounter;
                const params = new URLSearchParams();
                params.set('q', query);
                if (purokSelect?.value) {
                    params.set('purok', purokSelect.value);
                }

                setLoading(true);

                try {
                    const response = await fetch(`${suggestionsUrl}?${params.toString()}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        throw new Error('Failed to fetch suggestions.');
                    }

                    const payload = await response.json();
                    if (currentRequest !== requestCounter) {
                        return;
                    }

                    renderSuggestions(payload.data ?? [], query);
                } catch (_error) {
                    if (currentRequest === requestCounter) {
                        suggestionsBox.innerHTML = '<div class="px-3 py-2 text-sm text-red-600">Unable to load suggestions.</div>';
                        suggestionsBox.classList.remove('hidden');
                    }
                } finally {
                    if (currentRequest === requestCounter) {
                        setLoading(false);
                    }
                }
            };

            headInput.addEventListener('input', (event) => {
                const query = event.target.value.trim();
                headIdInput.value = '';
                clearTimeout(debounceTimer);

                if (query.length === 0) {
                    setLoading(false);
                    closeSuggestions();

                    if (hadActiveHeadFilter) {
                        hadActiveHeadFilter = false;
                        form.requestSubmit();
                    }
                    return;
                }

                if (query.length < 2) {
                    setLoading(false);
                    closeSuggestions();
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, debounceMs);
            });

            headInput.addEventListener('focus', () => {
                const query = headInput.value.trim();
                if (query.length >= 2) {
                    fetchSuggestions(query);
                }
            });

            headInput.addEventListener('keydown', (event) => {
                const items = Array.from(suggestionsBox.querySelectorAll('.head-suggestion'));
                if (items.length === 0) return;

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    activeSuggestionIndex = (activeSuggestionIndex + 1) % items.length;
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    activeSuggestionIndex = activeSuggestionIndex <= 0 ? items.length - 1 : activeSuggestionIndex - 1;
                } else if (event.key === 'Enter') {
                    if (activeSuggestionIndex >= 0 && activeSuggestionIndex < items.length) {
                        event.preventDefault();
                        const item = items[activeSuggestionIndex];
                        selectSuggestion({
                            id: item.dataset.id,
                            name: item.dataset.name,
                        });
                    }
                    return;
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    closeSuggestions();
                    return;
                } else {
                    return;
                }

                items.forEach((item, index) => {
                    const isActive = index === activeSuggestionIndex;
                    item.classList.toggle('bg-blue-100', isActive);
                    item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                const activeItem = items[activeSuggestionIndex];
                if (activeItem) {
                    headInput.setAttribute('aria-activedescendant', activeItem.id);
                    activeItem.scrollIntoView({ block: 'nearest' });
                }
            });

            purokSelect?.addEventListener('change', () => {
                headIdInput.value = '';
                closeSuggestions();
                hadActiveHeadFilter = false;
            });

            document.addEventListener('click', (event) => {
                if (!suggestionsBox.contains(event.target) && event.target !== headInput) {
                    closeSuggestions();
                }
            });

            form.addEventListener('submit', () => {
                if (!filterSubmitButton) return;
                filterSubmitButton.dataset.originalLabel = filterSubmitButton.textContent ?? 'Apply Filters';
                filterSubmitButton.textContent = 'Applying...';
                filterSubmitButton.disabled = true;
            });
        })();
    </script>
    <script>
        (function () {
            document.addEventListener('click', function (event) {
                const button = event.target.closest('.household-expand-toggle');
                if (!button) {
                    return;
                }

                const targetId = button.getAttribute('data-target');
                const targetRow = targetId ? document.getElementById(targetId) : null;
                if (!targetRow) {
                    return;
                }

                const willExpand = targetRow.classList.contains('hidden');
                targetRow.classList.toggle('hidden');
                button.setAttribute('aria-expanded', willExpand ? 'true' : 'false');

                const icon = button.querySelector('.household-expand-icon');
                if (icon) {
                    icon.textContent = willExpand ? '▲' : '▼';
                }
            });
        })();
    </script>
    <script>
        const purokLabels = @json($purokLabels);
        const purokHouseholdSeries = @json($purokHouseholdSeries);
        const purokMemberSeries = @json($purokMemberSeries);
        const sizeBands = @json($sizeBands);
        const statusDistribution = @json($statusDistribution);

        window.reportCreateChart('householdPurokChart', {
            type: 'bar',
            data: {
                labels: purokLabels,
                datasets: [
                    {
                        label: 'Households',
                        data: purokHouseholdSeries,
                        backgroundColor: '#f59e0b'
                    },
                    {
                        label: 'Residents',
                        data: purokMemberSeries,
                        backgroundColor: '#2563eb'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        window.reportCreateChart('householdStatusChart', {
            type: 'pie',
            data: {
                labels: ['Active', 'Pending', 'Suspended'],
                datasets: [{
                    data: [statusDistribution.active, statusDistribution.pending, statusDistribution.suspended],
                    backgroundColor: ['#16a34a', '#f59e0b', '#dc2626']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        window.reportCreateChart('householdSizeChart', {
            type: 'line',
            data: {
                labels: Object.keys(sizeBands),
                datasets: [{
                    label: 'Households',
                    data: Object.values(sizeBands),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    </script>
@endpush
