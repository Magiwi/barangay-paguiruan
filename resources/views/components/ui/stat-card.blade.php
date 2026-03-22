@props([
    'label',
    'value',
    'color' => 'green',
    'href' => null,
])

@php
    $bgColors = [
        'green'   => 'bg-green-50',
        'blue'    => 'bg-blue-50',
        'amber'   => 'bg-amber-50',
        'red'     => 'bg-red-50',
        'indigo'  => 'bg-indigo-50',
        'teal'    => 'bg-teal-50',
        'purple'  => 'bg-purple-50',
        'orange'  => 'bg-orange-50',
    ];
    $textColors = [
        'green'   => 'text-green-600',
        'blue'    => 'text-blue-600',
        'amber'   => 'text-amber-600',
        'red'     => 'text-red-600',
        'indigo'  => 'text-indigo-600',
        'teal'    => 'text-teal-600',
        'purple'  => 'text-purple-600',
        'orange'  => 'text-orange-600',
    ];
    $valueColors = [
        'green'   => 'text-green-700',
        'blue'    => 'text-blue-700',
        'amber'   => 'text-amber-700',
        'red'     => 'text-red-700',
        'indigo'  => 'text-indigo-700',
        'teal'    => 'text-teal-700',
        'purple'  => 'text-purple-700',
        'orange'  => 'text-orange-700',
    ];
    $bgColor = $bgColors[$color] ?? $bgColors['green'];
    $textColor = $textColors[$color] ?? $textColors['green'];
    $valueColor = $valueColors[$color] ?? $valueColors['green'];
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm {{ $href ? 'group hover:shadow-md hover:border-green-200 transition-all duration-200' : '' }}">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $bgColor }}">
            @if ($slot->isNotEmpty())
                <span class="{{ $textColor }}">{{ $slot }}</span>
            @else
                <svg class="h-5 w-5 {{ $textColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            @endif
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium text-gray-500 {{ $href ? 'group-hover:text-green-600' : '' }}">{{ $label }}</p>
            <p class="text-xl font-bold {{ $valueColor }}">{{ $value }}</p>
        </div>
    </div>
</{{ $tag }}>
