@props([
    'title' => null,
    'wrapperClass' => 'rounded-2xl border border-gray-200 bg-white p-5 shadow-sm',
    'titleClass' => 'text-xs font-semibold uppercase tracking-wide text-gray-500',
    'bodyClass' => '',
])

<div class="{{ $wrapperClass }}">
    @if ($title)
        <p class="{{ $titleClass }}">{{ $title }}</p>
    @endif
    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>

