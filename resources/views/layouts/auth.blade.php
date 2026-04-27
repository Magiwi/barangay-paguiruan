<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'e-Governance System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
    @include('partials.ui-foundation-styles')
    @include('partials.a11y-skip-link-styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 via-white to-gray-100 font-sans antialiased">
    @include('partials.a11y-skip-link')
    <div class="min-h-screen flex flex-col">
        <div class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <main id="main-content" class="w-full flex-shrink-0 flex justify-center px-4">
                @yield('content')
            </main>
        </div>
        @include('partials.public-footer')
    </div>
</body>
</html>
