<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Population Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
<div class="report-sheet">
    @include('admin.reports.partials.print-header', [
        'reportTitle' => 'Population Reports',
        'generatedAt' => now()->format('M d, Y h:i A'),
        'leftMeta' => [
            'Report Name: Population',
            'Purok Filter: ' . ($filters['purok'] ?? 'All Puroks'),
        ],
        'rightMeta' => [
            'Total Records: ' . count($records),
        ],
    ])

    <table class="report-table">
        <thead>
        <tr>
            <th style="width:18%;">Purok</th>
            <th style="width:9%;">Total</th>
            <th style="width:9%;">Active</th>
            <th style="width:10%;">Permanent</th>
            <th style="width:11%;">Non-Permanent</th>
            <th style="width:9%;">Minors</th>
            <th style="width:9%;">Adults</th>
            <th style="width:9%;">Seniors</th>
            <th style="width:8%;">Male</th>
            <th style="width:8%;">Female</th>
        </tr>
        </thead>
        <tbody>
        @forelse($records as $row)
            <tr>
                <td>{{ $row['purok'] }}</td>
                <td>{{ $row['total_residents'] }}</td>
                <td>{{ $row['active_residents'] }}</td>
                <td>{{ $row['permanent'] }}</td>
                <td>{{ $row['non_permanent'] }}</td>
                <td>{{ $row['minors'] }}</td>
                <td>{{ $row['adults'] }}</td>
                <td>{{ $row['seniors'] }}</td>
                <td>{{ $row['male'] }}</td>
                <td>{{ $row['female'] }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="empty-cell">No records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>

