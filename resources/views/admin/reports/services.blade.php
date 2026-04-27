@extends($layout ?? 'layouts.admin')

@section('title', 'Service Reports - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6">

        <x-ui.report-topbar
            title="Service Reports"
            subtitle="Certificates, permits, and issue reports summary"
            :back-url="route($rp . '.reports.index')"
            class="no-print mb-8"
        >
            <x-slot:actions>
                <x-ui.export-toolbar
                    :pdf-url="route($rp . '.reports.services.export.pdf', request()->query())"
                    :excel-url="route($rp . '.reports.services.export.excel', request()->query())"
                    filter-label="Exports include current filters"
                    :filter-value="$allPuroks->firstWhere('id', $purokId)?->name ?? 'All Puroks'"
                    :show-print="true"
                    print-button-class="ui-btn ui-btn-primary inline-flex rounded-lg px-4 py-2.5 text-sm shadow-sm"
                />
            </x-slot:actions>
        </x-ui.report-topbar>

        {{-- Purok Filter --}}
        <div class="mb-8 no-print">
            <x-ui.report-filter-bar
                :action="route($rp . '.reports.services')"
                :reset-url="route($rp . '.reports.services')"
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

        {{-- Section: Certificate Requests --}}
        <div class="mb-10">
            <x-ui.section-heading title="Certificate Requests">
                <x-slot:actions>
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.certificates.index') }}" class="text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1 no-print">
                            View all requests
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </x-slot:actions>
            </x-ui.section-heading>
            
            {{-- Certificate Status Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <x-ui.metric-icon-card
                    label="Total"
                    :value="number_format($certificateStats['total'])"
                    wrapper-class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Pending"
                    :value="number_format($certificateStats['pending'])"
                    wrapper-class="rounded-2xl bg-amber-50 p-5 shadow-sm ring-1 ring-amber-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 no-print"
                    label-class="text-xs font-medium text-amber-600 uppercase"
                    value-class="text-2xl font-bold text-amber-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Approved"
                    :value="number_format($certificateStats['approved'])"
                    wrapper-class="rounded-2xl bg-green-50 p-5 shadow-sm ring-1 ring-green-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600 no-print"
                    label-class="text-xs font-medium text-green-600 uppercase"
                    value-class="text-2xl font-bold text-green-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Released"
                    :value="number_format($certificateStats['released'])"
                    wrapper-class="rounded-2xl bg-blue-50 p-5 shadow-sm ring-1 ring-blue-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 no-print"
                    label-class="text-xs font-medium text-blue-600 uppercase"
                    value-class="text-2xl font-bold text-blue-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Rejected"
                    :value="number_format($certificateStats['rejected'])"
                    wrapper-class="rounded-2xl bg-red-50 p-5 shadow-sm ring-1 ring-red-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 no-print"
                    label-class="text-xs font-medium text-red-600 uppercase"
                    value-class="text-2xl font-bold text-red-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
            </div>

            {{-- Certificate by Type Table --}}
            <x-ui.mini-table-card title="Requests by Certificate Type">
                <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-ui.table-th label="Certificate Type" />
                                <x-ui.table-th label="Total Requests" align="right" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($certificatesByType as $cert)
                                <x-ui.table-row class="transition-colors">
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-medium" label="{{ $cert->certificate_type }}" />
                                    <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($cert->total) }}" />
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-empty-row colspan="2" cell-class="px-6 py-12 text-center">
                                    <x-ui.empty-state message="No certificate requests yet" icon="document" />
                                </x-ui.table-empty-row>
                            @endforelse
                        </tbody>
                        @if ($certificatesByType->isNotEmpty())
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Total" />
                                    <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($certificateStats['total']) }}" />
                                </tr>
                            </tfoot>
                        @endif
                </table>
            </x-ui.mini-table-card>
        </div>

        {{-- Section: Permit Applications --}}
        <div class="mb-10">
            <x-ui.section-heading title="Permit Applications">
                <x-slot:actions>
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.permits.index') }}" class="text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1 no-print">
                            View all permits
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </x-slot:actions>
            </x-ui.section-heading>

            {{-- Permit Status Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <x-ui.metric-icon-card
                    label="Total"
                    :value="number_format($permitStats['total'])"
                    wrapper-class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Pending"
                    :value="number_format($permitStats['pending'])"
                    wrapper-class="rounded-2xl bg-amber-50 p-5 shadow-sm ring-1 ring-amber-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 no-print"
                    label-class="text-xs font-medium text-amber-600 uppercase"
                    value-class="text-2xl font-bold text-amber-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Approved"
                    :value="number_format($permitStats['approved'])"
                    wrapper-class="rounded-2xl bg-green-50 p-5 shadow-sm ring-1 ring-green-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600 no-print"
                    label-class="text-xs font-medium text-green-600 uppercase"
                    value-class="text-2xl font-bold text-green-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Released"
                    :value="number_format($permitStats['released'])"
                    wrapper-class="rounded-2xl bg-blue-50 p-5 shadow-sm ring-1 ring-blue-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 no-print"
                    label-class="text-xs font-medium text-blue-600 uppercase"
                    value-class="text-2xl font-bold text-blue-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Rejected"
                    :value="number_format($permitStats['rejected'])"
                    wrapper-class="rounded-2xl bg-red-50 p-5 shadow-sm ring-1 ring-red-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 no-print"
                    label-class="text-xs font-medium text-red-600 uppercase"
                    value-class="text-2xl font-bold text-red-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
            </div>

            {{-- Two tables side by side --}}
            <div class="grid md:grid-cols-2 gap-4">
                {{-- Permits by Type --}}
                <x-ui.mini-table-card title="Applications by Permit Type">
                    <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <x-ui.table-th label="Permit Type" />
                                    <x-ui.table-th label="Count" align="right" />
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($permitsByType as $permit)
                                    <x-ui.table-row class="transition-colors">
                                        <x-ui.table-td text-class="text-gray-900" weight-class="font-medium" label="{{ $permit->permit_type }}" />
                                        <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($permit->total) }}" />
                                    </x-ui.table-row>
                                @empty
                                    <x-ui.table-empty-row colspan="2" cell-class="px-6 py-12 text-center">
                                        <x-ui.empty-state message="No permit applications yet" icon="document" />
                                    </x-ui.table-empty-row>
                                @endforelse
                            </tbody>
                            @if ($permitsByType->isNotEmpty())
                                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                    <tr>
                                        <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Total" />
                                        <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($permitStats['total']) }}" />
                                    </tr>
                                </tfoot>
                            @endif
                    </table>
                </x-ui.mini-table-card>

                {{-- Permits per Purok --}}
                <x-ui.mini-table-card title="Applications per Purok">
                    <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <x-ui.table-th label="Purok" />
                                    <x-ui.table-th label="Count" align="right" />
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($permitsPerPurok as $row)
                                    <x-ui.table-row class="transition-colors">
                                        <x-ui.table-td text-class="text-gray-900" weight-class="font-medium" label="{{ $row->name }}" />
                                        <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($row->permits_count) }}" />
                                    </x-ui.table-row>
                                @empty
                                    <x-ui.table-empty-row colspan="2" cell-class="px-6 py-12 text-center">
                                        <x-ui.empty-state message="No purok data available" icon="location" />
                                    </x-ui.table-empty-row>
                                @endforelse
                            </tbody>
                            @if ($permitsPerPurok->isNotEmpty())
                                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                    <tr>
                                        <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Total" />
                                        <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($permitsPerPurok->sum('permits_count')) }}" />
                                    </tr>
                                </tfoot>
                            @endif
                    </table>
                </x-ui.mini-table-card>
            </div>
        </div>

        {{-- Section: Issue Reports --}}
        <div>
            <x-ui.section-heading title="Issue Reports">
                <x-slot:actions>
                    <a href="{{ route($rp . '.issues.index') }}" class="text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1 no-print">
                        View all issues
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </x-slot:actions>
            </x-ui.section-heading>
            
            {{-- Issue Status Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <x-ui.metric-icon-card
                    label="Total"
                    :value="number_format($issueStats['total'])"
                    wrapper-class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Pending"
                    :value="number_format($issueStats['pending'])"
                    wrapper-class="rounded-2xl bg-amber-50 p-5 shadow-sm ring-1 ring-amber-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 no-print"
                    label-class="text-xs font-medium text-amber-600 uppercase"
                    value-class="text-2xl font-bold text-amber-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="In Progress"
                    :value="number_format($issueStats['in_progress'])"
                    wrapper-class="rounded-2xl bg-blue-50 p-5 shadow-sm ring-1 ring-blue-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 no-print"
                    label-class="text-xs font-medium text-blue-600 uppercase"
                    value-class="text-2xl font-bold text-blue-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Resolved"
                    :value="number_format($issueStats['resolved'])"
                    wrapper-class="rounded-2xl bg-green-50 p-5 shadow-sm ring-1 ring-green-100"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600 no-print"
                    label-class="text-xs font-medium text-green-600 uppercase"
                    value-class="text-2xl font-bold text-green-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
                <x-ui.metric-icon-card
                    label="Closed"
                    :value="number_format($issueStats['closed'])"
                    wrapper-class="rounded-2xl border border-gray-200 bg-gray-100 p-5 shadow-sm"
                    icon-wrapper-class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-200 text-gray-600 no-print"
                    label-class="text-xs font-medium text-gray-600 uppercase"
                    value-class="text-2xl font-bold text-gray-700"
                >
                    <x-slot:icon>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </x-slot:icon>
                </x-ui.metric-icon-card>
            </div>

            {{-- Issue Status Summary Table --}}
            <x-ui.mini-table-card title="Issue Status Breakdown">
                <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-ui.table-th label="Status" />
                                <x-ui.table-th label="Count" align="right" />
                                <x-ui.table-th label="Percentage" align="right" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- Pending --}}
                            @php $pendingPct = $issueStats['total'] > 0 ? round(($issueStats['pending'] / $issueStats['total']) * 100, 1) : 0; @endphp
                            <x-ui.table-row class="transition-colors">
                                <x-ui.table-td size-class="" text-class="">
                                    <x-ui.status-badge class="bg-amber-100 text-amber-800" padding-class="px-3 py-1" font-class="font-medium">
                                        Pending
                                    </x-ui.status-badge>
                                </x-ui.table-td>
                                <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($issueStats['pending']) }}" />
                                <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-500" label="{{ $pendingPct }}%" />
                            </x-ui.table-row>
                            {{-- In Progress --}}
                            @php $progressPct = $issueStats['total'] > 0 ? round(($issueStats['in_progress'] / $issueStats['total']) * 100, 1) : 0; @endphp
                            <x-ui.table-row class="transition-colors">
                                <x-ui.table-td size-class="" text-class="">
                                    <x-ui.status-badge class="bg-blue-100 text-blue-800" padding-class="px-3 py-1" font-class="font-medium">
                                        In Progress
                                    </x-ui.status-badge>
                                </x-ui.table-td>
                                <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($issueStats['in_progress']) }}" />
                                <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-500" label="{{ $progressPct }}%" />
                            </x-ui.table-row>
                            {{-- Resolved --}}
                            @php $resolvedPct = $issueStats['total'] > 0 ? round(($issueStats['resolved'] / $issueStats['total']) * 100, 1) : 0; @endphp
                            <x-ui.table-row class="transition-colors">
                                <x-ui.table-td size-class="" text-class="">
                                    <x-ui.status-badge class="bg-green-100 text-green-800" padding-class="px-3 py-1" font-class="font-medium">
                                        Resolved
                                    </x-ui.status-badge>
                                </x-ui.table-td>
                                <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($issueStats['resolved']) }}" />
                                <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-500" label="{{ $resolvedPct }}%" />
                            </x-ui.table-row>
                            {{-- Closed --}}
                            @php $closedPct = $issueStats['total'] > 0 ? round(($issueStats['closed'] / $issueStats['total']) * 100, 1) : 0; @endphp
                            <x-ui.table-row class="transition-colors">
                                <x-ui.table-td size-class="" text-class="">
                                    <x-ui.status-badge class="bg-gray-100 text-gray-800" padding-class="px-3 py-1" font-class="font-medium">
                                        Closed
                                    </x-ui.status-badge>
                                </x-ui.table-td>
                                <x-ui.table-td align="right" class="tabular-nums" label="{{ number_format($issueStats['closed']) }}" />
                                <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-500" label="{{ $closedPct }}%" />
                            </x-ui.table-row>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                            <tr>
                                <x-ui.table-td text-class="text-gray-900" weight-class="font-bold" label="Total" />
                                <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="{{ number_format($issueStats['total']) }}" />
                                <x-ui.table-td align="right" class="tabular-nums" text-class="text-gray-900" weight-class="font-bold" label="100%" />
                            </tr>
                        </tfoot>
                </table>
            </x-ui.mini-table-card>
        </div>
    </div>
</section>
@endsection

