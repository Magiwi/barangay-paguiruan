@props([
    'hoverable' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            @if (isset($head))
                <thead class="bg-gray-50/80">
                    <tr>{{ $head }}</tr>
                </thead>
            @endif
            <tbody class="divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if (isset($footer))
        <div class="border-t border-gray-200 px-6 py-3">
            {{ $footer }}
        </div>
    @endif
</div>
