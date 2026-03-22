@props([
    'title',
    'wrapperClass' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm',
    'headerClass' => 'px-6 py-4 border-b border-gray-200 bg-gray-50',
    'titleClass' => 'text-sm font-semibold text-gray-700',
    'bodyClass' => 'overflow-x-auto',
])

<div class="{{ $wrapperClass }}">
    <div class="{{ $headerClass }}">
        <h3 class="{{ $titleClass }}">{{ $title }}</h3>
    </div>
    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>

