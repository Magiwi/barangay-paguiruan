<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'About Us - Barangay Paguiruan')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>[x-cloak]{display:none!important}</style>
    @include('partials.ui-foundation-styles')
    @include('partials.a11y-skip-link-styles')
</head>
<body class="min-h-screen bg-gray-50 font-[Inter,sans-serif] antialiased">
    @include('partials.a11y-skip-link')

    <header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/80 backdrop-blur-lg">
        <nav class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo1.png') }}" alt="Barangay Paguiruan official seal" class="h-9 w-auto shrink-0 object-contain">
                    <span class="text-sm md:text-base font-semibold tracking-tight text-gray-800 group-hover:text-gray-900 transition">Barangay Paguiruan, Floridablanca</span>
                </a>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/') }}" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 sm:inline">Home</a>
                    @auth
                        <a href="{{ route('resident.dashboard') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Log In</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content" class="flex min-h-[calc(100vh-8rem)] flex-col">
        @yield('content')
    </main>

    @hasSection('custom_footer')
        @yield('custom_footer')
    @else
        @include('partials.public-footer')
    @endif
</body>
</html>
