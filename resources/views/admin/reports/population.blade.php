@extends($layout ?? 'layouts.admin')

@section('title', 'Population Reports - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mx-auto max-w-6xl space-y-6">

        <x-ui.report-topbar
            title="Population Reports"
            subtitle="Demographics from the same population register used for barangay totals (approved accounts + listed household members without duplicate counting)"
            :back-url="route($rp . '.reports.index')"
            class="no-print"
        >
            <x-slot:actions>
                <x-ui.export-toolbar
                    :pdf-url="route($rp . '.reports.population.export.pdf', request()->query())"
                    :excel-url="route($rp . '.reports.population.export.excel', request()->query())"
                    filter-label="Exports include current filters"
                    :filter-value="$activePopulationFilterLabel"
                    :show-print="false"
                />
            </x-slot:actions>
        </x-ui.report-topbar>

        <div class="no-print rounded-2xl border border-blue-100 bg-blue-50/80 px-4 py-3 text-sm text-blue-900 shadow-sm">
            <p class="font-semibold text-blue-950">How these numbers relate</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-blue-900/90">
                <li><strong>{{ number_format($filteredPopulationCount) }}</strong> people match <strong>current filters</strong> (purok scope + age + gender).</li>
                <li><strong>{{ number_format($scopePopulationTotal) }}</strong> people in the <strong>same purok scope</strong> with <strong>all ages and genders</strong> — use this to compare with dashboard “total residents” when filters are set to “All”.</li>
                <li><strong>Active / Suspended</strong> below count only <strong>user accounts</strong> (login). Household members without their own account appear in per-purok population rows but not in those two cards.</li>
                @if (($activeAccountsOutsidePurokTable ?? 0) > 0)
                    <li><strong>{{ number_format($activeAccountsOutsidePurokTable) }}</strong> active account(s) have <strong>no purok assigned</strong>, so they are not in the per-purok “Active” column (they are still included in the Active total card).</li>
                @endif
            </ul>
        </div>

        {{-- Multi Filter Row --}}
        <div class="mb-8 no-print">
            <x-ui.report-filter-bar
                :action="route($rp . '.reports.population')"
                :reset-url="route($rp . '.reports.population')"
                fields-class="grid grid-cols-1 gap-3 md:grid-cols-5 md:items-end"
                submit-label="Filter"
                submit-class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700"
                wrapper-class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
            >
                <div class="min-w-[180px]">
                    <label for="purok" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Purok</label>
                    <select name="purok" id="purok" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" @selected(! $purokId)>All Puroks</option>
                        @foreach($allPuroks as $p)
                            <option value="{{ $p->id }}" @selected($purokId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label for="age_range" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Age Range</label>
                    <select name="age_range" id="age_range" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" @selected(($ageRange ?? 'all') === 'all')>All Ages</option>
                        <option value="minors" @selected(($ageRange ?? 'all') === 'minors')>Minors (0-17)</option>
                        <option value="adults" @selected(($ageRange ?? 'all') === 'adults')>Adults (18-59)</option>
                        <option value="seniors" @selected(($ageRange ?? 'all') === 'seniors')>Seniors (60+)</option>
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label for="gender" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" id="gender" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all" @selected(($gender ?? 'all') === 'all')>All</option>
                        <option value="male" @selected(($gender ?? 'all') === 'male')>Male</option>
                        <option value="female" @selected(($gender ?? 'all') === 'female')>Female</option>
                    </select>
                </div>
            </x-ui.report-filter-bar>
            @if($purokId || ($ageRange ?? 'all') !== 'all' || ($gender ?? 'all') !== 'all')
                <p class="mt-2 text-xs text-blue-600 font-medium">
                    {{ $activePopulationFilterLabel }}
                </p>
            @endif
        </div>

        {{-- Section: Age & Gender Summary --}}
        <div class="mb-8">
            <x-ui.section-heading title="Age & Gender Demographics" />
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="rounded-2xl bg-gradient-to-br from-orange-50 to-orange-100 p-5 shadow-sm ring-1 ring-orange-200">
                    <p class="text-xs font-semibold text-orange-600 uppercase tracking-wider">Minors</p>
                    <p class="text-3xl font-bold text-orange-800 mt-1">{{ number_format($ageStats['minors']) }}</p>
                    <p class="text-[10px] text-orange-500 mt-1">Below 18 years</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 p-5 shadow-sm ring-1 ring-blue-200">
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Adults</p>
                    <p class="text-3xl font-bold text-blue-800 mt-1">{{ number_format($ageStats['adults']) }}</p>
                    <p class="text-[10px] text-blue-500 mt-1">18 – 59 years</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 p-5 shadow-sm ring-1 ring-purple-200">
                    <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Seniors</p>
                    <p class="text-3xl font-bold text-purple-800 mt-1">{{ number_format($ageStats['seniors']) }}</p>
                    <p class="text-[10px] text-purple-500 mt-1">60 years & above</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-sky-50 to-sky-100 p-5 shadow-sm ring-1 ring-sky-200">
                    <p class="text-xs font-semibold text-sky-600 uppercase tracking-wider">Male</p>
                    <p class="text-3xl font-bold text-sky-800 mt-1">{{ number_format($genderStats['male']) }}</p>
                    <p class="text-[10px] text-sky-500 mt-1">Male residents</p>
                </div>
                <div class="rounded-2xl bg-gradient-to-br from-pink-50 to-pink-100 p-5 shadow-sm ring-1 ring-pink-200">
                    <p class="text-xs font-semibold text-pink-600 uppercase tracking-wider">Female</p>
                    <p class="text-3xl font-bold text-pink-800 mt-1">{{ number_format($genderStats['female']) }}</p>
                    <p class="text-[10px] text-pink-500 mt-1">Female residents</p>
                </div>
            </div>
        </div>

        {{-- Section: Per-Purok Age & Gender Breakdown --}}
        <div class="mb-8">
            <x-ui.section-heading title="Per-Purok Age & Gender Breakdown" />
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-ui.table-th label="Purok" padding-class="px-5 py-3" />
                                <x-ui.table-th label="Total" padding-class="px-5 py-3" align="right" />
                                <x-ui.table-th label="Minors" padding-class="px-5 py-3" align="right" text-class="text-orange-600" />
                                <x-ui.table-th label="Adults" padding-class="px-5 py-3" align="right" text-class="text-blue-600" />
                                <x-ui.table-th label="Seniors" padding-class="px-5 py-3" align="right" text-class="text-purple-600" />
                                <x-ui.table-th label="Male" padding-class="px-5 py-3" align="right" text-class="text-sky-600" />
                                <x-ui.table-th label="Female" padding-class="px-5 py-3" align="right" text-class="text-pink-600" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($demographicsPerPurok as $row)
                                <x-ui.table-row class="transition-colors">
                                    <x-ui.table-td padding-class="px-5 py-3.5" text-class="text-gray-900" weight-class="font-medium" label="{{ $row->name }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" label="{{ number_format($row->total_residents) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-orange-600" label="{{ number_format($row->minors) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-blue-600" label="{{ number_format($row->adults) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-purple-600" label="{{ number_format($row->seniors) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-sky-600" label="{{ number_format($row->male) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-pink-600" label="{{ number_format($row->female) }}" />
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-empty-row colspan="7" cell-class="px-5 py-12 text-center text-gray-500">
                                    <x-ui.empty-state message="No purok data available" />
                                </x-ui.table-empty-row>
                            @endforelse
                        </tbody>
                        @if ($demographicsPerPurok->isNotEmpty())
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <x-ui.table-td padding-class="px-5 py-3.5" text-class="text-gray-900" weight-class="font-bold" label="Grand Total" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($demographicsPerPurok->sum('total_residents')) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-orange-600" weight-class="font-bold" label="{{ number_format($demographicsPerPurok->sum('minors')) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-blue-600" weight-class="font-bold" label="{{ number_format($demographicsPerPurok->sum('adults')) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-purple-600" weight-class="font-bold" label="{{ number_format($demographicsPerPurok->sum('seniors')) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-sky-600" weight-class="font-bold" label="{{ number_format($demographicsPerPurok->sum('male')) }}" />
                                    <x-ui.table-td padding-class="px-5 py-3.5" align="right" class="tabular-nums" text-class="text-pink-600" weight-class="font-bold" label="{{ number_format($demographicsPerPurok->sum('female')) }}" />
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Section: Activity Status Summary --}}
        <div class="mb-8">
            <x-ui.section-heading title="Activity Status Summary" />
            <div class="grid md:grid-cols-2 gap-4">
                <x-ui.summary-card wrapper-class="rounded-2xl bg-gradient-to-br from-green-50 to-green-100 p-6 shadow-sm ring-1 ring-green-200">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-green-500 text-white shadow-lg shadow-green-200 no-print">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-green-700 uppercase">Active accounts</p>
                            <p class="text-4xl font-bold text-green-800 mt-1">{{ number_format($activeCount) }}</p>
                            <p class="text-xs text-green-600 mt-1">Approved resident logins, not suspended (current age/gender filters apply)</p>
                        </div>
                    </div>
                </x-ui.summary-card>
                <x-ui.summary-card wrapper-class="rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gray-400 text-white shadow-lg shadow-gray-200 no-print">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-600 uppercase">Suspended accounts</p>
                            <p class="text-4xl font-bold text-gray-700 mt-1">{{ number_format($inactiveCount) }}</p>
                            <p class="text-xs text-gray-500 mt-1">Approved accounts with suspended login only — not pending or rejected registrations</p>
                        </div>
                    </div>
                </x-ui.summary-card>
            </div>
        </div>

        {{-- Section: Residents per Purok --}}
        <div class="mb-8">
            <x-ui.section-heading title="Residents per Purok" />
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-ui.table-th label="Purok Name" />
                                <x-ui.table-th label="Total Residents" align="right" />
                                <x-ui.table-th label="Active" align="right" text-class="text-green-600" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($residentsPerPurok as $purok)
                                <x-ui.table-row class="transition-colors">
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-medium" label="{{ $purok->name }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($purok->residents_count) }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-green-600" weight-class="font-medium" label="{{ number_format($activePerPurok[$purok->id] ?? 0) }}" />
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-empty-row colspan="3" cell-class="px-6 py-12 text-center">
                                    <x-ui.empty-state message="No purok data available" />
                                </x-ui.table-empty-row>
                            @endforelse
                        </tbody>
                        @if ($residentsPerPurok->isNotEmpty())
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Grand Total" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($residentsPerPurok->sum('residents_count')) }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-green-600" weight-class="font-bold" label="{{ number_format($activePerPurokFootSum) }}" />
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Section: Resident Type Distribution --}}
        <div>
            <x-ui.section-heading title="Resident Type Distribution" />
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-ui.table-th label="Purok Name" />
                                <x-ui.table-th label="Permanent" align="right" text-class="text-blue-600" />
                                <x-ui.table-th label="Non-Permanent" align="right" text-class="text-amber-600" />
                                <x-ui.table-th label="Total" align="right" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php
                                $grandPermanent = 0;
                                $grandNonPermanent = 0;
                            @endphp
                            @forelse ($puroks as $purok)
                                @php
                                    $purokData = $residentTypePerPurok[$purok->id] ?? collect();
                                    $permanent = $purokData->firstWhere('resident_type', 'permanent');
                                    $nonPermanent = $purokData->firstWhere('resident_type', 'non-permanent');
                                    $permanentCount = $permanent ? $permanent->total : 0;
                                    $nonPermanentCount = $nonPermanent ? $nonPermanent->total : 0;
                                    $grandPermanent += $permanentCount;
                                    $grandNonPermanent += $nonPermanentCount;
                                @endphp
                                <x-ui.table-row class="transition-colors">
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-medium" label="{{ $purok->name }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-blue-600" label="{{ number_format($permanentCount) }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-amber-600" label="{{ number_format($nonPermanentCount) }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-medium" label="{{ number_format($permanentCount + $nonPermanentCount) }}" />
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-empty-row colspan="4" cell-class="px-6 py-12 text-center">
                                    <x-ui.empty-state message="No data available" />
                                </x-ui.table-empty-row>
                            @endforelse
                        </tbody>
                        @if ($puroks->isNotEmpty())
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Grand Total" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-blue-600" weight-class="font-bold" label="{{ number_format($grandPermanent) }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-amber-600" weight-class="font-bold" label="{{ number_format($grandNonPermanent) }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($grandPermanent + $grandNonPermanent) }}" />
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var filterForm = document.querySelector('form[action="{{ route($rp . '.reports.population') }}"]');
    if (!filterForm) return;
    ['purok', 'age_range', 'gender'].forEach(function (id) {
        var field = document.getElementById(id);
        if (!field) return;
        field.addEventListener('change', function () {
            filterForm.requestSubmit();
        });
    });
});
</script>
@endpush

