@props([
    'title' => 'Report',
    'subtitle' => null,
    'backUrl' => null,
    'class' => '',
    'actionsClass' => '',
])

<div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between {{ $class }}">
    <div class="flex items-center gap-4">
        @if ($backUrl)
            <a href="{{ $backUrl }}"
               class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition hover:bg-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        @endif

        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="w-full md:ml-auto md:w-auto {{ $actionsClass }}">
        {{ $actions ?? '' }}
    </div>
</div>

