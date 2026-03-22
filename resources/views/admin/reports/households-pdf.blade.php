<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Report</title>
    @include('admin.reports.partials.print-styles')
</head>
<body>
    <div class="report-sheet">
        @include('admin.reports.partials.print-header', [
            'reportTitle' => 'Household Reports',
            'generatedAt' => now()->format('M d, Y h:i A'),
            'leftMeta' => [
                'Report Name: Household',
                'Purok Filter: ' . ($filters['purok'] ?: 'All Puroks'),
            ],
            'rightMeta' => [
                'Total Records: ' . count($records),
            ],
        ])

        <div>
            @forelse($records as $record)
                <div style="text-align:left; vertical-align:top; margin: 8px 0 12px 0;">
                    <div style="font-weight:700; text-transform:uppercase;">
                        Head of Family: {{ $record['head_name_upper'] ?? strtoupper($record['head_name']) }}
                    </div>
                    <div>Email: {{ $record['email'] }}</div>
                    <div>Purok: {{ $record['purok'] }}</div>
                    <div>Resident Type: {{ $record['resident_type'] }}</div>
                    <div>Status: {{ $record['status'] }}</div>
                    <div>Registered At: {{ $record['registered_at'] }}</div>
                    <div style="margin-top:6px; font-weight:600;">Members:</div>
                    @php
                        $members = $record['members_list'] ?? [];
                    @endphp
                    @if (! empty($members))
                        @foreach ($members as $memberName)
                            <div style="padding-left:10px;">- {{ $memberName }}</div>
                        @endforeach
                    @else
                        <div style="padding-left:10px;">(No members linked)</div>
                    @endif
                </div>
                @if (! $loop->last)
                    <hr style="border:0; border-top:1px solid #d1d5db; margin: 10px 0 14px 0;">
                @endif
            @empty
                <div class="empty-cell">No household records found.</div>
            @endforelse
        </div>
    </div>
</body>
</html>
