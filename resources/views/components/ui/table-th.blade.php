@props([
    'label' => null,
    'paddingClass' => 'px-6 py-4',
    'align' => 'left',
    'textClass' => 'text-gray-600',
    'weightClass' => 'font-semibold',
])

@php
    $alignClass = $align === 'right' ? 'text-right' : 'text-left';
@endphp

<th {{ $attributes->class("{$paddingClass} {$alignClass} text-xs {$weightClass} uppercase tracking-wider {$textClass}") }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</th>

