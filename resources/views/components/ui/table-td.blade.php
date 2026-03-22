@props([
    'label' => null,
    'paddingClass' => 'px-6 py-4',
    'align' => 'left',
    'sizeClass' => 'text-sm',
    'textClass' => 'text-gray-700',
    'weightClass' => '',
])

@php
    $alignClass = $align === 'right' ? 'text-right' : 'text-left';
@endphp

<td {{ $attributes->class("{$paddingClass} {$alignClass} {$sizeClass} {$textClass} {$weightClass}") }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</td>

