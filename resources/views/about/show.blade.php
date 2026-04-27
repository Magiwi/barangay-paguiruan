@extends(auth()->check() ? 'layouts.resident' : 'layouts.about-guest')

@section('title', 'About Us - Barangay Paguiruan')

@section('content')
@include('about.partials.styles')

@foreach ($sections as $section)
    @if (empty($section['visible']))
        @continue
    @endif
    @php $type = $section['type'] ?? ''; $data = $section['data'] ?? []; @endphp
    @switch($type)
        @case('hero')
            @include('about.sections.hero', ['data' => $data])
            @break
        @case('intro')
            @include('about.sections.intro', ['data' => $data])
            @break
        @case('location')
            @include('about.sections.location', ['data' => $data])
            @break
        @case('gallery')
            @include('about.sections.gallery', ['data' => $data])
            @break
        @case('stats')
            @include('about.sections.stats', ['data' => $data, 'communityStats' => $communityStats])
            @break
        @case('mission_vision')
            @include('about.sections.mission_vision', ['data' => $data])
            @break
        @case('priorities')
            @include('about.sections.priorities', ['data' => $data])
            @break
        @case('officials')
            @include('about.sections.officials', ['data' => $data, 'officialCards' => $officialCards])
            @break
        @case('contact')
            @include('about.sections.contact', ['data' => $data])
            @break
    @endswitch
@endforeach

@include('about.partials.scripts')
@endsection

@section('custom_footer')
    @include('partials.public-footer')
@endsection
