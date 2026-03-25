<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-Governance System — Barangay Paguiruan, Floridablanca</title>
    <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
            <style>
        body { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        .fade-in { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .fade-in.show { opacity: 1; transform: translateY(0); }
        .fade-delay-1 { transition-delay: 0.1s; }
        .fade-delay-2 { transition-delay: 0.2s; }
        .fade-delay-3 { transition-delay: 0.3s; }
        .fade-delay-4 { transition-delay: 0.4s; }
            </style>
</head>
<body class="min-h-screen bg-white antialiased text-gray-700">

{{-- ============================================================
     NAVBAR
     ============================================================ --}}
<header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/80 backdrop-blur-lg">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 md:px-6">
        <a href="/" class="flex items-center gap-2.5 group">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo" class="h-9 w-auto shrink-0 object-contain">
            <span class="text-sm md:text-base font-semibold tracking-tight text-gray-800 group-hover:text-blue-600 transition">Barangay Paguiruan, Floridablanca</span>
        </a>

        <div class="hidden md:flex items-center gap-1">
            @php
                $navLink = 'px-3 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200 relative';
            @endphp
            <a href="#hero" class="{{ $navLink }}">Home</a>
            <a href="#services" class="{{ $navLink }}">Services</a>
            <a href="#announcements" class="{{ $navLink }}">Announcements</a>
            <a href="#how-it-works" class="{{ $navLink }}">How It Works</a>
            <a href="#about-preview" class="{{ $navLink }}">About</a>
            <a href="#contact" class="{{ $navLink }}">Contact</a>
        </div>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ route('resident.dashboard') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Log In</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Register</a>
        @endif
            @endauth

            {{-- Mobile menu --}}
            <details class="relative md:hidden group">
                <summary class="flex list-none items-center justify-center h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer transition [&::-webkit-details-marker]:hidden">
                    <svg class="h-5 w-5 group-open:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    <svg class="h-5 w-5 hidden group-open:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </summary>
                <div class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white py-2 shadow-lg z-50">
                    <a href="#hero" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Home</a>
                    <a href="#services" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Services</a>
                    <a href="#announcements" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Announcements</a>
                    <a href="#how-it-works" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">How It Works</a>
                    <a href="#about-preview" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">About</a>
                    <a href="#contact" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Contact</a>
                </div>
            </details>
        </div>
    </nav>
</header>

{{-- ============================================================
     HERO SECTION
     ============================================================ --}}
<section id="hero" class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700">
    <div class="absolute inset-0">
        <img src="{{ asset('images/ui design/barangayhall.jpg') }}" alt="Barangay Hall" class="h-full w-full object-cover opacity-35">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/70 via-blue-800/65 to-indigo-900/70"></div>
    </div>
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;0.3&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-20 md:px-6 md:py-28 lg:py-36">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="fade-in">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium text-white backdrop-blur-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Official Barangay Portal
                </div>
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white md:text-5xl lg:text-6xl">
                    e-Governance<br>
                    <span class="text-blue-200">System</span>
                </h1>
                <p class="mt-4 max-w-lg text-lg leading-relaxed text-blue-100">
                    Bringing Barangay Services Closer to the Community. Request certificates, file complaints, and track permits — all online.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('resident.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-blue-600 shadow-lg hover:bg-blue-50 transition-all duration-200 hover:shadow-xl">
                            Go to Dashboard
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-blue-600 shadow-lg hover:bg-blue-50 transition-all duration-200 hover:shadow-xl">
                            Get Started
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                        <a href="#services" class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10 transition-all duration-200">
                            Learn More
                        </a>
                    @endauth
                </div>
            </div>

            <div class="fade-in fade-delay-2 hidden lg:block">
                <div class="relative mx-auto max-w-md">
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-6 backdrop-blur-sm shadow-2xl">
                        <div class="mb-4 flex items-center gap-2">
                            <div class="h-3 w-3 rounded-full bg-red-400"></div>
                            <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                            <div class="h-3 w-3 rounded-full bg-green-400"></div>
                            <span class="ml-2 text-xs font-medium text-white/60">e-Governance Dashboard</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 rounded-xl bg-white/15 p-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-400/20"><svg class="h-5 w-5 text-green-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                <div><p class="text-sm font-semibold text-white">Certificate Ready</p><p class="text-xs text-white/60">Barangay Clearance approved</p></div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl bg-white/15 p-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-400/20"><svg class="h-5 w-5 text-blue-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>
                                <div><p class="text-sm font-semibold text-white">Permit Processing</p><p class="text-xs text-white/60">Business permit under review</p></div>
                            </div>
                            <div class="flex items-center gap-3 rounded-xl bg-white/15 p-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-400/20"><svg class="h-5 w-5 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg></div>
                                <div><p class="text-sm font-semibold text-white">New Announcement</p><p class="text-xs text-white/60">Community clean-up this Saturday</p></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full"><path d="M0 60V20C240 0 480 0 720 20C960 40 1200 40 1440 20V60H0Z" fill="white"/></svg>
    </div>
</section>

{{-- ============================================================
     FEATURES / CORE SERVICES
     ============================================================ --}}
<section id="services" class="bg-white py-20 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-12 text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">What We Offer</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-800 md:text-4xl">Core Services</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">Access all essential barangay services from your device — no long lines, no wasted time.</p>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('login') }}" class="fade-in fade-delay-1 group block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <div class="mb-4 overflow-hidden rounded-xl border border-gray-100">
                    <img src="{{ asset('images/ui design/barangayhall.jpg') }}" alt="Barangay Hall Services" class="h-32 w-full object-cover transition-transform duration-300 group-hover:scale-105">
                </div>
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Certificate Requests</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Request barangay clearance, indigency certificates, and more with just a few clicks.</p>
            </a>
            <a href="{{ route('login') }}" class="fade-in fade-delay-2 group block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-green-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <div class="mb-4 overflow-hidden rounded-xl border border-gray-100">
                    <img src="{{ asset('images/ui design/health center.jpg') }}" alt="Health Center Service" class="h-32 w-full object-cover transition-transform duration-300 group-hover:scale-105">
                </div>
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-600 transition-colors group-hover:bg-green-600 group-hover:text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Permit Applications</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Apply for business and construction permits online. Track status in real time.</p>
            </a>
            <a href="{{ route('login') }}" class="fade-in fade-delay-3 group block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:border-amber-200 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <div class="mb-4 overflow-hidden rounded-xl border border-gray-100">
                    <img src="{{ asset('images/ui design/street1.jpg') }}" alt="Street Community Access" class="h-32 w-full object-cover transition-transform duration-300 group-hover:scale-105">
                </div>
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition-colors group-hover:bg-amber-600 group-hover:text-white">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Complaints & Grievances</h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed">Report issues in your community. Every concern is tracked and resolved transparently.</p>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     ANNOUNCEMENTS
     ============================================================ --}}
<section id="announcements" class="scroll-mt-24 bg-gray-50 py-20 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-12 text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Latest Updates</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-800 md:text-4xl">Announcements</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">See the most recent barangay announcements and community notices.</p>
        </div>
        @if (($latestAnnouncements ?? collect())->isEmpty())
            <div class="fade-in rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
                <p class="text-sm text-gray-500">No announcements available yet.</p>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestAnnouncements as $index => $announcement)
                    <article class="fade-in fade-delay-{{ min($index + 1, 4) }} rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-lg">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                            {{ optional($announcement->published_at)->format('M d, Y') ?? $announcement->created_at->format('M d, Y') }}
                        </p>
                        <h3 class="mt-2 text-lg font-semibold leading-snug text-gray-800 line-clamp-2">{{ $announcement->title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-gray-600">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $announcement->content), 130) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ============================================================
     COMMUNITY GALLERY
     ============================================================ --}}
<section class="bg-white py-16 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-10 text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Community Spaces</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-800 md:text-4xl">Barangay Highlights</h2>
            <p class="mx-auto mt-3 max-w-2xl text-gray-500">A quick look at key places around Barangay Paguiruan, Floridablanca.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @php
                $galleryImages = [
                    ['file' => 'purok1.jpg', 'label' => 'Purok 1'],
                    ['file' => 'purok2.jpg', 'label' => 'Purok 2'],
                    ['file' => 'purok3.jpg', 'label' => 'Purok 3'],
                    ['file' => 'purok4.jpg', 'label' => 'Purok 4'],
                    ['file' => 'purok5.jpg', 'label' => 'Purok 5'],
                ];
            @endphp
            @foreach ($galleryImages as $index => $image)
                <div class="fade-in fade-delay-{{ min($index + 1, 4) }} overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <img src="{{ asset('images/ui design/' . $image['file']) }}" alt="{{ $image['label'] }}" class="h-44 w-full object-cover">
                    <div class="border-t border-gray-100 px-3 py-2 text-center text-xs font-semibold tracking-wide text-gray-600 uppercase">
                        {{ $image['label'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS
     ============================================================ --}}
<section id="how-it-works" class="bg-gray-50 py-20 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-14 text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Simple Process</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-800 md:text-4xl">How It Works</h2>
            <p class="mx-auto mt-3 max-w-xl text-gray-500">Get your barangay services done in four easy steps.</p>
        </div>
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $steps = [
                    ['num' => '01', 'title' => 'Register', 'desc' => 'Create your account with your personal details for verification.', 'color' => 'blue'],
                    ['num' => '02', 'title' => 'Submit Request', 'desc' => 'Choose the service you need and fill out the online form.', 'color' => 'green'],
                    ['num' => '03', 'title' => 'Processing', 'desc' => 'Barangay staff reviews and processes your request promptly.', 'color' => 'amber'],
                    ['num' => '04', 'title' => 'Get Result', 'desc' => 'Receive notification when your document is ready for release.', 'color' => 'indigo'],
                ];
            @endphp
            @foreach ($steps as $i => $step)
                <div class="fade-in fade-delay-{{ $i + 1 }} text-center">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-{{ $step['color'] }}-100 text-{{ $step['color'] }}-600">
                        <span class="text-2xl font-extrabold">{{ $step['num'] }}</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     STATISTICS
     ============================================================ --}}
<section class="bg-white py-20 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="fade-in rounded-2xl bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 p-10 md:p-14 shadow-xl">
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-bold text-white md:text-4xl">Trusted by the Community</h2>
                <p class="mt-2 text-blue-200">Delivering efficient barangay services to the people of Paguiruan.</p>
            </div>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                @php
                    $stats = [
                        ['value' => number_format((int) ($communityStats['residents_served'] ?? 0)), 'label' => 'Residents Served'],
                        ['value' => number_format((int) ($communityStats['certificates_issued'] ?? 0)), 'label' => 'Certificates Issued'],
                        ['value' => number_format((int) ($communityStats['complaints_resolved'] ?? 0)), 'label' => 'Complaints Resolved'],
                        ['value' => number_format((int) ($communityStats['announcements_published'] ?? 0)), 'label' => 'Announcements Published'],
                    ];
                @endphp
                @foreach ($stats as $i => $stat)
                    <div class="fade-in fade-delay-{{ $i + 1 }} text-center">
                        <p class="text-3xl font-extrabold text-white md:text-4xl">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-sm font-medium text-blue-200 uppercase tracking-wide">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ABOUT PREVIEW
     ============================================================ --}}
<section id="about-preview" class="bg-gray-50 py-20 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="fade-in">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">About the System</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-800 md:text-4xl">Modernizing Barangay Governance</h2>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    The e-Governance System is a digital platform designed to streamline barangay operations in Paguiruan, Floridablanca, Pampanga. It empowers residents with convenient online access to government services while providing officials with modern tools for transparent and efficient governance.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach (['Faster service delivery with digital workflows', 'Real-time tracking of requests and complaints', 'Transparent and accountable governance'] as $item)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm text-gray-600">{{ $item }}</span>
                        </li>
                    @endforeach
                    </ul>
                @auth
                    <a href="{{ route('about') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 transition">
                        Learn More
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endauth
            </div>
            <div class="fade-in fade-delay-2">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-2xl font-bold text-gray-800">24/7</p>
                            <p class="text-xs text-gray-500 mt-1">Online Access</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600 mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            </div>
                            <p class="text-2xl font-bold text-gray-800">100%</p>
                            <p class="text-xs text-gray-500 mt-1">Secure & Private</p>
                        </div>
                    </div>
                    <div class="mt-8 space-y-4">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                            </div>
                            <p class="text-2xl font-bold text-gray-800">Fast</p>
                            <p class="text-xs text-gray-500 mt-1">Digital Processing</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 mb-3">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                            </div>
                            <p class="text-2xl font-bold text-gray-800">6+</p>
                            <p class="text-xs text-gray-500 mt-1">Puroks Connected</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     MAP SECTION
     ============================================================ --}}
<section id="contact" class="bg-white py-20 px-4 md:px-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-12 text-center fade-in">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Find Us</p>
            <h2 class="mt-2 text-3xl font-bold tracking-tight text-gray-800 md:text-4xl">Our Location</h2>
            <p class="mt-3 text-gray-500">Barangay Paguiruan, Floridablanca, Pampanga, Philippines</p>
        </div>
        <div class="fade-in grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <iframe
                    src="https://maps.google.com/maps?q=Barangay%20Paguiruan%20Floridablanca%20Pampanga&t=&z=15&ie=UTF8&iwloc=&output=embed"
                    class="w-full h-[400px] rounded-2xl border border-gray-200 shadow-sm"
                    loading="lazy"
                    allowfullscreen>
                </iframe>
            </div>
            <div class="space-y-5">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Address</p>
                            <p class="mt-1 text-sm text-gray-500">Barangay Paguiruan, Floridablanca, Pampanga, Philippines</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Phone</p>
                            <p class="mt-1 text-sm text-gray-500">(096) 1254-9690</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Email</p>
                            <p class="mt-1 text-sm text-gray-500">barangaypaguiruan2024@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CALL TO ACTION
     ============================================================ --}}
<section class="bg-gray-50 py-20 px-4 md:px-6">
    <div class="mx-auto max-w-3xl text-center fade-in">
        <div class="rounded-2xl border border-gray-200 bg-white p-10 shadow-sm md:p-14">
            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 md:text-3xl">Experience Faster and More Transparent Barangay Services</h2>
            <p class="mx-auto mt-3 max-w-lg text-gray-500">Join thousands of Paguiruan residents who are already using the e-Governance System for convenient, digital government services.</p>
            @guest
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200">
                    Register Now
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            @endguest
            @auth
                <a href="{{ route('resident.dashboard') }}" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-8 py-3.5 text-sm font-semibold text-white shadow-lg hover:bg-blue-700 hover:shadow-xl transition-all duration-200">
                    Go to Dashboard
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            @endauth
        </div>
    </div>
</section>

{{-- ============================================================
     FOOTER
     ============================================================ --}}
@include('partials.public-footer')

{{-- ============================================================
     SCRIPTS — Fade-in on scroll
     ============================================================ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    });
</script>

    </body>
</html>
