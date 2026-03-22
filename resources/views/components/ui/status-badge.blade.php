@props([
    'label' => null,
    'paddingClass' => 'px-2.5 py-1',
    'fontClass' => 'font-semibold',
])

<span {{ $attributes->class("inline-flex rounded-full text-xs {$paddingClass} {$fontClass}") }}>
    {{ $slot->isEmpty() ? $label : $slot }}
</span>

