@props([
    'title',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6']) }}>
    <div>
        <h1 class="text-xl font-semibold tracking-tight text-gray-800">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="flex items-center gap-3">{{ $slot }}</div>
    @endif
</div>
