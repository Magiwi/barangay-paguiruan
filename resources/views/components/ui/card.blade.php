@props([
    'padding' => true,
    'hover' => false,
])

<div {{ $attributes->merge([
    'class' => 'ui-surface-card'
        . ($hover ? ' ui-surface-card-hover' : '')
        . ($padding ? ' p-6' : '')
]) }}>
    {{ $slot }}
</div>
