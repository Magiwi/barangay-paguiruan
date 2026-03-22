@props([
    'colspan' => 1,
    'cellClass' => 'px-6 py-12 text-center',
])

<tr>
    <td colspan="{{ $colspan }}" class="{{ $cellClass }}">
        {{ $slot }}
    </td>
</tr>

