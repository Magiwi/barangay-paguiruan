@php
    $d = $data ?? [];
    $cards = $d['cards'] ?? [[], [], [], []];
    while (count($cards) < 4) { $cards[] = ['title' => '', 'body' => '']; }
@endphp
<div class="space-y-3">
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Intro <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="intro" rows="2">{{ $d['intro'] ?? '' }}</textarea></label>
    @foreach ([0, 1, 2, 3] as $ci)
        @php $c = $cards[$ci] ?? []; @endphp
        <div class="rounded border border-gray-100 p-2 bg-gray-50">
            <p class="text-xs font-semibold text-gray-600 mb-1">Priority {{ $ci + 1 }}</p>
            <label class="block text-xs text-gray-500">Title <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="cards.{{ $ci }}.title" value="{{ $c['title'] ?? '' }}"></label>
            <label class="block text-xs text-gray-500 mt-1">Body <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="cards.{{ $ci }}.body" rows="2">{{ $c['body'] ?? '' }}</textarea></label>
        </div>
    @endforeach
</div>
