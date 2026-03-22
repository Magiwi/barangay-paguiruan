<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household View Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
    <div class="report-sheet">
        @include('admin.reports.partials.print-header', [
            'reportTitle' => 'Household Reports',
            'generatedAt' => $generatedAt,
            'leftMeta' => [
                'Report Type: ' . ($reportType ?? 'Household View'),
                'Scope: ' . ($reportScope ?? 'Household'),
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
</body>
</html>
