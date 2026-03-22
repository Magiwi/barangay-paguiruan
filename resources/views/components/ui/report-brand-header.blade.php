@props([
    'reportTitle' => 'Report',
    'scopeText' => null,
    'generatedAt' => null,
])

@php
    $generatedAt = $generatedAt ?: now()->format('F d, Y h:i A');
@endphp

<div>
    <div class="hidden print:block rounded-xl border border-gray-300 bg-white p-4">
        <div class="flex items-center justify-between gap-4">
            <img src="{{ asset('images/logo1.png') }}" alt="Left Logo" class="h-16 w-16 object-contain">
            <div class="text-center flex-1">
                <h1 class="text-lg font-semibold tracking-tight text-gray-800">Barangay Paguiruan, Floridablanca</h1>
                <p class="text-xs text-gray-600">e-Governance System</p>
                <p class="text-xs text-gray-600">{{ $reportTitle }}</p>
                <p class="text-[11px] text-gray-500 mt-1">Generated on: {{ $generatedAt }}</p>
                @if ($scopeText)
                    <p class="text-[11px] text-gray-700 mt-1">{{ $scopeText }}</p>
                @endif
            </div>
            <img src="{{ asset('images/logo1.png') }}" alt="Right Logo" class="h-16 w-16 object-contain">
        </div>
    </div>

    <div class="no-print rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <img src="{{ asset('images/logo1.png') }}" alt="Left Logo" class="h-14 w-14 object-contain">
            <div class="text-center flex-1">
                <h2 class="text-base font-semibold tracking-tight text-gray-900">Barangay Paguiruan, Floridablanca</h2>
                <p class="text-xs text-gray-500">e-Governance System</p>
            </div>
            <img src="{{ asset('images/logo1.png') }}" alt="Right Logo" class="h-14 w-14 object-contain">
        </div>
    </div>
</div>

