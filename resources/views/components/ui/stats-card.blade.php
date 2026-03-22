@props([
    'label',
    'value',
    'wrapperClass' => 'rounded-2xl border bg-white p-5 shadow-sm',
    'labelClass' => 'text-xs font-semibold uppercase tracking-wide text-gray-500',
    'valueClass' => 'mt-1 text-3xl font-bold text-gray-900',
])

<div class="{{ $wrapperClass }}">
    <p class="{{ $labelClass }}">{{ $label }}</p>
    <p class="{{ $valueClass }}">{{ $value }}</p>
</div>

