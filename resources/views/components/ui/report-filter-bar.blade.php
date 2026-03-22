@props([
    'action',
    'method' => 'GET',
    'fieldsClass' => 'grid grid-cols-1 gap-4 md:grid-cols-3',
    'submitLabel' => 'Apply Filters',
    'submitClass' => 'ui-btn ui-btn-primary',
    'resetUrl' => null,
    'showReset' => true,
    'wrapperClass' => 'rounded-2xl border border-gray-200 bg-white p-4 shadow-sm',
])

<form method="{{ $method }}" action="{{ $action }}" class="{{ $wrapperClass }}">
    <div class="{{ $fieldsClass }}">
        {{ $slot }}
    </div>

    <div class="mt-4 flex items-center justify-end gap-2">
        @if ($showReset && $resetUrl)
            <a href="{{ $resetUrl }}"
               class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200">
                Reset
            </a>
        @endif
        <button type="submit" class="{{ $submitClass }}">
            {{ $submitLabel }}
        </button>
    </div>
</form>

