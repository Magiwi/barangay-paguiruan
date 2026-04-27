@php $d = $data ?? []; @endphp
<div class="space-y-3">
    <p class="text-xs text-gray-500">Officials are loaded from the Officials roster (read-only).</p>
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Subtitle <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="subtitle" rows="2">{{ $d['subtitle'] ?? '' }}</textarea></label>
</div>
