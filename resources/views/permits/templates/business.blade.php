<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Permit</title>
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

        .meta {
            text-align: right;
            font-size: 13px;
            margin-bottom: 10px;
            line-height: 1.25;
        }
        .doc-title {
            text-align: center;
            margin: 6px 0 22px;
            color: #1e4f9c;
            text-transform: uppercase;
            font-size: 46px;
            font-weight: 700;
            text-decoration: underline;
            line-height: 1.05;
        }
        .section-label { font-size: 18px; font-weight: 700; margin-bottom: 12px; }
        .paragraph { margin: 0 0 12px; font-size: 16px; text-align: justify; line-height: 1.3; }
        .strong { font-weight: 700; }

        .info-box {
            border: 1px solid #777;
            padding: 8px 10px;
            margin: 14px 0;
        }
        .info-row {
            margin: 0 0 6px;
            font-size: 15px;
            line-height: 1.25;
        }
        .info-row:last-child { margin-bottom: 0; }

        .notes-title {
            margin-top: 12px;
            margin-bottom: 6px;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }

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

    $businessName = trim((string) data_get($extra, 'business_name', '')) ?: '________________________';
    $businessAddress = trim((string) data_get($extra, 'business_address', '')) ?: '________________________';
    $purpose = trim((string) $permit->purpose) !== '' ? $permit->purpose : (trim((string) data_get($extra, 'purpose', '')) ?: '________________________');

    $issuedOn = $permit->released_at ?? now();
    $carbonDate = \Illuminate\Support\Carbon::parse($issuedOn);
    $day = $carbonDate->format('j');
    $month = $carbonDate->format('F');
    $year = $carbonDate->format('Y');

    $purposeDetails = [];
    if ($purpose === 'Business Permit Renewal') {
        $purposeDetails[] = ['Previous Permit Number', $clean((string) data_get($extra, 'previous_permit_number', ''))];
        $purposeDetails[] = ['Last Permit Year', $clean((string) data_get($extra, 'last_permit_year', ''))];
    } elseif ($purpose === 'Change of Business Address') {
        $purposeDetails[] = ['Old Business Address', $clean((string) data_get($extra, 'old_business_address', ''))];
        $purposeDetails[] = ['New Business Address', $clean((string) data_get($extra, 'new_business_address', ''))];
    } elseif ($purpose === 'Change of Business Name') {
        $purposeDetails[] = ['Old Business Name', $clean((string) data_get($extra, 'old_business_name', ''))];
        $purposeDetails[] = ['New Business Name', $clean((string) data_get($extra, 'new_business_name', ''))];
    } elseif ($purpose === 'Change of Ownership') {
        $purposeDetails[] = ['Previous Owner Name', $clean((string) data_get($extra, 'previous_owner_name', ''))];
        $purposeDetails[] = ['New Owner Name', $clean((string) data_get($extra, 'new_owner_name', ''))];
    } elseif ($purpose === 'Additional Line of Business') {
        $purposeDetails[] = ['Current Line of Business', $clean((string) data_get($extra, 'current_line_of_business', ''))];
        $purposeDetails[] = ['Additional Line of Business', $clean((string) data_get($extra, 'additional_line_of_business', ''))];
    } elseif ($purpose === 'Closure / Cessation of Business') {
        $purposeDetails[] = ['Closure Effective Date', $clean((string) data_get($extra, 'closure_effective_date', ''))];
        $purposeDetails[] = ['Closure Reason', $clean((string) data_get($extra, 'closure_reason', ''))];
    } elseif ($purpose === 'Compliance Requirement (BIR / DTI / SEC / LGU)') {
        $purposeDetails[] = ['Agency / Office', $clean((string) data_get($extra, 'agency_name', ''))];
        $purposeDetails[] = ['Reference Number', $clean((string) data_get($extra, 'reference_number', ''))];
    } elseif ($purpose === 'Loan / Financing Requirement') {
        $purposeDetails[] = ['Institution / Bank Name', $clean((string) data_get($extra, 'financing_institution', ''))];
        $purposeDetails[] = ['Reference Number', $clean((string) data_get($extra, 'financing_reference_number', ''))];
    }
    $purposeDetails = array_values(array_filter(
        $purposeDetails,
        static fn (array $row): bool => trim((string) ($row[1] ?? '')) !== ''
    ));
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
        <div class="meta">
            Permit No.: <span class="strong">{{ str_pad((string) $permit->id, 5, '0', STR_PAD_LEFT) }}</span><br>
            Date Issued: <span class="strong">{{ $month }} {{ $day }}, {{ $year }}</span>
        </div>

        <div class="doc-title">BUSINESS<br>PERMIT</div>
        <div class="section-label">TO WHOM IT MAY CONCERN:</div>

        <p class="paragraph">
            This is to <span class="strong">CERTIFY</span> that <span class="strong">{{ $applicantName }}</span>,
            residing at <span class="strong">{{ $applicantAddress }}</span>, has filed a request related to a business permit.
        </p>

        <div class="info-box">
            <p class="info-row"><span class="strong">Business Name:</span> {{ $businessName }}</p>
            <p class="info-row"><span class="strong">Business Address:</span> {{ $businessAddress }}</p>
            <p class="info-row"><span class="strong">Purpose:</span> {{ $purpose }}</p>
        </div>

        @if (count($purposeDetails) > 0)
            <div class="notes-title">Purpose Details</div>
            <div class="info-box">
                @foreach ($purposeDetails as [$label, $value])
                    <p class="info-row"><span class="strong">{{ $label }}:</span> {{ $value }}</p>
                @endforeach
            </div>
        @endif

        <p class="paragraph">
            {{ \App\Models\SiteSetting::getValue('doc_permit_disclaimer_business') }}
        </p>

        @include('partials.document-pdf-signature-punong-barangay', ['officialsPdf' => $officialsPdf])
    </div>
</div>
</body>
</html>
