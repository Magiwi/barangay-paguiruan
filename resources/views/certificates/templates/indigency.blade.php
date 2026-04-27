<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Indigency</title>
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

        .frame {
            border: 2px solid #111;
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .col-left, .col-right {
            display: table-cell;
            vertical-align: top;
        }
        .col-left {
            width: 34%;
            border-right: 2px solid #111;
            padding: 12px 10px 14px;
            text-align: center;
        }
        .col-right {
            width: 66%;
            padding: 18px 14px 20px;
        }

        .official { margin: 0 0 8px; }
        .official .name { font-size: 18px; font-weight: 700; text-decoration: underline; line-height: 1.05; }
        .official .role { font-size: 15px; font-style: italic; line-height: 1.15; margin-top: 2px; }
        .dash { margin: 2px 0 8px; font-weight: 700; }
        .seal-note { margin-top: 12px; font-size: 13px; font-style: italic; font-weight: 700; color: #641c37; }

        .doc-title {
            text-align: center;
            margin: 6px 0 22px;
            color: #1e4f9c;
            text-transform: uppercase;
            font-size: 48px;
            font-weight: 700;
            text-decoration: underline;
            line-height: 1.05;
        }
        .section-label { font-size: 18px; font-weight: 700; margin-bottom: 16px; }
        .paragraph { margin: 0 0 14px; font-size: 17px; text-align: left; line-height: 1.32; }
        .strong { font-weight: 700; font-style: italic; }
        .currency { font-family: "DejaVu Sans", sans-serif; font-style: normal; }
        .issued-block { margin-top: 24px; }
        .signature { text-align: center; margin-top: 44px; }
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

    $user = $certificate->user;
    $requestForType = (string) data_get($certificate->extra_fields, 'request_for_type', 'self');
    $requestedName = trim((string) data_get($certificate->extra_fields, 'request_for_name', ''));
    $requestForAddress = trim((string) data_get($certificate->extra_fields, 'request_for_address', ''));
    $profileName = trim(($user?->first_name ?? '') . ' ' . ($user?->middle_name ?? '') . ' ' . ($user?->last_name ?? ''));
    $residentName = trim((string) (
        $certificate->certificate_name_override
        ?: ($requestForType === 'minor_family_member' && $requestedName !== '' ? $requestedName : $profileName)
    )) ?: '________________________';
    $baseAddress = trim((string) ($user?->address ?? ''));
    if ($baseAddress === '') {
        $purokLabel = trim((string) ($user?->purokRelation?->name ?? ''));
        $normalizedPurok = $purokLabel !== '' && preg_match('/^purok\b/i', $purokLabel) !== 1
            ? 'Purok ' . $purokLabel
            : $purokLabel;
        $baseAddress = trim(implode(', ', array_filter([
            trim((string) ($user?->house_no ?? '')),
            $normalizedPurok,
        ])));
    }
    $targetAddress = $requestForType === 'minor_family_member' && $requestForAddress !== ''
        ? $requestForAddress
        : $baseAddress;
    $residentAddress = trim((string) ($certificate->certificate_address_override ?: $targetAddress)) ?: '________________________';
    $purpose = trim((string) $certificate->purpose) !== '' ? $certificate->purpose : 'assistance purposes';
    $incomeBracketRaw = trim((string) data_get($certificate->extra_fields, 'monthly_income', 'No Income'));
    $incomeBracketNormalized = preg_replace('/\s+/', ' ', $incomeBracketRaw) ?? $incomeBracketRaw;
    $incomeBracketNormalized = str_replace('?', '₱', $incomeBracketNormalized);
    if (! str_contains($incomeBracketNormalized, '₱') && preg_match('/^(\d[\d,]*)\s*[–-]\s*(\d[\d,]*)$/u', $incomeBracketNormalized, $matches) === 1) {
        $incomeBracketEscaped = '&#8369;' . e($matches[1]) . ' – &#8369;' . e($matches[2]);
    } elseif (! str_contains($incomeBracketNormalized, '₱') && preg_match('/^\d[\d,]*$/', $incomeBracketNormalized) === 1) {
        $incomeBracketEscaped = '&#8369;' . e($incomeBracketNormalized);
    } else {
        $incomeBracketEscaped = str_replace('₱', '&#8369;', e($incomeBracketNormalized));
    }

    $issuedOn = $certificate->certificate_issued_on ?: now();
    $carbonDate = \Illuminate\Support\Carbon::parse($issuedOn);
    $day = $carbonDate->format('j');
    $year = $carbonDate->format('Y');
@endphp

<div class="header">
    <div class="logo-left">@if($leftLogo)<img src="{{ $leftLogo }}" alt="Left Logo">@endif</div>
    <div class="logo-right">@if($rightLogo)<img src="{{ $rightLogo }}" alt="Right Logo">@endif</div>
    @include('partials.document-pdf-header-lines')
</div>

<div class="frame">
    <div class="col-left">
        @include('partials.document-pdf-officials-left-column', ['officialsPdf' => $officialsPdf])
        @include('partials.document-pdf-seal-note')
    </div>

    <div class="col-right">
        <div class="doc-title">CERTIFICATE OF<br>INDIGENCY</div>
        <div class="section-label">TO WHOM IT MAY CONCERN:</div>

        <p class="paragraph">
            <span class="strong">This is to CERTIFY</span> that, <span class="strong">{{ $residentName }}</span>, of legal age, Filipino,
            is a bonafide resident of {{ $residentAddress }}, {{ \App\Models\SiteSetting::getValue('doc_jurisdiction_short') }}.
        </p>

        <p class="paragraph">
            <span class="strong">This is to CERTIFY further</span>, that the above-named person belongs and is a part of the
            <span class="strong">INDIGENT FAMILIES</span> residing in {{ \App\Models\SiteSetting::getValue('doc_jurisdiction_short') }}.
        </p>

        <p class="paragraph">
            This certification is being issued upon the request of the above-named person for
            <span class="strong">{{ $purpose }}</span> purposes only.
        </p>

        <p class="paragraph">
            Declared Monthly Income: <span class="strong currency">{!! $incomeBracketEscaped !!}</span>.
        </p>

        <p class="paragraph issued-block">
            Given this <span class="strong">{{ $day }}</span> day of <span class="strong">{{ $year }}</span>,
            {{ \App\Models\SiteSetting::getValue('doc_issued_at_suffix') }}
        </p>

        @include('partials.document-pdf-signature-punong-barangay', ['officialsPdf' => $officialsPdf])
    </div>
</div>
</body>
</html>
