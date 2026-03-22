@props([
    'type' => 'default',
    'size' => 'sm',
])

@php
    $colors = [
        'pending'     => 'ui-badge-warning',
        'approved'    => 'ui-badge-success',
        'released'    => 'ui-badge-info',
        'rejected'    => 'ui-badge-danger',
        'info'        => 'ui-badge-info',
        'success'     => 'ui-badge-success',
        'warning'     => 'ui-badge-warning',
        'danger'      => 'ui-badge-danger',
        'in_progress' => 'ui-badge-info',
        'resolved'    => 'ui-badge-success',
        'closed'      => 'ui-badge-muted',
        'default'     => 'ui-badge-muted',
        'admin'       => 'ui-badge-danger',
        'staff'       => 'ui-badge-warning',
        'resident'    => 'ui-badge-info',
        'verified'    => 'ui-badge-success',
        'suspended'   => 'ui-badge-danger',
        'active'      => 'ui-badge-success',
    ];

    $sizes = [
        'xs' => 'px-2 py-0.5 text-[11px]',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-xs',
    ];

    $colorClass = $colors[$type] ?? $colors['default'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-medium {$colorClass} {$sizeClass}"]) }}>
    {{ $slot }}
</span>
