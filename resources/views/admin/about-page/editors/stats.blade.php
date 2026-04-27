@php $d = $data ?? []; $labels = $d['labels'] ?? []; @endphp
<div class="space-y-3">
    <label class="block text-xs text-gray-500">Section kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <p class="text-xs text-gray-500">Counts are always live from the database.</p>
    <label class="block text-xs text-gray-500">Label — residents <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="labels.residents" value="{{ $labels['residents'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Label — households <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="labels.households" value="{{ $labels['households'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Label — puroks <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="labels.puroks" value="{{ $labels['puroks'] ?? '' }}"></label>
</div>
