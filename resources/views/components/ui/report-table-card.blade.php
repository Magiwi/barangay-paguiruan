@props([
    'title' => null,
    'wrapperClass' => 'rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden',
    'headerClass' => 'border-b border-gray-200 px-5 py-4',
    'titleClass' => 'text-sm font-semibold uppercase tracking-wide text-gray-500',
    'bodyClass' => 'overflow-x-auto',
    'footerClass' => 'border-t border-gray-200 px-5 py-4',
])

<div class="{{ $wrapperClass }}">
    @if ($title)
        <div class="{{ $headerClass }}">
            <h2 class="{{ $titleClass }}">{{ $title }}</h2>
        </div>
    @endif

    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="{{ $footerClass }}">
            {{ $footer }}
        </div>
    @endisset
</div>

