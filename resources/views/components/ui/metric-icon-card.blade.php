@props([
    'label',
    'value',
    'wrapperClass' => 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm',
    'iconWrapperClass' => 'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 no-print',
    'iconClass' => 'h-5 w-5',
    'labelClass' => 'text-xs font-medium text-gray-500 uppercase',
    'valueClass' => 'text-2xl font-bold text-gray-900',
])

<div class="{{ $wrapperClass }}">
    <div class="flex items-center gap-3">
        <div class="{{ $iconWrapperClass }}">
            @isset($icon)
                <span class="{{ $iconClass }}">
                    {{ $icon }}
                </span>
            @endisset
        </div>
        <div>
            <p class="{{ $labelClass }}">{{ $label }}</p>
            <p class="{{ $valueClass }}">{{ $value }}</p>
        </div>
    </div>
</div>

