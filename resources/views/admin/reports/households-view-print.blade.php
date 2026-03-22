@extends($layout ?? 'layouts.admin')

@section('title', 'Household View Print - e-Governance')

@section('content')
@include('admin.reports.partials.print-styles')
<style>
    body { background: #f3f4f6; }
    .print-wrapper { max-width: 980px; margin: 0 auto; padding: 16px; }
    .print-toolbar { margin-bottom: 10px; text-align: right; }
    .print-toolbar button {
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    @media print {
        .print-toolbar { display: none !important; }
        body { background: #fff !important; }
        .print-wrapper { max-width: none; margin: 0; padding: 0; }
        .report-sheet { box-shadow: none !important; border: none !important; padding: 0; }
    }
</style>

<div class="print-wrapper">
    <div class="print-toolbar">
        <button onclick="window.print()">
            Print Report
        </button>
    </div>

    <div class="report-sheet" style="border:1px solid #d1d5db; box-shadow:0 1px 4px rgba(15,23,42,.08);">
        @include('admin.reports.partials.print-header', [
            'reportTitle' => 'Household Reports',
            'generatedAt' => $generatedAt,
            'leftMeta' => [
                'Report Name: ' . ($reportType ?? 'Household'),
                'Scope: ' . ($reportScope ?? 'family_members'),
                'Sort: ' . strtoupper($viewOrder) . ' ' . str_replace('_', ' ', ucfirst($viewSort)),
            ],
            'rightMeta' => [
                'Total Records: ' . number_format($detailTotalRecords),
                'Household Heads: ' . number_format($householdHeadsCount),
            ],
            'filterChips' => $appliedFilters,
        ])

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:58%;">Name</th>
                    <th style="width:24%;">Relationship</th>
                    <th style="width:18%;">House No.</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($groupedRows ?? []) as $group)
                    <tr>
                        <td colspan="3" style="font-weight:700; background:#eef2f7;">
                            {{ $group['house_head'] }} (Head of Family)
                        </td>
                    </tr>
                    @foreach (($group['members'] ?? []) as $member)
                        <tr>
                            <td style="padding-left: 14px;">{{ $member['family_member'] }}</td>
                            <td>{{ $member['relationship'] }}</td>
                            <td>{{ $member['house_no'] }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="3" class="empty-cell">No household records found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
