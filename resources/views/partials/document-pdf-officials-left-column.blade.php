@php
    use App\Support\OfficialPrint;

    /** @var array<string, mixed> $officialsPdf */
    $chairman = $officialsPdf['chairman'] ?? null;
    $kagawads = $officialsPdf['kagawads'] ?? [];
    $skChair = $officialsPdf['sk_chairman'] ?? null;
    $treasurer = $officialsPdf['treasurer'] ?? null;
    $secretary = $officialsPdf['secretary'] ?? null;
@endphp
@if ($chairman)
    <div class="official">
        <div class="name">{{ $chairman['honorific'] }}</div>
        <div class="role">{{ $chairman['role'] }}</div>
    </div>
@else
    <div class="official">
        <div class="name">________________________</div>
        <div class="role">{{ OfficialPrint::punongBarangayTitle() }}</div>
    </div>
@endif
<div class="dash">- Kagawad -</div>
@foreach ($kagawads as $row)
    <div class="official">
        <div class="name">{{ $row['honorific'] }}</div>
        <div class="role">{{ $row['role'] }}</div>
    </div>
@endforeach
@if ($skChair)
    <div class="official">
        <div class="name">{{ $skChair['honorific'] }}</div>
        <div class="role">
            SK Chairman
            @if (! empty($skChair['committee']))
                <br>{{ $skChair['committee'] }}
            @endif
        </div>
    </div>
@endif
@if ($treasurer)
    <div class="official">
        <div class="name">{{ $treasurer['honorific'] }}</div>
        <div class="role">{{ $treasurer['position'] }}</div>
    </div>
@endif
@if ($secretary)
    <div class="official">
        <div class="name">{{ $secretary['honorific'] }}</div>
        <div class="role">{{ $secretary['position'] }}</div>
    </div>
@endif
