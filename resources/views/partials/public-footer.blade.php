@php
    $isAuthenticated = auth()->check();
    $homeUrl = $isAuthenticated ? route('resident.dashboard') : url('/');
    $aboutUrl = $isAuthenticated ? route('about') : route('login');
    $documentsUrl = $isAuthenticated ? route('resident.certificates.index') : route('login');
    $announcementsUrl = $isAuthenticated ? route('resident.announcements.index') : route('login');
@endphp

<footer class="bg-blue-900 text-white">
    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Barangay Paguiruan</p>
                        <p class="text-xs text-blue-300">Floridablanca, Pampanga</p>
                    </div>
                </div>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-blue-200">
                    Serving the residents of Barangay Paguiruan through responsive governance, active community programs, and inclusive public service.
                </p>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-widest text-blue-300">Quick Links</h3>
                <ul class="mt-4 space-y-2.5">
                    <li><a href="{{ $homeUrl }}" class="text-sm text-blue-200 transition hover:text-white">Home</a></li>
                    <li><a href="{{ $aboutUrl }}" class="text-sm text-blue-200 transition hover:text-white">About Us</a></li>
                    <li><a href="{{ $documentsUrl }}" class="text-sm text-blue-200 transition hover:text-white">Request Documents</a></li>
                    <li><a href="{{ $announcementsUrl }}" class="text-sm text-blue-200 transition hover:text-white">Announcements</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-widest text-blue-300">Contact</h3>
                <ul class="mt-4 space-y-2.5">
                    <li class="flex items-center gap-2 text-sm text-blue-200">
                        <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        Barangay Paguiruan, Floridablanca, Pampanga
                    </li>
                    <li class="flex items-center gap-2 text-sm text-blue-200">
                        <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                        (096) 1254-9690
                    </li>
                    <li class="flex items-center gap-2 text-sm text-blue-200">
                        <svg class="h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        barangaypaguiruan2024@gmail.com
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="border-t border-blue-800">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-6 py-4 sm:flex-row">
            <p class="text-xs text-blue-300">&copy; {{ date('Y') }} Barangay Paguiruan. All rights reserved.</p>
            <p class="text-xs text-blue-400">Republic of the Philippines</p>
        </div>
    </div>
</footer>
