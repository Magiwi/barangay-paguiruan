@php
    $d = $data ?? [];
    $paragraphs = $d['paragraphs'] ?? ['', ''];
    $bullets = $d['bullets'] ?? ['', '', ''];
    $purokOpts = $d['purok_options'] ?? [];
    $purokRowCount = 20;
@endphp
<div class="space-y-3">
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    @foreach ([0, 1] as $pi)
        <label class="block text-xs text-gray-500">Paragraph {{ $pi + 1 }} <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="paragraphs.{{ $pi }}" rows="2">{{ $paragraphs[$pi] ?? '' }}</textarea></label>
    @endforeach
    @foreach ([0, 1, 2] as $bi)
        <label class="block text-xs text-gray-500">Bullet {{ $bi + 1 }} <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="bullets.{{ $bi }}" value="{{ $bullets[$bi] ?? '' }}"></label>
    @endforeach
    <label class="block text-xs text-gray-500">Purok dropdown label <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="purok_label" value="{{ $d['purok_label'] ?? '' }}"></label>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
        <p class="text-xs font-semibold text-gray-700">Purok list &amp; map embeds</p>
        <p class="mt-1 text-[11px] text-gray-500">Up to 20 rows. Set a unique <span class="font-mono">value</span> per row (e.g. <span class="font-mono">default</span>, <span class="font-mono">purok1</span>). Leave per-row map URL empty to use the built-in default for that key.</p>
        <p class="mt-1 text-[11px] text-gray-500">Main map (first load): use “Default map URL” below, or Site Settings contact map when empty.</p>
    </div>

    @for ($pi = 0; $pi < $purokRowCount; $pi++)
        @php
            $o = $purokOpts[$pi] ?? [];
        @endphp
        <div class="rounded border border-gray-100 bg-white p-2">
            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Purok row {{ $pi + 1 }}</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <label class="block text-xs text-gray-500">Value <input type="text" class="js-data mt-0.5 w-full rounded border-gray-300 font-mono text-xs" data-field="purok_options.{{ $pi }}.value" value="{{ $o['value'] ?? '' }}" placeholder="e.g. purok1"></label>
                <label class="block text-xs text-gray-500 sm:col-span-2">Label <input type="text" class="js-data mt-0.5 w-full rounded border-gray-300 text-sm" data-field="purok_options.{{ $pi }}.label" value="{{ $o['label'] ?? '' }}"></label>
            </div>
            <label class="mt-2 block text-xs text-gray-500">Map embed URL (optional) <input type="text" class="js-data mt-0.5 w-full rounded border-gray-300 font-mono text-[11px]" data-field="purok_options.{{ $pi }}.map_embed_url" value="{{ $o['map_embed_url'] ?? '' }}" placeholder="https://maps.google.com/maps?..."></label>
        </div>
    @endfor

    <label class="block text-xs text-gray-500">Default map URL (optional — iframe on first load; uses Site Settings if empty) <input type="text" class="js-data mt-1 w-full rounded border-gray-300 font-mono text-xs" data-field="map_embed_url" value="{{ $d['map_embed_url'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Map caption <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="map_caption" value="{{ $d['map_caption'] ?? '' }}"></label>
</div>
