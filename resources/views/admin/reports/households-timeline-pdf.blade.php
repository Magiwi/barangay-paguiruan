<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Timeline Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
<div class="report-sheet">
    @include('admin.reports.partials.print-header', [
        'reportTitle' => 'Household Reports',
        'generatedAt' => now()->format('M d, Y h:i A'),
        'leftMeta' => [
            'Report Name: Household Timeline',
            'Head ID: ' . ($filters['head_id'] ?: 'All'),
            'Action: ' . ($filters['action'] ?: 'All'),
        ],
        'rightMeta' => [
            'Date From: ' . ($filters['date_from'] ?: 'N/A'),
            'Date To: ' . ($filters['date_to'] ?: 'N/A'),
            'Total Records: ' . count($records),
        ],
        'filterChips' => [
            'Search: ' . ($filters['search'] ?: 'N/A'),
        ],
    ])

    <table class="report-table">
        <thead>
        <tr>
            <th style="width: 18%;">Date</th>
            <th style="width: 18%;">Action</th>
            <th style="width: 16%;">Performed By</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        @forelse($records as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['action'] }}</td>
                <td>{{ $row['performed_by'] }}</td>
                <td>{{ $row['description'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="empty-cell">No timeline records found for selected filters.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
