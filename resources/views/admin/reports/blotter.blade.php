@extends($layout ?? 'layouts.admin')

@section('title', 'Blotter Reports - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
    <div class="mx-auto max-w-7xl space-y-6">
        <x-ui.report-topbar
            title="Blotter Reports & Analytics"
            subtitle="Insights, trends, and exportable reports for blotter records."
            :back-url="route($rp . '.reports.index')"
            class="no-print"
        >
            <x-slot:actions>
                <x-ui.export-toolbar
                    :pdf-url="route($rp . '.reports.blotter.export.pdf', request()->query())"
                    :excel-url="route($rp . '.reports.blotter.export.excel', request()->query())"
                    :csv-url="route($rp . '.reports.blotter.export.csv', request()->query())"
                    filter-label="Exports include current filters"
                    :filter-value="'From: ' . (request('from') ?: 'Any') . ' | To: ' . (request('to') ?: 'Any') . ' | Status: ' . (request('status') ?: 'All')"
                />
            </x-slot:actions>
        </x-ui.report-topbar>

        <x-ui.report-filter-bar
            :action="route($rp . '.reports.blotter')"
            :reset-url="route($rp . '.reports.blotter')"
            fields-class="grid grid-cols-1 gap-4 md:grid-cols-5"
            submit-label="Apply Filters"
            submit-class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
        >
                <div>
                    <label for="from" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">From</label>
                    <input id="from" name="from" type="date" value="{{ request('from') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label for="to" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">To</label>
                    <input id="to" name="to" type="date" value="{{ request('to') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All Statuses</option>
                        @foreach (['pending' => 'Pending', 'ongoing' => 'Ongoing', 'resolved' => 'Resolved'] as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="complaint_type" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Complaint Type</label>
                    <select id="complaint_type" name="complaint_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All Types</option>
                        @foreach ($complaintTypeOptions as $option)
                            <option value="{{ $option }}" @selected(request('complaint_type') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Search</label>
                    <input id="search" name="search" type="text" value="{{ request('search') }}" placeholder="Case, complainant, respondent..."
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
        </x-ui.report-filter-bar>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stats-card
                label="Total Blotter Cases"
                :value="number_format($totalCases)"
                wrapper-class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-red-700"
            />
            <x-ui.stats-card
                label="Pending Cases"
                :value="number_format($pendingCases)"
                wrapper-class="rounded-2xl border border-yellow-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-yellow-700"
            />
            <x-ui.stats-card
                label="Ongoing Cases"
                :value="number_format($ongoingCases)"
                wrapper-class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-blue-700"
            />
            <x-ui.stats-card
                label="Resolved Cases"
                :value="number_format($resolvedCases)"
                wrapper-class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm"
                value-class="mt-1 text-3xl font-bold text-green-700"
            />
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <x-ui.chart-panel title="Blotter Cases Over Time" wrapper-class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
                <canvas id="monthlyCasesChart"></canvas>
            </x-ui.chart-panel>
            <x-ui.chart-panel title="Status Distribution">
                <canvas id="statusChart"></canvas>
            </x-ui.chart-panel>
        </div>

        <x-ui.chart-panel title="Complaint Categories">
            <canvas id="categoryChart"></canvas>
        </x-ui.chart-panel>

        <x-ui.report-table-card title="Filtered Case Records">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            @php
                                $sort = request('sort', 'created_at');
                                $direction = request('direction', 'desc');
                                $nextDirection = fn ($column) => $sort === $column && $direction === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <x-ui.table-th padding-class="px-5 py-3">
                                <a class="hover:text-gray-900" href="{{ route($rp . '.reports.blotter', array_merge(request()->query(), ['sort' => 'case_number', 'direction' => $nextDirection('case_number')])) }}">Case Number</a>
                            </x-ui.table-th>
                            <x-ui.table-th padding-class="px-5 py-3">
                                <a class="hover:text-gray-900" href="{{ route($rp . '.reports.blotter', array_merge(request()->query(), ['sort' => 'complainant_name', 'direction' => $nextDirection('complainant_name')])) }}">Complainant</a>
                            </x-ui.table-th>
                            <x-ui.table-th padding-class="px-5 py-3">
                                <a class="hover:text-gray-900" href="{{ route($rp . '.reports.blotter', array_merge(request()->query(), ['sort' => 'respondent_name', 'direction' => $nextDirection('respondent_name')])) }}">Respondent</a>
                            </x-ui.table-th>
                            <x-ui.table-th padding-class="px-5 py-3">
                                <a class="hover:text-gray-900" href="{{ route($rp . '.reports.blotter', array_merge(request()->query(), ['sort' => 'complaint_type', 'direction' => $nextDirection('complaint_type')])) }}">Complaint Type</a>
                            </x-ui.table-th>
                            <x-ui.table-th padding-class="px-5 py-3">
                                <a class="hover:text-gray-900" href="{{ route($rp . '.reports.blotter', array_merge(request()->query(), ['sort' => 'status', 'direction' => $nextDirection('status')])) }}">Status</a>
                            </x-ui.table-th>
                            <x-ui.table-th padding-class="px-5 py-3">
                                <a class="hover:text-gray-900" href="{{ route($rp . '.reports.blotter', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $nextDirection('created_at')])) }}">Date Filed</a>
                            </x-ui.table-th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($rows as $row)
                            <x-ui.table-row>
                                <x-ui.table-td class="whitespace-nowrap" padding-class="px-5 py-3" text-class="text-gray-900" weight-class="font-medium" label="{{ $row->case_number ?: $row->blotter_number }}" />
                                <x-ui.table-td class="whitespace-nowrap" padding-class="px-5 py-3" label="{{ $row->complainant_name ?: '—' }}" />
                                <x-ui.table-td class="whitespace-nowrap" padding-class="px-5 py-3" label="{{ $row->respondent_name ?: 'N/A' }}" />
                                <x-ui.table-td class="whitespace-nowrap" padding-class="px-5 py-3" label="{{ $row->complaint_type ?: 'Others' }}" />
                                <x-ui.table-td class="whitespace-nowrap" padding-class="px-5 py-3" size-class="" text-class="">
                                    @php
                                        $statusClass = match ($row->status) {
                                            'resolved' => 'bg-green-100 text-green-700',
                                            'ongoing' => 'bg-blue-100 text-blue-700',
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <x-ui.status-badge class="{{ $statusClass }}">
                                        {{ ucfirst($row->status ?? 'pending') }}
                                    </x-ui.status-badge>
                                </x-ui.table-td>
                                <x-ui.table-td class="whitespace-nowrap" padding-class="px-5 py-3" label="{{ optional($row->created_at)->format('M d, Y') }}" />
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-empty-row colspan="6" cell-class="px-5 py-12 text-center text-sm text-gray-500">
                                <x-ui.empty-state message="No blotter records found for the selected filters." text-class="text-sm text-gray-500" />
                            </x-ui.table-empty-row>
                        @endforelse
                    </tbody>
            </table>
            <x-slot:footer>
                {{ $rows->links() }}
            </x-slot:footer>
        </x-ui.report-table-card>
    </div>
</section>
@endsection

<x-ui.chart-script />

@push('scripts')
    <script>
        const monthLabels = @json($monthLabels);
        const monthlySeries = @json($monthlySeries);
        const statusDistribution = @json($statusDistribution);
        const complaintCategories = @json($complaintCategories);

        window.reportCreateChart('monthlyCasesChart', {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Cases',
                    data: monthlySeries,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        window.reportCreateChart('statusChart', {
            type: 'pie',
            data: {
                labels: ['Pending', 'Ongoing', 'Resolved'],
                datasets: [{
                    data: [statusDistribution.pending, statusDistribution.ongoing, statusDistribution.resolved],
                    backgroundColor: ['#f59e0b', '#2563eb', '#16a34a']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        window.reportCreateChart('categoryChart', {
            type: 'bar',
            data: {
                labels: ['Noise', 'Physical', 'Financial', 'Property', 'Others'],
                datasets: [{
                    data: [
                        complaintCategories.noise,
                        complaintCategories.physical,
                        complaintCategories.financial,
                        complaintCategories.property,
                        complaintCategories.others
                    ],
                    backgroundColor: ['#ef4444', '#f97316', '#0ea5e9', '#22c55e', '#6b7280']
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
