@php
    use App\Support\AboutPageMedia;
    $slides = $data['slides'] ?? [];
@endphp
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-10 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">{{ $data['kicker'] ?? '' }}</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
            <p class="mt-3 text-gray-600">{{ $data['subtitle'] ?? '' }}</p>
        </div>
        @if (count($slides))
            <div class="fade-in relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md" id="about-slideshow">
                @foreach ($slides as $i => $slide)
                    @php
                        $src = AboutPageMedia::url($slide['path'] ?? '') ?? '';
                        $alt = $slide['alt'] ?? ($slide['label'] ?? '');
                    @endphp
                    <div class="about-slide {{ $i === 0 ? '' : 'hidden' }}">
                        @if($src !== '')
                            <img src="{{ $src }}" alt="{{ $alt }}" class="h-[360px] w-full object-cover sm:h-[420px]">
                        @endif
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-5">
                            <p class="text-sm font-semibold uppercase tracking-wider text-white">{{ $slide['label'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach

                <button type="button" id="about-slide-prev" class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white" aria-label="Previous slide">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </button>
                <button type="button" id="about-slide-next" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 text-gray-700 shadow hover:bg-white" aria-label="Next slide">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5L15.75 12l-7.5 7.5"/></svg>
                </button>

                <div class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-black/30 px-2 py-1 backdrop-blur-sm">
                    @foreach ($slides as $i => $slide)
                        <button type="button" class="about-slide-dot h-2 w-2 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/50' }}" data-slide-index="{{ $i }}" aria-label="Go to slide {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
