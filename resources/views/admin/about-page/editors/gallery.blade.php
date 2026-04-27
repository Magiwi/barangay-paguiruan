@php
    $d = $data ?? [];
    $slides = array_slice($d['slides'] ?? [], 0, 12);
    while (count($slides) < 6) {
        $slides[] = ['path' => '', 'label' => '', 'alt' => ''];
    }
@endphp
<div class="space-y-3">
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Subtitle <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="subtitle" value="{{ $d['subtitle'] ?? '' }}"></label>
    <p class="text-xs font-semibold text-gray-600 pt-2">Slides (path: asset path or storage:… after upload)</p>
    @foreach ($slides as $si => $slide)
        <div class="gallery-slide-row rounded border border-gray-100 p-2 bg-gray-50">
            <p class="text-[10px] uppercase text-gray-400 mb-1">Slide {{ $si + 1 }}</p>
            <input type="text" class="js-data mb-1 w-full rounded border-gray-300 font-mono text-xs" data-field="slides.{{ $si }}.path" value="{{ $slide['path'] ?? '' }}" placeholder="path">
            <div class="flex gap-2">
                <input type="text" class="js-data flex-1 rounded border-gray-300 text-sm" data-field="slides.{{ $si }}.label" value="{{ $slide['label'] ?? '' }}" placeholder="Label">
                <input type="text" class="js-data flex-1 rounded border-gray-300 text-sm" data-field="slides.{{ $si }}.alt" value="{{ $slide['alt'] ?? '' }}" placeholder="Alt">
            </div>
            <input type="file" accept="image/*" class="js-gallery-upload mt-1 text-xs text-gray-600" data-slide-index="{{ $si }}">
        </div>
    @endforeach
</div>
