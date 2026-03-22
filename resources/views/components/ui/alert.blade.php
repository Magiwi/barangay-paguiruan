@props([
    'type' => 'success',
    'dismissible' => false,
])

@php
    $styles = [
        'success' => 'border-green-200 text-green-800',
        'error'   => 'border-red-200 text-red-800',
        'warning' => 'border-yellow-200 text-yellow-800',
        'info'    => 'border-blue-200 text-blue-800',
    ];
    $bgStyles = [
        'success' => 'background-color: var(--success-100)',
        'error'   => 'background-color: var(--danger-100)',
        'warning' => 'background-color: var(--warning-100)',
        'info'    => 'background-color: var(--info-100)',
    ];
    $styleClass = $styles[$type] ?? $styles['info'];
    $bgStyle = $bgStyles[$type] ?? $bgStyles['info'];
@endphp

<div {{ $attributes->merge(['class' => "mb-4 rounded-2xl border px-4 py-3 text-sm {$styleClass}"]) }}
     style="{{ $bgStyle }}"
     @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif>
    <div class="flex items-center justify-between">
        <span>{{ $slot }}</span>
        @if ($dismissible)
            <button type="button" @click="show = false" class="ml-3 opacity-60 hover:opacity-100 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        @endif
    </div>
</div>
