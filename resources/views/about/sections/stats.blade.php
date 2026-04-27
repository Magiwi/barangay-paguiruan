@php
    $communityStats = $communityStats ?? [
        'total_people' => 0,
        'total_households' => 0,
        'total_puroks' => 0,
    ];
    $labels = $data['labels'] ?? [];
@endphp
<section class="bg-gray-50 py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
        </div>
        <div class="mt-12 rounded-2xl bg-white shadow-md ring-1 ring-gray-100 fade-in fade-delay-1">
            <div class="grid grid-cols-1 md:grid-cols-3">
                <div class="stat-divider flex flex-col items-center py-10 px-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-7 w-7 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-700">{{ number_format((int) ($communityStats['total_people'] ?? 0)) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-gray-500">{{ $labels['residents'] ?? 'Total Residents' }}</p>
                </div>
                <div class="stat-divider flex flex-col items-center border-t md:border-t-0 py-10 px-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-green-50">
                        <svg class="h-7 w-7 text-green-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-700">{{ number_format((int) ($communityStats['total_households'] ?? 0)) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-gray-500">{{ $labels['households'] ?? 'Households' }}</p>
                </div>
                <div class="flex flex-col items-center border-t md:border-t-0 py-10 px-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-7 w-7 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-700">{{ number_format((int) ($communityStats['total_puroks'] ?? 0)) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-gray-500">{{ $labels['puroks'] ?? 'Puroks (Zones)' }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
