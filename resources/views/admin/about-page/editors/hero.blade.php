@php $d = $data ?? []; @endphp
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
    <label class="block text-xs text-gray-500">Badge <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="badge" value="{{ $d['badge'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Title line 1 <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="title_line1" value="{{ $d['title_line1'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Title line 2 <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="title_line2" value="{{ $d['title_line2'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500 sm:col-span-2">Subtitle <textarea class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="subtitle" rows="2">{{ $d['subtitle'] ?? '' }}</textarea></label>
    <label class="block text-xs text-gray-500">Primary button label <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="primary_button_label" value="{{ $d['primary_button_label'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Primary button link <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="primary_button_href" value="{{ $d['primary_button_href'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Secondary button label <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="secondary_button_label" value="{{ $d['secondary_button_label'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Secondary button link <input type="text" class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="secondary_button_href" value="{{ $d['secondary_button_href'] ?? '' }}"></label>
    <label class="block text-xs text-gray-500">Hero visual
        <select class="js-data mt-1 w-full rounded border-gray-300 text-sm" data-field="hero_visual">
            <option value="icon" {{ ($d['hero_visual'] ?? 'icon') === 'icon' ? 'selected' : '' }}>Icon (default)</option>
            <option value="image" {{ ($d['hero_visual'] ?? '') === 'image' ? 'selected' : '' }}>Image</option>
        </select>
    </label>
    <label class="block text-xs text-gray-500 sm:col-span-2">Hero image (storage path after upload) <input type="text" class="js-data mt-1 w-full rounded border-gray-300 font-mono text-xs" data-field="hero_image_path" value="{{ $d['hero_image_path'] ?? '' }}" placeholder="storage:site-pages/about/..."></label>
    <div class="sm:col-span-2">
        <p class="text-xs text-gray-500 mb-1">Upload hero image</p>
        <input type="file" accept="image/*" class="js-hero-upload block text-sm text-gray-600">
    </div>
</div>
