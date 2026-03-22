@props([
    'baseClass' => 'hover:bg-gray-50',
])

<tr {{ $attributes->class($baseClass) }}>
    {{ $slot }}
</tr>

