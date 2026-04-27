@php $d = $data ?? []; @endphp
<div class="space-y-3">
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" class="js-data js-boolean" data-field="use_site_settings" value="1" {{ ($d['use_site_settings'] ?? true) ? 'checked' : '' }}>
        Use Site Settings for address, phone, and email (recommended)
    </label>
    <p class="text-xs text-gray-500">When enabled, address, phone, email, and office hours come from Admin → Site settings (Home &amp; contact). Edit headings and column labels below; set the office hours text under Site settings.</p>
    <label class="block text-xs text-gray-500">Kicker <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="kicker" value="{{ $d['kicker'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Heading <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="heading" value="{{ $d['heading'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Column label — address <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="label_address" value="{{ $d['label_address'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Column label — phone <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="label_phone" value="{{ $d['label_phone'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Column label — email <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="label_email" value="{{ $d['label_email'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Manual address (HTML, when not using site settings) <textarea class="js-data mt-1 w-full rounded border-gray-300 font-mono text-xs" data-field="manual_address_html" rows="2">{{ $d['manual_address_html'] ?? '' }}</textarea></label>
    <label class="block text-xs text-gray-500">Manual phone <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="manual_phone" value="{{ $d['manual_phone'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Manual email <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="manual_email" value="{{ $d['manual_email'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Office hours line <span class="font-normal text-gray-400">(only when not using Site Settings)</span> <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="office_hours_line" value="{{ $d['office_hours_line'] ?? '' }}"></label>
</div>
