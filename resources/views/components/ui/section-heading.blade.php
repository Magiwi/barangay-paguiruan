@props([
    'title',
    'subtitle' => null,
    'wrapperClass' => 'mb-4',
    'titleClass' => 'text-sm font-semibold text-gray-500 uppercase tracking-wider',
    'subtitleClass' => 'mt-1 text-xs text-gray-500',
    'innerClass' => 'flex items-center justify-between gap-3',
])

<div class="{{ $wrapperClass }}">
    <div class="{{ $innerClass }}">
        <div>
            <h2 class="{{ $titleClass }}">{{ $title }}</h2>
            @if ($subtitle)
                <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div>
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>

