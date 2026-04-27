@extends($layout ?? 'layouts.admin')

@section('title', 'Classification Reports - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6">

        <x-ui.report-topbar
            title="Classification Reports"
            subtitle="PWD and Senior Citizens breakdown"
            :back-url="route($rp . '.reports.index')"
            class="no-print mb-8"
        >
            <x-slot:actions>
                <x-ui.export-toolbar
                    :pdf-url="route($rp . '.reports.classification.export.pdf', request()->query())"
                    :excel-url="route($rp . '.reports.classification.export.excel', request()->query())"
                    filter-label="Exports include current filters"
                    :filter-value="$allPuroks->firstWhere('id', $purokId)?->name ?? 'All Puroks'"
                    :show-print="true"
                    print-button-class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gray-900 transition"
                />
            </x-slot:actions>
        </x-ui.report-topbar>

        {{-- Purok Filter --}}
        <div class="mb-8 no-print">
            <x-ui.report-filter-bar
                :action="route($rp . '.reports.classification')"
                :reset-url="route($rp . '.reports.classification')"
                fields-class="flex flex-wrap items-end gap-3"
                submit-label="Filter"
                wrapper-class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
            >
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <label for="purok" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Filter by Purok</label>
                    <select name="purok" id="purok" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Puroks</option>
                        @foreach($allPuroks as $p)
                            <option value="{{ $p->id }}" @selected($purokId == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </x-ui.report-filter-bar>
            @if($purokId)
                <p class="mt-2 text-xs text-blue-600 font-medium">
                    Showing data for: {{ $allPuroks->firstWhere('id', $purokId)?->name ?? 'Unknown Purok' }}
                </p>
            @endif
        </div>

        {{-- Section: Classification Summary --}}
        <div class="mb-8">
            <x-ui.section-heading title="Classification Summary" />
            <div class="grid md:grid-cols-2 gap-4">
                {{-- PWD Card --}}
                <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 p-6 shadow-sm ring-1 ring-blue-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-700 uppercase">Persons with Disability</p>
                            <p class="text-4xl font-bold text-blue-800 mt-2">{{ number_format($totals['pwd']) }}</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[var(--brand-600)] text-white shadow-lg shadow-[var(--brand-100)] no-print">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200 flex gap-6">
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ $totals['pwd_verified'] }}</p>
                            <p class="text-xs text-green-600">Verified</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-amber-600">{{ $totals['pwd_pending'] }}</p>
                            <p class="text-xs text-amber-600">Pending</p>
                        </div>
                    </div>
                </div>

                {{-- Senior Card --}}
                <div class="rounded-2xl bg-gradient-to-br from-purple-50 to-purple-100 p-6 shadow-sm ring-1 ring-purple-200">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-purple-700 uppercase">Senior Citizens</p>
                            <p class="text-4xl font-bold text-purple-800 mt-2">{{ number_format($totals['senior']) }}</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-500 text-white shadow-lg shadow-purple-200 no-print">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-purple-200 flex gap-6">
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ $totals['senior_verified'] }}</p>
                            <p class="text-xs text-green-600">Verified</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-amber-600">{{ $totals['senior_pending'] }}</p>
                            <p class="text-xs text-amber-600">Pending</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Section: Classification per Purok --}}
        <div>
            <x-ui.section-heading title="Breakdown by Purok" />
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-ui.table-th label="Purok Name" />
                                <x-ui.table-th label="PWD" align="right" text-class="text-blue-600" />
                                <x-ui.table-th label="Senior" align="right" text-class="text-purple-600" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($classificationPerPurok as $row)
                                <x-ui.table-row class="transition-colors">
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-medium" label="{{ $row->name }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" size-class="" text-class="">
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-sm font-medium {{ $row->pwd_count > 0 ? 'bg-blue-100 text-blue-700' : 'text-gray-400' }}">
                                            {{ number_format($row->pwd_count) }}
                                        </span>
                                    </x-ui.table-td>
                                    <x-ui.table-td align="right" class="tabular-nums" size-class="" text-class="">
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-sm font-medium {{ $row->senior_count > 0 ? 'bg-purple-100 text-purple-700' : 'text-gray-400' }}">
                                            {{ number_format($row->senior_count) }}
                                        </span>
                                    </x-ui.table-td>
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-empty-row colspan="3" cell-class="px-6 py-12 text-center">
                                    <x-ui.empty-state message="No classification data available" />
                                </x-ui.table-empty-row>
                            @endforelse
                        </tbody>
                        @if ($classificationPerPurok->isNotEmpty())
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Grand Total" />
                                    <x-ui.table-td align="right" size-class="" text-class="">
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-sm font-bold bg-[var(--brand-700)] text-white">
                                            {{ number_format($totals['pwd']) }}
                                        </span>
                                    </x-ui.table-td>
                                    <x-ui.table-td align="right" size-class="" text-class="">
                                        <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2.5 py-1 rounded-full text-sm font-bold bg-purple-600 text-white">
                                            {{ number_format($totals['senior']) }}
                                        </span>
                                    </x-ui.table-td>
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

