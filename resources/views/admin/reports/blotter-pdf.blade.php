<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blotter Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
    <div class="report-sheet">
        @include('admin.reports.partials.print-header', [
            'reportTitle' => 'Blotter Reports',
            'generatedAt' => now()->format('M d, Y h:i A'),
            'leftMeta' => [
                'Report Name: Blotter',
                'Date Range: ' . (($filters['from'] ?: 'Start') . ' to ' . ($filters['to'] ?: 'Present')),
            ],
            'rightMeta' => [
                'Status: ' . ($filters['status'] ?: 'All'),
                'Complaint Type: ' . ($filters['complaint_type'] ?: 'All'),
                'Total Records: ' . count($records),
            ],
            'filterChips' => [
                'Search: ' . ($filters['search'] ?: 'None'),
            ],
        ])

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:14%;">Case Number</th>
                    <th style="width:20%;">Complainant</th>
                    <th style="width:20%;">Respondent</th>
                    <th style="width:16%;">Complaint Type</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:20%;">Date Filed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record['case_number'] }}</td>
                        <td>{{ $record['complainant_name'] }}</td>
                        <td>{{ $record['respondent_name'] }}</td>
                        <td>{{ $record['complaint_type'] }}</td>
                        <td>{{ ucfirst($record['status']) }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($record['created_at'])->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-cell">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
