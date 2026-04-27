@php
    $reportTitle = $reportTitle ?? 'REPORT';
    $generatedAt = $generatedAt ?? now()->format('M d, Y h:i A');
    $leftMeta = $leftMeta ?? [];
    $rightMeta = $rightMeta ?? [];
    $filterChips = $filterChips ?? [];

    $logo1Path = public_path('images/logo1.png');
    $logo2Path = public_path('images/logo2.png');
    $logo3Path = public_path('images/logo3.png');

    $logo1 = file_exists($logo1Path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo1Path)) : null;
    $logo2 = file_exists($logo2Path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo2Path)) : null;
    $logo3 = file_exists($logo3Path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo3Path)) : null;
@endphp

<div class="report-header">
    <div class="header-top">
        <div class="header-left-logo">
            @if ($logo1)
                <img src="{{ $logo1 }}" alt="Barangay logo">
            @endif
        </div>
        <div class="header-office">
            <div class="office-line-1">{{ \App\Models\SiteSetting::getValue('doc_header_line_1') }}</div>
            <div class="office-line-2">{{ \App\Models\SiteSetting::getValue('doc_header_line_2') }}</div>
            <div class="office-line-3">{{ \App\Models\SiteSetting::getValue('doc_header_line_3') }}</div>
            <div class="office-line-4">{{ \App\Models\SiteSetting::getValue('doc_header_line_4') }}</div>
        </div>
        <div class="header-right-logos">
            @if ($logo2)
                <img src="{{ $logo2 }}" alt="Seal 1">
            @endif
            @if ($logo3)
                <img src="{{ $logo3 }}" alt="Seal 2">
            @endif
        </div>
    </div>
    <div class="header-divider"></div>
    <div class="report-title">{{ strtoupper($reportTitle) }}</div>

    <div class="report-meta">
        <table class="report-meta-table">
            <tr>
                <td class="report-meta-left">
                    <div class="report-meta-item"><strong>Generated:</strong> {{ $generatedAt }}</div>
                    @foreach ($leftMeta as $meta)
                        <div class="report-meta-item">{{ $meta }}</div>
                    @endforeach
                </td>
                <td class="report-meta-right">
                    @foreach ($rightMeta as $meta)
                        <div class="report-meta-item">{{ $meta }}</div>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    @if (!empty($filterChips))
        <div class="filter-chips">
            @foreach ($filterChips as $chip)
                <span class="filter-chip">{{ $chip }}</span>
            @endforeach
        </div>
    @endif
</div>
