@props(['url'])
@php
    $embedLogo = static function (string $filename): ?string {
        $path = public_path('images/' . $filename);
        if (! is_file($path)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    };

    $officeLine = static function (string $key): string {
        try {
            return \App\Models\SiteSetting::getValue($key);
        } catch (\Throwable) {
            return \App\Models\SiteSetting::DEFAULTS[$key] ?? '';
        }
    };

    $logo1 = $embedLogo('logo1.png');
    $logo2 = $embedLogo('logo2.png');
@endphp
<tr>
<td class="header">
<table class="email-brand-header" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="email-brand-logo" width="72" valign="middle" align="left">
@if ($logo1)
<a href="{{ $url }}" style="display:inline-block;text-decoration:none;">
<img src="{{ $logo1 }}" width="56" height="56" alt="Barangay Paguiruan" style="display:block;border:0;width:56px;height:56px;object-fit:contain;">
</a>
@endif
</td>
<td class="email-brand-office" valign="middle" align="center">
<div class="email-office-line">{{ $officeLine('doc_header_line_1') }}</div>
<div class="email-office-line">{{ $officeLine('doc_header_line_2') }}</div>
<div class="email-office-line email-office-strong">{{ $officeLine('doc_header_line_3') }}</div>
<div class="email-office-line email-office-strong">{{ $officeLine('doc_header_line_4') }}</div>
</td>
<td class="email-brand-logo email-brand-logo-right" width="72" valign="middle" align="right">
@if ($logo2)
<img src="{{ $logo2 }}" width="56" height="56" alt="Municipality of Floridablanca" style="display:block;border:0;width:56px;height:56px;object-fit:contain;margin-left:auto;">
@endif
</td>
</tr>
</table>
</td>
</tr>
