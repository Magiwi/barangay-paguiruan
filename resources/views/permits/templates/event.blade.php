<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Permit</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: "Times New Roman", serif; color: #111; font-size: 14px; line-height: 1.35; }
        .header { position: relative; text-align: center; margin-bottom: 10px; min-height: 86px; }
        .logo-left, .logo-right { position: absolute; top: 0; width: 78px; height: 78px; }
        .logo-left { left: 0; }
        .logo-right { right: 0; }
        .logo-left img, .logo-right img { width: 78px; height: 78px; object-fit: contain; }
        .gov-line { margin: 0; font-weight: 700; line-height: 1.2; }
        .gov-sm { font-size: 20px; }
        .gov-md { font-size: 22px; text-transform: uppercase; }
        .gov-lg { font-size: 42px; text-transform: uppercase; letter-spacing: .3px; }
        .office-line { margin: 4px 0 0; font-size: 20px; font-weight: 700; text-transform: uppercase; }
        .frame { border: 2px solid #111; display: table; width: 100%; table-layout: fixed; }
        .col-left, .col-right { display: table-cell; vertical-align: top; }
        .col-left { width: 34%; border-right: 2px solid #111; padding: 12px 10px 14px; text-align: center; }
        .col-right { width: 66%; padding: 18px 14px 20px; }
        .official { margin: 0 0 8px; }
        .official .name { font-size: 18px; font-weight: 700; text-decoration: underline; line-height: 1.05; }
        .official .role { font-size: 15px; font-style: italic; line-height: 1.15; margin-top: 2px; }
        .dash { margin: 2px 0 8px; font-weight: 700; }
        .seal-note { margin-top: 12px; font-size: 13px; font-style: italic; font-weight: 700; color: #641c37; }
        .meta { text-align: right; font-size: 13px; margin-bottom: 10px; line-height: 1.25; }
        .doc-title { text-align: center; margin: 6px 0 22px; color: #1e4f9c; text-transform: uppercase; font-size: 46px; font-weight: 700; text-decoration: underline; line-height: 1.05; }
        .section-label { font-size: 18px; font-weight: 700; margin-bottom: 12px; }
        .paragraph { margin: 0 0 12px; font-size: 16px; text-align: justify; line-height: 1.3; }
        .strong { font-weight: 700; }
        .info-box { border: 1px solid #777; padding: 8px 10px; margin: 14px 0; }
        .info-row { margin: 0 0 6px; font-size: 15px; line-height: 1.25; }
        .info-row:last-child { margin-bottom: 0; }
        .signature { text-align: center; margin-top: 42px; }
        .signature .name { font-size: 19px; font-weight: 700; text-decoration: underline; text-transform: uppercase; }
        .signature .role { font-size: 18px; font-style: italic; margin-top: 3px; }
    </style>
</head>
<body>
@php
    $leftLogoPath = public_path('images/logo1.png');
    $rightLogoPath = public_path('images/logo2.png');
    $leftLogo = file_exists($leftLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($leftLogoPath)) : null;
    $rightLogo = file_exists($rightLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($rightLogoPath)) : null;

    $applicant = $permit->applicant;
    $extra = is_array($permit->extra_fields) ? $permit->extra_fields : [];
    $clean = static function (?string $value): string {
        $normalized = trim((string) $value);
        return in_array(strtolower($normalized), ['', 'n/a', 'na', '-', '—'], true) ? '' : $normalized;
    };
    $applicantName = trim(($applicant?->first_name ?? '') . ' ' . ($applicant?->middle_name ?? '') . ' ' . ($applicant?->last_name ?? '')) ?: '________________________';
    $houseNo = $clean((string) ($applicant?->house_no ?? ''));
    $purokRaw = $clean((string) ($applicant?->purokRelation?->name ?? $applicant?->purok ?? ''));
    $purokName = $purokRaw !== '' && preg_match('/^purok\b/i', $purokRaw) !== 1 ? 'Purok ' . $purokRaw : $purokRaw;
    $applicantAddress = trim(implode(', ', array_filter([$houseNo, $purokName]))) ?: '________________________';
    $eventName = trim((string) data_get($extra, 'event_name', '')) ?: '________________________';
    $eventDateRaw = data_get($extra, 'event_date');
    $eventDate = $eventDateRaw ? \Illuminate\Support\Carbon::parse($eventDateRaw)->format('F j, Y') : '________________';
    $purpose = trim((string) $permit->purpose) !== '' ? $permit->purpose : 'event processing';
    $issuedOn = $permit->released_at ?? now();
    $date = \Illuminate\Support\Carbon::parse($issuedOn);
@endphp

<div class="header">
    <div class="logo-left">@if($leftLogo)<img src="{{ $leftLogo }}" alt="Left Logo">@endif</div>
    <div class="logo-right">@if($rightLogo)<img src="{{ $rightLogo }}" alt="Right Logo">@endif</div>
    <p class="gov-line gov-sm">Republic of the Philippines</p>
    <p class="gov-line gov-md">Province of Pampanga</p>
    <p class="gov-line gov-md">MUNICIPALITY OF FLORIDABLANCA</p>
    <p class="gov-line gov-lg">BARANGAY PAGUIRUAN</p>
    <p class="office-line">OFFICE OF THE PUNONG BARANGAY</p>
</div>

<div class="frame">
    <div class="col-left">
        <div class="official"><div class="name">Hon. Jose C. Basa</div><div class="role">Punong Barangay</div></div>
        <div class="dash">- Kagawad -</div>
        <div class="official"><div class="name">Rex R. Rodil</div><div class="role">Committee on Environment</div></div>
        <div class="official"><div class="name">Ronnie P. Medina</div><div class="role">Committee on Transportation</div></div>
        <div class="official"><div class="name">Jim Arthur P. Santos</div><div class="role">Committee on Appropriation and Agriculture</div></div>
        <div class="official"><div class="name">Edward R. Naquiat</div><div class="role">Committee on Peace and Order</div></div>
        <div class="official"><div class="name">Arnold I. Alfaro</div><div class="role">Committee on Public Works</div></div>
        <div class="official"><div class="name">Georgina R. Baul</div><div class="role">Committee on Education</div></div>
        <div class="official"><div class="name">Robin B. Almario</div><div class="role">Committee on Health</div></div>
        <div class="official"><div class="name">Diether M. Santos</div><div class="role">SK Chairman<br>Committee on Sports, Youth, and Development</div></div>
        <div class="official"><div class="name">Jalvin M. Rodil</div><div class="role">Barangay Treasurer</div></div>
        <div class="official"><div class="name">Nadine S. Castor</div><div class="role">Barangay Secretary</div></div>
        <div class="seal-note">*NOT VALID WITHOUT SEAL*</div>
    </div>

    <div class="col-right">
        <div class="meta">
            Permit No.: <span class="strong">{{ str_pad((string) $permit->id, 5, '0', STR_PAD_LEFT) }}</span><br>
            Date Issued: <span class="strong">{{ $date->format('F j, Y') }}</span>
        </div>
        <div class="doc-title">EVENT<br>PERMIT</div>
        <div class="section-label">TO WHOM IT MAY CONCERN:</div>

        <p class="paragraph">
            This is to certify that <span class="strong">{{ $applicantName }}</span>, residing at
            <span class="strong">{{ $applicantAddress }}</span>, requested an event permit.
        </p>

        <div class="info-box">
            <p class="info-row"><span class="strong">Event Name:</span> {{ $eventName }}</p>
            <p class="info-row"><span class="strong">Event Date:</span> {{ $eventDate }}</p>
            <p class="info-row"><span class="strong">Purpose:</span> {{ $purpose }}</p>
        </div>

        <p class="paragraph">
            This document is issued for official barangay verification and processing purposes only.
        </p>

        <div class="signature">
            <div class="name">HON. JOSE C. BASA</div>
            <div class="role">Punong Barangay</div>
        </div>
    </div>
</div>
</body>
</html>
