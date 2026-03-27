<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'e-Governance System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet">
    <style>[x-cloak]{display:none!important}</style>
    @include('partials.ui-foundation-styles')
</head>
<body class="min-h-screen bg-gray-50 font-[Inter,sans-serif] antialiased">

    {{-- Sticky Glass Navbar --}}
    <header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/80 backdrop-blur-lg">
        <nav class="mx-auto max-w-7xl px-4 md:px-6">
            <div class="flex h-16 items-center justify-between">

                {{-- Left: Logo --}}
                <a href="{{ route('resident.dashboard') }}" class="flex items-center gap-2.5 group">
                    <img src="{{ asset('images/logo1.png') }}" alt="Logo" class="h-9 w-auto shrink-0 object-contain">
                    <span class="text-sm md:text-base font-semibold tracking-tight text-gray-800 group-hover:text-gray-900 transition">Barangay Paguiruan, Floridablanca</span>
                </a>

                {{-- Center: Desktop Nav --}}
                <div class="hidden lg:flex lg:items-center lg:gap-1">
                    @php
                        $navLink = 'relative px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors duration-200';
                        $navActive = 'relative px-3 py-2 text-sm font-medium ui-topnav-active';
                        $navUnderline = 'absolute bottom-0 left-3 right-3 h-0.5 rounded-full ui-topnav-underline';
                        $navSummaryBase = 'flex list-none items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium cursor-pointer transition [&::-webkit-details-marker]:hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-200';
                        $dropdownPanelBase = 'absolute left-0 z-50 mt-3 hidden rounded-xl border border-gray-200 bg-white py-1.5 shadow-lg ring-1 ring-gray-100 group-open:block';
                        $dropdownItem = 'block rounded-lg px-4 py-2.5 text-sm text-gray-600 transition hover:bg-gray-50 hover:text-gray-900';
                    @endphp

                    <a href="{{ route('resident.dashboard') }}" class="{{ request()->routeIs('resident.dashboard') ? $navActive : $navLink }}">
                        Home
                        @if(request()->routeIs('resident.dashboard'))<span class="{{ $navUnderline }}"></span>@endif
                    </a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? $navActive : $navLink }}">
                        About
                        @if(request()->routeIs('about'))<span class="{{ $navUnderline }}"></span>@endif
                    </a>

                    @php $isOfficials = request()->routeIs('resident.officials.*'); @endphp
                    <details class="relative group">
                        <summary class="{{ $isOfficials ? $navActive : $navLink }} {{ $navSummaryBase }}">
                            Officials
                            <svg class="h-3.5 w-3.5 opacity-50 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            @if($isOfficials)<span class="{{ $navUnderline }}"></span>@endif
                        </summary>
                        <div class="{{ $dropdownPanelBase }} w-56">
                            <a href="{{ route('resident.officials.council') }}" class="{{ $dropdownItem }}">Barangay Council</a>
                            <a href="{{ route('resident.officials.sk') }}" class="{{ $dropdownItem }}">SK Officials</a>
                        </div>
                    </details>

                    @php $isServices = request()->routeIs('resident.certificates.*') || request()->routeIs('resident.issues.*') || request()->routeIs('resident.permits.*') || request()->routeIs('resident.blotter-requests.*'); @endphp
                    <details class="relative group">
                        <summary class="{{ $isServices ? $navActive : $navLink }} {{ $navSummaryBase }}">
                            Services
                            <svg class="h-3.5 w-3.5 opacity-50 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            @if($isServices)<span class="{{ $navUnderline }}"></span>@endif
                        </summary>
                        <div class="{{ $dropdownPanelBase }} w-72">
                            <a href="{{ route('resident.certificates.index') }}" class="{{ $dropdownItem }}">Request Documents</a>
                            <a href="{{ route('resident.permits.index') }}" class="{{ $dropdownItem }}">Apply for Permit</a>
                            <a href="{{ route('resident.issues.index') }}" class="{{ $dropdownItem }}">Report Issue</a>
                            <a href="{{ route('resident.blotter-requests.index') }}" class="{{ $dropdownItem }}">Request Blotter</a>
                        </div>
                    </details>

                    <a href="{{ route('resident.announcements.index') }}" class="{{ request()->routeIs('resident.announcements.*') ? $navActive : $navLink }}">
                        Announcements
                        @if(request()->routeIs('resident.announcements.*'))<span class="{{ $navUnderline }}"></span>@endif
                    </a>
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Notification Bell --}}
                    @php $unreadCount = auth()->user()->unreadNotificationsCount(); @endphp
                    <details class="relative group" id="notif-bell">
                        <summary class="flex list-none items-center justify-center h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 cursor-pointer transition [&::-webkit-details-marker]:hidden relative focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            @if ($unreadCount > 0)
                                <span class="absolute top-0.5 right-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                            @endif
                        </summary>
                        <div class="absolute right-0 z-50 mt-3 hidden w-80 rounded-xl border border-gray-200 bg-white shadow-lg ring-1 ring-gray-100 group-open:block">
                            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('resident.notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium ui-link">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            @php $latestNotifs = auth()->user()->userNotifications()->latest()->take(5)->get(); @endphp
                            @if ($latestNotifs->isEmpty())
                                <div class="px-4 py-8 text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    <p class="mt-2 text-xs text-gray-400">No notifications yet</p>
                                </div>
                            @else
                                <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                                    @foreach ($latestNotifs as $notif)
                                        <a href="{{ route('resident.notifications.open', $notif) }}"
                                           class="block px-4 py-3 transition hover:bg-gray-50 {{ $notif->is_read ? '' : 'bg-green-50/50' }}">
                                            <p class="text-sm font-medium text-gray-800 truncate">{{ $notif->title }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                            <p class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <div class="border-t border-gray-100">
                                <a href="{{ route('resident.notifications.index') }}" class="block px-4 py-2.5 text-center text-xs font-medium ui-link hover:bg-gray-50 rounded-b-xl">View All</a>
                            </div>
                        </div>
                    </details>

                    {{-- Profile Dropdown --}}
                    <details class="relative group hidden md:block">
                        <summary class="flex list-none items-center gap-2 cursor-pointer rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition [&::-webkit-details-marker]:hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-200">
                            <div class="ui-avatar-circle h-7 w-7 text-xs">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                            </div>
                            <span class="hidden sm:inline max-w-[100px] truncate">{{ auth()->user()->first_name }}</span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="absolute right-0 z-50 mt-3 hidden w-56 rounded-xl border border-gray-200 bg-white py-1.5 shadow-lg ring-1 ring-gray-100 group-open:block">
                            <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->full_name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            @php $role = auth()->user()->role; @endphp
                            @if (in_array($role, ['staff', 'admin']))
                                <a href="{{ route('staff.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">Staff Panel</a>
                            @endif
                            @if (in_array($role, ['admin', 'super_admin'], true))
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">Admin Panel</a>
                            @endif
                            @if (in_array($role, ['staff', 'admin']) && auth()->user()->hasModuleAccess('registrations'))
                                <a href="{{ route('admin.pending-registrations.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">Pending Registrations</a>
                            @endif
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ url('/profile') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">Profile Settings</a>
                            <a href="{{ url('/profile/password') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">Change Password</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 hover:text-red-700">Sign Out</button>
                            </form>
                        </div>
                    </details>

                    {{-- Mobile Menu --}}
                    <details class="relative lg:hidden group">
                        <summary class="flex list-none items-center justify-center h-9 w-9 rounded-lg text-gray-500 hover:bg-gray-100 cursor-pointer transition [&::-webkit-details-marker]:hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-200">
                            <svg class="h-5 w-5 group-open:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                            <svg class="h-5 w-5 hidden group-open:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </summary>
                        <div class="absolute right-0 z-50 mt-3 hidden w-72 rounded-xl border border-gray-200 bg-white py-2 shadow-lg ring-1 ring-gray-100 group-open:block">
                            <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                <p class="text-sm font-medium text-gray-800">{{ auth()->user()->full_name }}</p>
                                <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('resident.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Home</a>
                            <a href="{{ route('about') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">About</a>
                            <a href="{{ route('resident.officials.council') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Barangay Council</a>
                            <a href="{{ route('resident.officials.sk') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">SK Officials</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('resident.certificates.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Request Documents</a>
                            <a href="{{ route('resident.permits.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Apply for Permit</a>
                            <a href="{{ route('resident.issues.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Report Issue</a>
                            <a href="{{ route('resident.blotter-requests.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Request Blotter</a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('resident.announcements.index') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Announcements</a>
                            <a href="{{ route('resident.notifications.index') }}" class="flex items-center justify-between px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                Notifications
                                @if ($unreadCount > 0)
                                    <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                                @endif
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ url('/profile') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Profile Settings</a>
                            @php $roleMobile = auth()->user()->role; @endphp
                            @if (in_array($roleMobile, ['staff', 'admin']))
                                <a href="{{ route('staff.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Staff Panel</a>
                            @endif
                            @if (in_array($roleMobile, ['admin', 'super_admin'], true))
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">Admin Panel</a>
                            @endif
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-500 hover:bg-red-50">Sign Out</button>
                            </form>
                        </div>
                    </details>
                </div>
            </div>
        </nav>
    </header>

    {{-- Main --}}
    <main class="min-h-[calc(100vh-8rem)] flex flex-col">
        @yield('content')
    </main>

    {{-- Footer --}}
    @hasSection('custom_footer')
        @yield('custom_footer')
    @else
        @include('partials.public-footer')
    @endif

<script>
    (function () {
        const dropdowns = Array.from(document.querySelectorAll('header details'));
        if (!dropdowns.length) return;

        const closeAll = () => {
            dropdowns.forEach((d) => d.removeAttribute('open'));
        };

        dropdowns.forEach((current) => {
            current.addEventListener('toggle', () => {
                if (!current.open) return;
                dropdowns.forEach((d) => {
                    if (d !== current) d.removeAttribute('open');
                });
            });
        });

        document.addEventListener('click', (event) => {
            const insideAny = dropdowns.some((d) => d.contains(event.target));
            if (!insideAny) closeAll();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAll();
        });
    })();
</script>
</body>
</html>
