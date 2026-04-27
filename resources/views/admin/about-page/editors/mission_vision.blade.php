@php $d = $data ?? []; @endphp
<div class="space-y-3">
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Mission title <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="mission_title" value="{{ $d['mission_title'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Mission body <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="mission_body" rows="3">{{ $d['mission_body'] ?? '' }}</textarea></label>
    <label class="block text-xs text-gray-500">Vision title <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="vision_title" value="{{ $d['vision_title'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Vision body <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="vision_body" rows="3">{{ $d['vision_body'] ?? '' }}</textarea></label>
</div>
