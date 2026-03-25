@extends('layouts.resident')

@section('title', 'About Us - Barangay Paguiruan')

@section('content')
<style>
    html { scroll-behavior: smooth; }
    .fade-in { opacity: 0; transform: translateY(20px); transition: all 0.6s ease; }
    .fade-in.show { opacity: 1; transform: translateY(0); }
    .fade-delay-1 { transition-delay: 0.1s; }
    .fade-delay-2 { transition-delay: 0.2s; }
    .fade-delay-3 { transition-delay: 0.3s; }
    .fade-delay-4 { transition-delay: 0.4s; }
    .stat-divider { position: relative; }
    .stat-divider::after {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 48px;
        width: 1px;
        background: linear-gradient(to bottom, transparent, #cbd5e1, transparent);
    }
    @media (max-width: 767px) { .stat-divider::after { display: none; } }
    .stat-divider:last-child::after { display: none; }
</style>

{{-- ============================================================
     HERO SECTION — Government-style gradient with pattern overlay
     ============================================================ --}}
<section class="relative overflow-hidden bg-gradient-to-r from-blue-800 via-blue-700 to-green-600">
    {{-- Pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.07]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-rule=&quot;evenodd&quot;%3E%3Cpath d=&quot;M0 40L40 0H20L0 20M40 40V20L20 40&quot;/%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="relative mx-auto max-w-7xl px-6 py-20 sm:py-28 lg:py-32">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-2">
            {{-- Left: Text content --}}
            <div class="fade-in">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-medium text-blue-100 ring-1 ring-white/20 backdrop-blur-sm">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18"/></svg>
                    Republic of the Philippines
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-[3.5rem] lg:leading-[1.15]">
                    About Barangay<br>Paguiruan
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-blue-100/90">
                    A growing community in Floridablanca, Pampanga, guided by active local leadership, strong bayanihan spirit, and
                    continuous programs that support families, youth, and senior citizens.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#about-system" class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-900/20 hover:bg-gray-50 transition-all duration-300">
                        Learn More
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                    </a>
                    <a href="#contact" class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/20 hover:bg-white/20 backdrop-blur-sm transition-all duration-300">
                        Contact Us
                    </a>
                </div>
            </div>

            {{-- Right: Abstract government seal illustration --}}
            <div class="hidden lg:flex justify-end fade-in fade-delay-2">
                <div class="relative">
                    <div class="absolute -inset-4 rounded-full bg-white/5 blur-2xl"></div>
                    <div class="relative flex h-64 w-64 items-center justify-center rounded-full border border-white/10 bg-white/5 backdrop-blur-sm">
                        <div class="flex h-48 w-48 items-center justify-center rounded-full border border-white/10 bg-white/5">
                            <div class="flex h-32 w-32 items-center justify-center rounded-full bg-white/10">
                                <svg class="h-16 w-16 text-white/80" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Wave divider --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 80V40C240 70 480 10 720 40C960 70 1200 10 1440 40V80H0Z" fill="#F9FAFB"/>
        </svg>
    </div>
</section>

{{-- ============================================================
     ABOUT SYSTEM — Feature cards with top border accent
     ============================================================ --}}
<section id="about-system" class="bg-gray-50 py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mx-auto max-w-3xl text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">About the Barangay</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">About Barangay Paguiruan</h2>
            <p class="mt-4 text-gray-600 leading-relaxed">
                Barangay Paguiruan is one of the active communities in Floridablanca, Pampanga, known for strong local leadership,
                close community ties, and resident-centered public service. The barangay continuously works to improve everyday
                services, community programs, and communication so residents can access support, information, and assistance more
                efficiently.
            </p>
        </div>

        <div class="mx-auto mt-14 grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-3">
            <div class="fade-in fade-delay-1 rounded-2xl border-t-4 border-blue-600 bg-white p-7 shadow-md hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-6 w-6 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                    </svg>
                </div>
                <h3 class="mt-5 text-base font-semibold tracking-tight text-gray-900">Active Community Programs</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">Barangay Paguiruan continues to support programs for health, sanitation, youth development, and livelihood for local families.</p>
            </div>
            <div class="fade-in fade-delay-2 rounded-2xl border-t-4 border-green-600 bg-white p-7 shadow-md hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50">
                    <svg class="h-6 w-6 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <h3 class="mt-5 text-base font-semibold tracking-tight text-gray-900">Transparent Local Governance</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">Barangay officials promote accountability through open communication, public advisories, and regular community coordination.</p>
            </div>
            <div class="fade-in fade-delay-3 rounded-2xl border-t-4 border-blue-600 bg-white p-7 shadow-md hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-6 w-6 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                </div>
                <h3 class="mt-5 text-base font-semibold tracking-tight text-gray-900">Inclusive Public Service</h3>
                <p class="mt-2 text-sm text-gray-600 leading-relaxed">Barangay services are focused on accessibility and fairness so residents from all puroks can receive timely support and assistance.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     GEOGRAPHY — Premium split layout with map
     ============================================================ --}}
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:items-start">
            {{-- Left: Description --}}
            <div class="fade-in">
                <p class="text-sm font-semibold uppercase tracking-widest text-green-600">Location</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50">
                        <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Our Barangay</h2>
                </div>

                <div class="mt-6 space-y-4 text-gray-600 leading-relaxed">
                    <p>
                        <strong class="text-gray-900">Barangay Paguiruan</strong> is located in the Municipality of
                        <strong class="text-gray-900">Floridablanca</strong>, Province of
                        <strong class="text-gray-900">Pampanga</strong>, in Central Luzon, Philippines. The barangay is
                        a vibrant community known for its warm residents and strong sense of local governance.
                    </p>
                    <p>
                        The Barangay Hall serves as the center of governance and community services, offering
                        document processing, dispute resolution, and various social programs.
                    </p>
                </div>

                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-3.5 w-3.5 text-green-700" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Strategic location in Floridablanca, Pampanga
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-3.5 w-3.5 text-green-700" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Accessible roads to town proper and neighboring barangays
                    </li>
                    <li class="flex items-center gap-3 text-sm text-gray-700">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-3.5 w-3.5 text-green-700" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        </span>
                        Peaceful, community-centered environment
                    </li>
                </ul>

                {{-- Purok switcher --}}
                <div class="mt-8 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <label for="purok-select" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">View by Purok</label>
                    <select id="purok-select" class="w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm ring-1 ring-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition">
                        <option value="default">Barangay Paguiruan (Overview)</option>
                        <option value="purok1">Purok 1</option>
                        <option value="purok2">Purok 2</option>
                        <option value="purok3">Purok 3</option>
                        <option value="purok4">Purok 4</option>
                        <option value="purok5">Purok 5</option>
                        <option value="purok6">Purok 6</option>
                    </select>
                </div>
            </div>

            {{-- Right: Map --}}
            <div class="fade-in fade-delay-2">
                <div class="overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200">
                    <iframe
                        id="barangay-map"
                        src="https://maps.google.com/maps?q=Barangay%20Paguiruan%20Floridablanca%20Pampanga&z=15&output=embed"
                        class="w-full h-[400px]"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <div class="border-t border-gray-100 bg-gray-50 px-4 py-2.5">
                        <p class="text-xs text-gray-500 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            Barangay Paguiruan, Floridablanca, Pampanga
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     COMMUNITY SLIDESHOW
     ============================================================ --}}
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-10 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Gallery</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Barangay Photo Highlights</h2>
            <p class="mt-3 text-gray-600">Key locations and community spaces in Barangay Paguiruan.</p>
        </div>
        @php
            $aboutSlides = [
                ['file' => 'barangayhall.jpg', 'label' => 'Barangay Hall'],
                ['file' => 'health center.jpg', 'label' => 'Health Center'],
                ['file' => 'park1.jpg', 'label' => 'Community Park'],
                ['file' => 'street1.jpg', 'label' => 'Barangay Street'],
            ];
        @endphp
        <div class="fade-in relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md" id="about-slideshow">
            @foreach ($aboutSlides as $i => $slide)
                <div class="about-slide {{ $i === 0 ? '' : 'hidden' }}">
                    <img src="{{ asset('images/ui design/' . $slide['file']) }}" alt="{{ $slide['label'] }}" class="h-[360px] w-full object-cover sm:h-[420px]">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-5">
                        <p class="text-sm font-semibold uppercase tracking-wider text-white">{{ $slide['label'] }}</p>
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
                @foreach ($aboutSlides as $i => $slide)
                    <button type="button" class="about-slide-dot h-2 w-2 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/50' }}" data-slide-index="{{ $i }}" aria-label="Go to slide {{ $i + 1 }}"></button>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     POPULATION — Horizontal stats strip
     ============================================================ --}}
<section class="bg-gray-50 py-16 px-6">
    <div class="mx-auto max-w-7xl">
        @php
            $communityStats = $communityStats ?? [
                'total_people' => 0,
                'total_households' => 0,
                'total_puroks' => 0,
            ];
        @endphp
        <div class="text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Demographics</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Community at a Glance</h2>
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
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-gray-500">Total Residents</p>
                </div>
                <div class="stat-divider flex flex-col items-center border-t md:border-t-0 py-10 px-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-green-50">
                        <svg class="h-7 w-7 text-green-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-700">{{ number_format((int) ($communityStats['total_households'] ?? 0)) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-gray-500">Households</p>
                </div>
                <div class="flex flex-col items-center border-t md:border-t-0 py-10 px-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-7 w-7 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                        </svg>
                    </div>
                    <p class="mt-4 text-4xl font-bold text-blue-700">{{ number_format((int) ($communityStats['total_puroks'] ?? 0)) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-gray-500">Puroks (Zones)</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     MISSION & VISION — Glass cards with left border accent
     ============================================================ --}}
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-14 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Our Purpose</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Mission & Vision</h2>
        </div>
        <div class="mx-auto grid max-w-5xl grid-cols-1 gap-8 md:grid-cols-2">
            <div class="fade-in fade-delay-1 group rounded-2xl border-l-4 border-blue-700 bg-white p-8 shadow-md ring-1 ring-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-700">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                    </svg>
                </div>
                <h3 class="mt-6 text-lg font-semibold tracking-tight text-gray-900">Our Mission</h3>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    To provide responsive, fair, and people-centered public service that protects community welfare,
                    strengthens peace and order, and supports sustainable growth for every family in Barangay Paguiruan.
                </p>
            </div>
            <div class="fade-in fade-delay-2 group rounded-2xl border-l-4 border-green-600 bg-white p-8 shadow-md ring-1 ring-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-600">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="mt-6 text-lg font-semibold tracking-tight text-gray-900">Our Vision</h3>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    A progressive and united Barangay Paguiruan where governance is transparent, opportunities are inclusive,
                    and every resident actively contributes to a safe and thriving community.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     IMPORTANCE — Full-width highlight block
     ============================================================ --}}
<section class="bg-blue-50 py-16 px-6">
    <div class="mx-auto max-w-4xl fade-in">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Community Priorities</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">What Matters in Barangay Paguiruan</h2>
            <p class="mx-auto mt-4 max-w-2xl text-gray-600 leading-relaxed">
                Barangay Paguiruan focuses on programs and actions that directly improve daily life,
                strengthen community relationships, and build long-term local resilience.
            </p>
        </div>
        <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="fade-in fade-delay-1 flex items-start gap-4 rounded-2xl bg-white p-6 shadow-md ring-1 ring-blue-100/50 hover:shadow-xl transition-all duration-300">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100">
                    <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-tight text-gray-900">Health and Cleanliness</h4>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed">Continuous support for health campaigns, clean surroundings, and safe community spaces for all residents.</p>
                </div>
            </div>
            <div class="fade-in fade-delay-2 flex items-start gap-4 rounded-2xl bg-white p-6 shadow-md ring-1 ring-blue-100/50 hover:shadow-xl transition-all duration-300">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                    <svg class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-tight text-gray-900">Youth and Education Support</h4>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed">Programs that encourage leadership, learning, and civic participation among children and young adults.</p>
                </div>
            </div>
            <div class="fade-in fade-delay-3 flex items-start gap-4 rounded-2xl bg-white p-6 shadow-md ring-1 ring-blue-100/50 hover:shadow-xl transition-all duration-300">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100">
                    <svg class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-tight text-gray-900">Peace and Order</h4>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed">Strong coordination with local leaders and authorities to keep neighborhoods safe and maintain harmony.</p>
                </div>
            </div>
            <div class="fade-in fade-delay-4 flex items-start gap-4 rounded-2xl bg-white p-6 shadow-md ring-1 ring-blue-100/50 hover:shadow-xl transition-all duration-300">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100">
                    <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold tracking-tight text-gray-900">Disaster Preparedness</h4>
                    <p class="mt-1 text-sm text-gray-600 leading-relaxed">Preparedness planning and community awareness help residents respond quickly during emergencies.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     DEVELOPERS — Clean government-style professional cards
     ============================================================ --}}
<section class="bg-white py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-14 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Leadership</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Barangay Officials</h2>
            <p class="mt-3 text-gray-600">Current officials including chairman, kagawads, and other active leadership roles.</p>
        </div>

        @php
            $officialCards = collect($officialCards ?? []);
            $officialSlides = $officialCards->chunk(4)->values();
        @endphp

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
                                <div class="fade-in fade-delay-{{ min($i + 1, 4) }} rounded-2xl bg-white p-6 text-center shadow-md ring-1 ring-gray-100 hover:shadow-xl transition-all duration-300">
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

{{-- ============================================================
     CONTACT — Official government-style layout
     ============================================================ --}}
<section id="contact" class="bg-gray-50 py-16 px-6">
    <div class="mx-auto max-w-7xl">
        <div class="text-center mb-12 fade-in">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Get in Touch</p>
            <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Contact Information</h2>
        </div>
        <div class="fade-in mx-auto max-w-4xl rounded-2xl bg-white shadow-md ring-1 ring-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 divide-y md:grid-cols-3 md:divide-y-0 md:divide-x divide-gray-100">
                {{-- Address --}}
                <div class="flex items-start gap-4 p-7">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Address</h3>
                        <p class="mt-1 text-sm text-gray-600 leading-relaxed">Barangay Paguiruan,<br>Floridablanca, Pampanga</p>
                    </div>
                </div>
                {{-- Phone --}}
                <div class="flex items-start gap-4 p-7">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-50">
                        <svg class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Phone</h3>
                        <p class="mt-1 text-sm text-gray-600">(096) 1254-9690</p>
                    </div>
                </div>
                {{-- Email --}}
                <div class="flex items-start gap-4 p-7">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                        <svg class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Email</h3>
                        <p class="mt-1 text-sm text-gray-600">barangaypaguiruan2024@gmail.com</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 bg-gray-50 px-7 py-3">
                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Office Hours: Monday – Friday, 8:00 AM – 5:00 PM
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ======================== SCRIPTS ======================== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

    document.querySelectorAll('.fade-in').forEach(function(el) {
        observer.observe(el);
    });

    document.getElementById('purok-select').addEventListener('change', function() {
        var mapEl = document.getElementById('barangay-map');
        var purokMaps = {
            'default': 'https://maps.google.com/maps?q=Barangay%20Paguiruan%20Floridablanca%20Pampanga&z=15&output=embed',
            'purok1': 'https://maps.google.com/maps?q=Paguiruan%20Purok%201%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok2': 'https://maps.google.com/maps?q=Paguiruan%20Purok%202%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok3': 'https://maps.google.com/maps?q=Paguiruan%20Purok%203%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok4': 'https://maps.google.com/maps?q=Paguiruan%20Purok%204%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok5': 'https://maps.google.com/maps?q=Paguiruan%20Purok%205%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok6': 'https://maps.google.com/maps?q=Paguiruan%20Purok%206%20Floridablanca%20Pampanga&z=16&output=embed'
        };
        mapEl.src = purokMaps[this.value] || purokMaps['default'];
    });

    var slides = Array.from(document.querySelectorAll('#about-slideshow .about-slide'));
    var dots = Array.from(document.querySelectorAll('#about-slideshow .about-slide-dot'));
    var prevBtn = document.getElementById('about-slide-prev');
    var nextBtn = document.getElementById('about-slide-next');
    var activeSlide = 0;
    var slideTimer = null;

    function showSlide(index) {
        if (!slides.length) return;
        activeSlide = (index + slides.length) % slides.length;

        slides.forEach(function (slide, i) {
            slide.classList.toggle('hidden', i !== activeSlide);
        });
        dots.forEach(function (dot, i) {
            dot.classList.toggle('bg-white', i === activeSlide);
            dot.classList.toggle('bg-white/50', i !== activeSlide);
        });
    }

    function startAutoSlide() {
        if (!slides.length) return;
        if (slideTimer) clearInterval(slideTimer);
        slideTimer = setInterval(function () {
            showSlide(activeSlide + 1);
        }, 4000);
    }

    if (slides.length) {
        prevBtn.addEventListener('click', function () {
            showSlide(activeSlide - 1);
            startAutoSlide();
        });
        nextBtn.addEventListener('click', function () {
            showSlide(activeSlide + 1);
            startAutoSlide();
        });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showSlide(Number(dot.dataset.slideIndex || 0));
                startAutoSlide();
            });
        });
        showSlide(0);
        startAutoSlide();
    }

    var officialSlides = Array.from(document.querySelectorAll('#officials-carousel .officials-slide'));
    var officialDots = Array.from(document.querySelectorAll('#officials-carousel .officials-slide-dot'));
    var officialPrevBtn = document.getElementById('officials-slide-prev');
    var officialNextBtn = document.getElementById('officials-slide-next');
    var activeOfficialSlide = 0;
    var officialTimer = null;

    function showOfficialSlide(index) {
        if (!officialSlides.length) return;
        activeOfficialSlide = (index + officialSlides.length) % officialSlides.length;

        officialSlides.forEach(function (slide, i) {
            slide.classList.toggle('hidden', i !== activeOfficialSlide);
        });
        officialDots.forEach(function (dot, i) {
            dot.classList.toggle('bg-blue-600', i === activeOfficialSlide);
            dot.classList.toggle('bg-gray-300', i !== activeOfficialSlide);
        });
    }

    function startOfficialAutoSlide() {
        if (officialSlides.length <= 1) return;
        if (officialTimer) clearInterval(officialTimer);
        officialTimer = setInterval(function () {
            showOfficialSlide(activeOfficialSlide + 1);
        }, 5000);
    }

    if (officialSlides.length) {
        if (officialPrevBtn) {
            officialPrevBtn.addEventListener('click', function () {
                showOfficialSlide(activeOfficialSlide - 1);
                startOfficialAutoSlide();
            });
        }
        if (officialNextBtn) {
            officialNextBtn.addEventListener('click', function () {
                showOfficialSlide(activeOfficialSlide + 1);
                startOfficialAutoSlide();
            });
        }
        officialDots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                showOfficialSlide(Number(dot.dataset.slideIndex || 0));
                startOfficialAutoSlide();
            });
        });
        showOfficialSlide(0);
        startOfficialAutoSlide();
    }
});
</script>
@endsection

@section('custom_footer')
    @include('partials.public-footer')
@endsection
