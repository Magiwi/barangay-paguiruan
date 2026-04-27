<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Barangay Paguiruan') — e-Governance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
    @include('partials.ui-foundation-styles')
    @include('partials.a11y-skip-link-styles')
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 antialiased">
    @include('partials.a11y-skip-link')
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex h-14 max-w-4xl items-center justify-between gap-4 px-4 md:px-6">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2 text-sm font-semibold text-gray-800 transition hover:text-[var(--brand-700)]">
                <img src="{{ asset('images/logo1.png') }}" alt="Barangay Paguiruan official seal" class="h-8 w-auto shrink-0">
                <span class="truncate">Barangay Paguiruan</span>
            </a>
            <nav class="flex shrink-0 items-center gap-1 sm:gap-3" aria-label="{{ __('public.footer.quick_links') }}">
                <a href="{{ url('/') }}" class="ui-link hidden text-sm sm:inline">{{ __('public.footer.home') }}</a>
                <a href="{{ route('about') }}" class="ui-link text-sm">{{ __('public.footer.about') }}</a>
                @auth
                    <a href="{{ route('resident.dashboard') }}" class="ui-btn ui-btn-primary ui-btn-sm rounded-lg">{{ __('public.nav_dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="ui-btn ui-btn-primary ui-btn-sm rounded-lg">{{ __('public.nav_login') }}</a>
                @endauth
            </nav>
        </div>
    </header>
    <main id="main-content">
        @yield('content')
    </main>
    @include('partials.public-footer')
</body>
</html>
