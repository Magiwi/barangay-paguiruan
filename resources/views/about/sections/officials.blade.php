@php
    $officialCards = collect($officialCards ?? []);
    $officialSlides = $officialCards->chunk(4)->values();
@endphp
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-14 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
            <p class="mt-3 text-gray-600">{{ $data['subtitle'] ?? '' }}</p>
        </div>

        @if ($officialCards->isEmpty())
            <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-600 fade-in">
                No active officials available at the moment.
            </div>
        @else
            <div id="officials-carousel" class="mx-auto max-w-4xl fade-in relative">
                @foreach ($officialSlides as $slideIndex => $slideItems)
                    <div class="officials-slide {{ $slideIndex === 0 ? '' : 'hidden' }}">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($slideItems as $i => $official)
                                @php $od = ['fade-delay-1', 'fade-delay-2', 'fade-delay-3', 'fade-delay-4'][min($i, 3)] ?? 'fade-delay-4'; @endphp
                                <div class="fade-in {{ $od }} rounded-2xl bg-white p-6 text-center shadow-md ring-1 ring-gray-100 hover:shadow-xl transition-all duration-300">
                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-blue-50 ring-4 ring-blue-50">
                                        <span class="text-xl font-bold text-blue-700">{{ $official['initials'] }}</span>
                                    </div>
                                    <h3 class="mt-4 text-sm font-semibold text-gray-900">{{ $official['name'] }}</h3>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $official['role'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if ($officialSlides->count() > 1)
                    <button type="button" id="officials-slide-prev" class="absolute -left-4 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 text-gray-700 shadow-md ring-1 ring-gray-200 hover:bg-gray-50" aria-label="Previous officials">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" id="officials-slide-next" class="absolute -right-4 top-1/2 -translate-y-1/2 rounded-full bg-white p-2 text-gray-700 shadow-md ring-1 ring-gray-200 hover:bg-gray-50" aria-label="Next officials">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5L15.75 12l-7.5 7.5"/></svg>
                    </button>
                    <div class="mt-6 flex justify-center gap-2">
                        @foreach ($officialSlides as $slideIndex => $slideItems)
                            <button type="button" class="officials-slide-dot h-2.5 w-2.5 rounded-full {{ $slideIndex === 0 ? 'bg-blue-600' : 'bg-gray-300' }}" data-slide-index="{{ $slideIndex }}" aria-label="Go to officials slide {{ $slideIndex + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
