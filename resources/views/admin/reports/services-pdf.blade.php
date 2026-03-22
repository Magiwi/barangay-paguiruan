<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Services Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
<div class="report-sheet">
    @include('admin.reports.partials.print-header', [
        'reportTitle' => 'Services Reports',
        'generatedAt' => now()->format('M d, Y h:i A'),
        'leftMeta' => [
            'Report Name: Services',
            'Purok Filter: ' . ($filters['purok'] ?? 'All Puroks'),
        ],
        'rightMeta' => [
            'Total Records: ' . count($records),
        ],
    ])

    <table class="report-table">
        <thead>
        <tr>
            <th style="width:16%;">Purok</th>
            <th style="width:8%;">Cert Total</th>
            <th style="width:8%;">Cert Pending</th>
            <th style="width:8%;">Permit Total</th>
            <th style="width:8%;">Permit Pending</th>
            <th style="width:8%;">Issue Total</th>
            <th style="width:9%;">Issue Pending</th>
            <th style="width:12%;">Issue In Progress</th>
            <th style="width:11%;">Issue Resolved</th>
            <th style="width:12%;">Issue Closed</th>
        </tr>
        </thead>
        <tbody>
        @forelse($records as $row)
            <tr>
                <td>{{ $row['purok'] }}</td>
                <td>{{ $row['cert_total'] }}</td>
                <td>{{ $row['cert_pending'] }}</td>
                <td>{{ $row['permit_total'] }}</td>
                <td>{{ $row['permit_pending'] }}</td>
                <td>{{ $row['issue_total'] }}</td>
                <td>{{ $row['issue_pending'] }}</td>
                <td>{{ $row['issue_in_progress'] }}</td>
                <td>{{ $row['issue_resolved'] }}</td>
                <td>{{ $row['issue_closed'] }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="empty-cell">No records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>

