<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summon - {{ data_get($blotter, 'case_number') ?: data_get($blotter, 'blotter_number') ?: 'Case' }}</title>
    <style>
        @page { margin: 22mm 18mm 20mm 18mm; }
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.45;
            margin: 0;
        }
        .document {
            position: relative;
            width: 100%;
        }
        .logo-left, .logo-right {
            position: absolute;
            top: 0;
            width: 86px;
            height: 86px;
            object-fit: contain;
        }
        .logo-left { left: 0; }
        .logo-right { right: 0; }
        .header {
            text-align: center;
            padding: 0 100px;
            margin-bottom: 8px;
        }
        .header .small {
            font-size: 12px;
            font-weight: 600;
            margin: 0 0 2px;
        }
        .header .big {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.2px;
            margin: 0 0 2px;
        }
        .header .barangay {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin: 1px 0 0;
        }
        .line {
            border-top: 1px solid #111;
            margin-top: 8px;
        }
        .office-title {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin: 14px 0 14px;
        }
        .top-grid {
            width: 100%;
            margin-top: 2px;
            margin-bottom: 10px;
        }
        .top-grid td {
            vertical-align: top;
            width: 50%;
            font-size: 11px;
        }
        .field-block { margin-bottom: 10px; }
        .label { font-weight: 700; }
        .blank-line {
            display: inline-block;
            min-width: 190px;
            border-bottom: 1px solid #111;
            height: 16px;
            line-height: 16px;
            vertical-align: baseline;
        }
        .blank-line.wide { min-width: 260px; }
        .blank-line.short { min-width: 120px; }
        .summon-title {
            text-align: center;
            margin: 14px 0 16px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 1.2px;
        }
        p { margin: 0 0 8px; }
        .to-line {
            margin-bottom: 12px;
            font-size: 11px;
        }
        .body-text {
            text-align: justify;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 11px;
            line-height: 1.55;
        }
        .strong { font-weight: 700; }
        .signature {
            margin-top: 34px;
            text-align: right;
        }
        .signature-name {
            font-size: 11px;
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .signature-role {
            font-size: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
@php
    $leftLogoPath = public_path('images/logo1.png');
    $rightLogoPath = public_path('images/logo2.png');
    $leftLogo = is_file($leftLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($leftLogoPath)) : null;
    $rightLogo = is_file($rightLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($rightLogoPath)) : null;

    $complainant = trim((string) data_get($blotter, 'complainant_name'));
    $respondent = trim((string) data_get($blotter, 'respondent_display_name'));
    if ($respondent === '' || $respondent === '—' || $respondent === '-') {
        $respondent = 'N/A';
    }
    $caseNumber = trim((string) (data_get($blotter, 'case_number') ?: data_get($blotter, 'blotter_number')));
    $complaintType = trim((string) data_get($blotter, 'complaint_type'));
    if ($complaintType === '' || $complaintType === '—' || $complaintType === '-') {
        $remarksSource = (string) data_get($blotter, 'remarks', '');
        if (preg_match('/^\s*(?:Complaint\s+Type|For)\s*:\s*(.+)\s*$/mi', $remarksSource, $matches) === 1) {
            $complaintType = trim((string) ($matches[1] ?? ''));
        }
    }
    if ($complaintType === '' || $complaintType === '—' || $complaintType === '-') {
        $complaintType = 'N/A';
    }
    $hearingDate = data_get($summon, 'hearing_date')
        ? \Illuminate\Support\Carbon::parse(data_get($summon, 'hearing_date'))->format('F d, Y')
        : null;
    $hearingTime = data_get($summon, 'hearing_time')
        ? \Illuminate\Support\Carbon::createFromFormat('H:i:s', strlen((string) data_get($summon, 'hearing_time')) === 5 ? data_get($summon, 'hearing_time') . ':00' : data_get($summon, 'hearing_time'))->format('h:i A')
        : null;
    $dayNow = now()->format('d');
    $monthNow = now()->format('F');
    $yearNow = now()->format('Y');
@endphp

<div class="document">
    @if ($leftLogo)
        <img src="{{ $leftLogo }}" alt="Barangay Logo" class="logo-left">
    @endif
    @if ($rightLogo)
        <img src="{{ $rightLogo }}" alt="Municipality Logo" class="logo-right">
    @endif

    <div class="header">
        <div class="small">Republic of the Philippines</div>
        <div class="small">Province of Pampanga</div>
        <div class="small">Municipality of Floridablanca</div>
        <div class="barangay">BARANGAY PAGUIRUAN</div>
    </div>

    <div class="line"></div>

    <div class="office-title">OFFICE OF THE LUPONG TAGAPAMAYAPA</div>

    <table class="top-grid" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="field-block">
                    <span class="label">Complainants</span><br>
                    <span class="blank-line wide">{{ $complainant ?: 'N/A' }}</span>
                </div>
                <div class="field-block">
                    <span class="label">Respondent/s</span><br>
                    <span class="blank-line wide">{{ $respondent }}</span>
                </div>
            </td>
            <td>
                <div class="field-block">
                    <span class="label">Barangay Case No.</span>
                    <span class="blank-line">{{ $caseNumber ?: 'N/A' }}</span>
                </div>
                <div class="field-block">
                    <span class="label">FOR:</span>
                    <span class="blank-line wide">{{ $complaintType }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="summon-title">SUMMON</div>

    <div class="to-line">
        <span class="label">To:</span>
        <span class="blank-line wide">{{ $respondent }}</span>
    </div>

    <p class="body-text">
        You are hereby summoned to appear before me in person, together with your witnesses on the
        <span class="blank-line">{{ $hearingDate ?: '__________' }}</span>
        at
        <span class="blank-line short">{{ $hearingTime ?: '__________' }}</span>
        then and there to answer a complaint made before me, copy of which is hereto attached, for mediation/conciliation
        of your dispute with complainant/s.
    </p>

    <p class="body-text">
        You are hereby warned that if you refuse or willfully fail to appear in obedience to this summon,
        you may be barred from filing any counterclaim arising from said complaint.
    </p>

    <p class="body-text">
        <span class="strong">FAIL NOT OR ELSE</span> face punishment as for contempt of court.
    </p>

    <p class="body-text" style="margin-top: 24px;">
        THIS
        <span class="blank-line short">{{ $dayNow ?: '___' }}</span>
        day of
        <span class="blank-line short">{{ $monthNow ?: '__________' }}</span>
        {{ $yearNow ?: '____' }}.
    </p>

    <div class="signature">
        <div class="signature-name">HON. JOSE C. BASA</div>
        <div class="signature-role">Punong Barangay/Lupon Chairman</div>
    </div>
</div>
</body>
</html>
