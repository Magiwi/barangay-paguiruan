@extends($layout ?? 'layouts.admin')

@section('title', 'Household View Report - e-Governance')

@section('content')
@php
    $rp = $routePrefix ?? 'admin';
    $query = request()->query();
    $backUrl = route($rp . '.reports.households', $query);
    $templatePrintUrl = route($rp . '.reports.households.view.print', $printQuery ?? $query);
    $exportFilterValue = !empty($appliedFilters)
        ? implode(' | ', array_slice($appliedFilters, 0, 2)) . (count($appliedFilters) > 2 ? ' | +' . (count($appliedFilters) - 2) . ' more' : '')
        : 'All household view records';
@endphp

<section class="bg-gray-50 min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">
        <div class="rounded-2xl border border-gray-200 bg-gradient-to-r from-white to-gray-50 px-5 py-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <h1 class="text-xl font-semibold text-gray-900">{{ $reportTitle }}</h1>
                    <p class="text-xs text-gray-500">
                        Type: {{ $reportType }} |
                        Scope: {{ $reportScope }} |
                        Generated: {{ $generatedAt }} |
                        Total Records: {{ number_format($detailTotalRecords) }}
                    </p>
                    <div class="mt-2 no-print">
                        <a href="{{ $backUrl }}" class="ui-focus-ring inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            &larr; Back
                        </a>
                    </div>
                </div>
                <div class="no-print w-full md:ml-auto md:w-auto">
                    <x-ui.export-toolbar
                        :pdf-url="route($rp . '.reports.households.view.export.pdf', $pdfQuery ?? $query)"
                        :excel-url="route($rp . '.reports.households.view.export.excel', $excelQuery ?? $query)"
                        :print-url="$templatePrintUrl"
                        filter-label="Exports include current view filters"
                        :filter-value="$exportFilterValue"
                        print-button-class="ui-focus-ring inline-flex items-center rounded-lg border border-violet-200 bg-violet-50 px-3 py-2 text-sm font-semibold text-violet-700 transition hover:bg-violet-100"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Total Rows</p>
                <p class="mt-1 text-2xl font-bold text-amber-700">{{ number_format($detailTotalRecords) }}</p>
            </div>
            <div class="rounded-xl border border-blue-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Unique Household Heads</p>
                <p class="mt-1 text-2xl font-bold text-blue-700">{{ number_format($householdHeadsCount) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Rows Per Page</p>
                <p class="mt-1 text-2xl font-bold text-emerald-700">{{ number_format($perPage) }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm no-print">
            <h2 class="text-sm font-semibold text-gray-800">Applied Filters</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Report Scope: {{ $reportScope }}</span>
                @forelse($appliedFilters as $filterLabel)
                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-700">{{ $filterLabel }}</span>
                @empty
                    <span class="text-xs text-gray-500">No additional filters applied.</span>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm no-print">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold text-gray-800">Show or hide columns</h2>
                <span id="columnsStatus" class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">0/0 columns shown</span>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="column-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-col="house_head" checked>
                    House Head
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="column-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-col="family_member" checked>
                    Family Member
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="column-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-col="relationship" checked>
                    Relationship
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="column-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-col="house_no" checked>
                    House No.
                </label>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button type="button" id="columnsSelectAll" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Select all</button>
                <button type="button" id="columnsClear" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Clear</button>
                <button type="button" id="columnsResetDefault" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Reset default</button>
                <button type="button" id="columnsApply" class="rounded-md bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-blue-700">Apply</button>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm no-print">
            <h2 class="text-sm font-semibold text-gray-800">Sort Household View</h2>
            <form method="GET" action="{{ route($rp . '.reports.households.view') }}" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-5">
                @foreach(request()->except(['view_sort', 'view_order', 'page', 'per_page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="md:col-span-2">
                    <label for="view_sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Sort By</label>
                    <select id="view_sort" name="view_sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="letter" @selected($viewSort === 'letter')>By Letter</option>
                        <option value="relationship" @selected($viewSort === 'relationship')>Relationship</option>
                        <option value="house_no" @selected($viewSort === 'house_no')>House No.</option>
                    </select>
                </div>

                <div>
                    <label for="view_order" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Order</label>
                    <select id="view_order" name="view_order" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="asc" @selected($viewOrder === 'asc')>A - Z</option>
                        <option value="desc" @selected($viewOrder === 'desc')>Z - A</option>
                    </select>
                </div>
                <div>
                    <label for="per_page" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Rows</label>
                    <select id="per_page" name="per_page" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="25" @selected((int) $perPage === 25)>25</option>
                        <option value="50" @selected((int) $perPage === 50)>50</option>
                        <option value="100" @selected((int) $perPage === 100)>100</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Apply Sort</button>
                    <a href="{{ route($rp . '.reports.households.view', request()->except(['view_sort', 'view_order', 'page', 'per_page'])) }}" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm" id="householdDetailTable">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="col-house_head px-4 py-2 text-left">House Head</th>
                            <th class="col-family_member px-4 py-2 text-left">Family Member</th>
                            <th class="col-relationship px-4 py-2 text-left">Relationship</th>
                            <th class="col-house_no px-4 py-2 text-left">House No.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($detailRows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="col-house_head px-4 py-2 font-medium text-gray-900">{{ $row['house_head'] }}</td>
                                <td class="col-family_member px-4 py-2 text-gray-700">{{ $row['family_member'] }}</td>
                                <td class="col-relationship px-4 py-2 text-gray-700">{{ $row['relationship'] }}</td>
                                <td class="col-house_no px-4 py-2 text-gray-700">{{ $row['house_no'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">No household records found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 px-4 py-3 no-print">
                {{ $detailRows->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        (function () {
            const toggles = Array.from(document.querySelectorAll('.column-toggle'));
            const applyButton = document.getElementById('columnsApply');
            const selectAllButton = document.getElementById('columnsSelectAll');
            const clearButton = document.getElementById('columnsClear');
            const storageKey = 'household-report-columns-v1';
            const statusBadge = document.getElementById('columnsStatus');
            const defaultColumns = toggles.map((toggle) => toggle.dataset.col);

            if (!toggles.length || !applyButton) return;

            const loadColumnPreferences = () => {
                try {
                    const raw = localStorage.getItem(storageKey);
                    if (!raw) return;
                    const parsed = JSON.parse(raw);
                    if (!Array.isArray(parsed)) return;
                    const enabled = new Set(parsed);
                    toggles.forEach((toggle) => {
                        toggle.checked = enabled.has(toggle.dataset.col);
                    });
                } catch (_error) {
                    // Ignore invalid browser state.
                }
            };

            const saveColumnPreferences = () => {
                const enabled = toggles
                    .filter((toggle) => toggle.checked)
                    .map((toggle) => toggle.dataset.col);
                try {
                    localStorage.setItem(storageKey, JSON.stringify(enabled));
                } catch (_error) {
                    // Ignore storage errors.
                }
            };

            const updateColumnStatus = () => {
                if (!statusBadge) return;
                const enabledCount = toggles.filter((toggle) => toggle.checked).length;
                statusBadge.textContent = enabledCount + '/' + toggles.length + ' columns shown';
            };

            const applyColumns = () => {
                toggles.forEach((toggle) => {
                    const columnClass = '.col-' + toggle.dataset.col;
                    document.querySelectorAll(columnClass).forEach((cell) => {
                        cell.classList.toggle('hidden', !toggle.checked);
                    });
                });
                saveColumnPreferences();
                updateColumnStatus();
            };

            selectAllButton?.addEventListener('click', () => {
                toggles.forEach((toggle) => {
                    toggle.checked = true;
                });
                applyColumns();
            });

            clearButton?.addEventListener('click', () => {
                toggles.forEach((toggle) => {
                    toggle.checked = false;
                });
                applyColumns();
            });

            document.getElementById('columnsResetDefault')?.addEventListener('click', () => {
                const defaults = new Set(defaultColumns);
                toggles.forEach((toggle) => {
                    toggle.checked = defaults.has(toggle.dataset.col);
                });
                applyColumns();
            });

            applyButton.addEventListener('click', applyColumns);
            loadColumnPreferences();
            applyColumns();
        })();
    </script>
@endpush
