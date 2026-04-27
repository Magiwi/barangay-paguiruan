@php
    $features = $data['features'] ?? [];
@endphp
<section id="about-system" class="bg-gray-50 py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mx-auto max-w-3xl text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
            <p class="mt-4 text-gray-600 leading-relaxed">
                {{ $data['body'] ?? '' }}
            </p>
        </div>

        <div class="mx-auto mt-14 grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-3">
            @foreach ($features as $i => $card)
                @php
                    $accent = ($card['accent'] ?? 'blue') === 'green' ? 'green' : 'blue';
                    $delayClass = ['fade-delay-1', 'fade-delay-2', 'fade-delay-3', 'fade-delay-4'][min($i, 3)] ?? 'fade-delay-4';
                    $border = $accent === 'green' ? 'border-green-600' : 'border-blue-600';
                    $iconBg = $accent === 'green' ? 'bg-green-50' : 'bg-blue-50';
                    $iconColor = $accent === 'green' ? 'text-green-700' : 'text-blue-700';
                @endphp
                <div class="fade-in {{ $delayClass }} rounded-2xl border-t-4 {{ $border }} bg-white p-7 shadow-md hover:shadow-xl transition-all duration-300">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $iconBg }}">
                        @if($accent === 'green')
                            <svg class="h-6 w-6 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                            </svg>
                        @elseif($i % 3 === 0)
                            <svg class="h-6 w-6 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        @else
                            <svg class="h-6 w-6 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="mt-5 text-base font-semibold tracking-tight text-gray-900">{{ $card['title'] ?? '' }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $card['body'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
