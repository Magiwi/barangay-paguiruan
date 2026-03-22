@props([
    'title',
    'wrapperClass' => 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm',
    'titleClass' => 'text-sm font-semibold uppercase tracking-wide text-gray-500',
    'chartHeightClass' => 'mt-4 h-72',
])

<div class="{{ $wrapperClass }}">
    <h2 class="{{ $titleClass }}">{{ $title }}</h2>
    <div class="{{ $chartHeightClass }}">
        {{ $slot }}
    </div>
</div>

