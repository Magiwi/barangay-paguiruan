@php
    $d = $data ?? [];
    $features = $d['features'] ?? [[], [], []];
    while (count($features) < 3) { $features[] = ['accent' => 'blue', 'title' => '', 'body' => '']; }
@endphp
<div class="space-y-4">
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Body <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="body" rows="3">{{ $d['body'] ?? '' }}</textarea></label>
    @foreach ([0, 1, 2] as $fi)
        @php $f = $features[$fi] ?? []; @endphp
        <div class="rounded border border-gray-100 p-3 bg-gray-50">
            <p class="text-xs font-semibold text-gray-600 mb-2">Feature card {{ $fi + 1 }}</p>
            <label class="block text-xs text-gray-500">Accent
                <select class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="features.{{ $fi }}.accent">
                    <option value="blue" {{ ($f['accent'] ?? '') === 'blue' ? 'selected' : '' }}>Blue</option>
                    <option value="green" {{ ($f['accent'] ?? '') === 'green' ? 'selected' : '' }}>Green</option>
                </select>
            </label>
            <label class="block text-xs text-gray-500 mt-2">Title <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="features.{{ $fi }}.title" value="{{ $f['title'] ?? '' }}"></label>
            <label class="block text-xs text-gray-500 mt-2">Body <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="features.{{ $fi }}.body" rows="2">{{ $f['body'] ?? '' }}</textarea></label>
        </div>
    @endforeach
</div>
