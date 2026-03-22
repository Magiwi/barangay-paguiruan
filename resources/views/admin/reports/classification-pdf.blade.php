<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Classification Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
<div class="report-sheet">
    @include('admin.reports.partials.print-header', [
        'reportTitle' => 'Classification Reports',
        'generatedAt' => now()->format('M d, Y h:i A'),
        'leftMeta' => [
            'Report Name: Classification',
            'Purok Filter: ' . ($filters['purok'] ?? 'All Puroks'),
        ],
        'rightMeta' => [
            'Total Records: ' . count($records),
        ],
    ])

    <table class="report-table">
        <thead>
        <tr>
            <th style="width:24%;">Purok</th>
            <th style="width:12%;">PWD Total</th>
            <th style="width:12%;">PWD Verified</th>
            <th style="width:12%;">PWD Pending</th>
            <th style="width:13%;">Senior Total</th>
            <th style="width:13%;">Senior Verified</th>
            <th style="width:14%;">Senior Pending</th>
        </tr>
        </thead>
        <tbody>
        @forelse($records as $row)
            <tr>
                <td>{{ $row['purok'] }}</td>
                <td>{{ $row['pwd_total'] }}</td>
                <td>{{ $row['pwd_verified'] }}</td>
                <td>{{ $row['pwd_pending'] }}</td>
                <td>{{ $row['senior_total'] }}</td>
                <td>{{ $row['senior_verified'] }}</td>
                <td>{{ $row['senior_pending'] }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty-cell">No records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>

