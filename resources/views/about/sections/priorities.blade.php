@php
    $cards = $data['cards'] ?? [];
    $delayClasses = ['fade-delay-1', 'fade-delay-2', 'fade-delay-3', 'fade-delay-4'];
@endphp
<section class="bg-blue-50 py-16 px-6">
    <div class="mx-auto max-w-4xl fade-in">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-gray-600 leading-relaxed">
                {{ $data['intro'] ?? '' }}
            </p>
        </div>
        <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach ($cards as $i => $card)
                @php
                    $dc = $delayClasses[min($i, 3)] ?? 'fade-delay-4';
                    $iconBg = $i % 2 === 0 ? 'bg-green-100' : 'bg-blue-100';
                    $iconColor = $i % 2 === 0 ? 'text-green-700' : 'text-blue-700';
                @endphp
                <div class="fade-in {{ $dc }} flex items-start gap-4 rounded-2xl bg-white p-6 shadow-md ring-1 ring-blue-100/50 hover:shadow-xl transition-all duration-300">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $iconBg }}">
                        @if($i % 4 === 0)
                            <svg class="h-5 w-5 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($i % 4 === 1)
                            <svg class="h-5 w-5 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($i % 4 === 2)
                            <svg class="h-5 w-5 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                            </svg>
                        @else
                            <svg class="h-5 w-5 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold tracking-tight text-gray-900">{{ $card['title'] ?? '' }}</h4>
                        <p class="mt-1 text-sm text-gray-600 leading-relaxed">{{ $card['body'] ?? '' }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
