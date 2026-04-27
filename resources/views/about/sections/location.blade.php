@php
    use App\Services\SitePage\AboutPageContentService;
    /** @var AboutPageContentService $aboutSvc */
    $aboutSvc = app(AboutPageContentService::class);
    $mapEmbed = $aboutSvc->resolveMapEmbedUrl($data);
    $options = $data['purok_options'] ?? [];
@endphp
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-start">
            <div class="fade-in">
                <p class="text-sm font-semibold uppercase tracking-widest text-green-600">{{ $data['kicker'] ?? '' }}</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50">
                        <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ $data['heading'] ?? '' }}</h2>
                </div>

                <div class="mt-6 space-y-4 text-gray-600 leading-relaxed">
                    @foreach ($data['paragraphs'] ?? [] as $p)
                        <p>{{ $p }}</p>
                    @endforeach
                </div>

                <ul class="mt-6 space-y-3">
                    @foreach ($data['bullets'] ?? [] as $bullet)
                        <li class="flex items-center gap-3 text-sm text-gray-700">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-3.5 w-3.5 text-green-700" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </span>
                            {{ $bullet }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <label for="purok-select" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">{{ $data['purok_label'] ?? 'View by Purok' }}</label>
                    <select id="purok-select" class="w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm ring-1 ring-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                        @foreach ($options as $opt)
                            <option value="{{ $opt['value'] ?? '' }}">{{ $opt['label'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="fade-in fade-delay-2">
                <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200">
                    <iframe
                        id="barangay-map"
                        src="{{ $mapEmbed }}"
                        class="w-full h-[400px]"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <div class="border-t border-gray-100 bg-gray-50 px-4 py-2.5">
                        <p class="text-xs text-gray-500 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            {{ $data['map_caption'] ?? '' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
