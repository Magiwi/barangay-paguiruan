@extends('layouts.resident')

@section('title', 'Dashboard - e-Governance System')

@section('content')
<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 md:px-6">

    {{-- Welcome Card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-800 via-blue-700 to-green-600 p-6 shadow-lg ring-1 ring-blue-900/20 md:p-8">
        <div class="absolute inset-0 opacity-[0.08]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-rule=&quot;evenodd&quot;%3E%3Cpath d=&quot;M0 40L40 0H20L0 20M40 40V20L20 40&quot;/%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-100">Resident Dashboard</p>
                <h1 class="mt-1 text-xl font-semibold tracking-tight text-white sm:text-2xl">
                    Welcome back, {{ auth()->user()->first_name }}
                </h1>
                <p class="mt-1 text-sm text-blue-100/90">Your barangay services and updates in one place.</p>
            </div>
            @if ($unreadNotifications > 0)
                <a href="{{ route('resident.notifications.index') }}" class="ui-focus-ring inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    {{ $unreadNotifications }} Unread
                </a>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <section>
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Quick Actions</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <a href="{{ route('resident.certificates.create') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex items-center gap-3 p-4">
                <div class="ui-icon-chip">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 transition group-hover:text-blue-700">Request Certificate</span>
            </a>
            <a href="{{ route('resident.permits.create') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex items-center gap-3 p-4">
                <div class="ui-icon-chip">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 transition group-hover:text-blue-700">Apply for Permit</span>
            </a>
            <a href="{{ route('resident.issues.create') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex items-center gap-3 p-4">
                <div class="ui-icon-chip">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <span class="text-sm font-medium text-gray-700 transition group-hover:text-blue-700">File Complaint</span>
            </a>
            <a href="{{ route('resident.notifications.index') }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring flex items-center gap-3 p-4">
                <div class="ui-icon-chip relative">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    @if ($unreadNotifications > 0)
                        <span class="absolute -top-1 -right-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                    @endif
                </div>
                <span class="text-sm font-medium text-gray-700 transition group-hover:text-blue-700">Notifications</span>
            </a>
        </div>
    </section>

    {{-- Community Highlights --}}
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Community Highlights</h2>
            <span class="text-xs text-gray-400">Updates & Info</span>
        </div>
        <div class="ui-surface-card p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold tracking-tight text-gray-900">Stay updated with barangay announcements</h3>
                    <p class="mt-1 text-sm text-gray-500">Get the latest community advisories, event schedules, and important public service notices.</p>
                </div>
                <a href="{{ route('resident.announcements.index') }}"
                   class="ui-focus-ring inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                    View Announcements
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Stats Cards --}}
    <section>
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">My Requests Overview</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            {{-- Certificates --}}
            <div class="group ui-surface-card ui-surface-card-hover h-full p-5">
                <div class="flex items-center justify-between">
                    <div class="ui-icon-chip bg-blue-50 text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ (int) $certificates->total }}</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold tracking-tight text-gray-900">Certificates</h3>
                <div class="mt-1.5 flex flex-wrap gap-x-3 text-xs text-gray-500">
                    @if ((int) $certificates->pending > 0)<span class="text-yellow-600 font-medium">{{ (int) $certificates->pending }} pending</span>@endif
                    @if ((int) $certificates->approved > 0)<span class="text-green-600 font-medium">{{ (int) $certificates->approved }} approved</span>@endif
                    @if ((int) $certificates->released > 0)<span>{{ (int) $certificates->released }} released</span>@endif
                    @if ((int) $certificates->total === 0)<span>No requests yet</span>@endif
                </div>
                <a href="{{ route('resident.certificates.index') }}" class="ui-focus-ring mt-3 inline-flex items-center gap-1 rounded-md text-xs font-medium text-blue-600 transition hover:text-blue-700">
                    View all <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>

            {{-- Permits --}}
            <div class="group ui-surface-card ui-surface-card-hover h-full p-5">
                <div class="flex items-center justify-between">
                    <div class="ui-icon-chip bg-emerald-50 text-emerald-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ (int) $permits->total }}</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold tracking-tight text-gray-900">Permits</h3>
                <div class="mt-1.5 flex flex-wrap gap-x-3 text-xs text-gray-500">
                    @if ((int) $permits->pending > 0)<span class="text-yellow-600 font-medium">{{ (int) $permits->pending }} pending</span>@endif
                    @if ((int) $permits->approved > 0)<span class="text-green-600 font-medium">{{ (int) $permits->approved }} approved</span>@endif
                    @if ((int) $permits->released > 0)<span>{{ (int) $permits->released }} released</span>@endif
                    @if ((int) $permits->total === 0)<span>No applications yet</span>@endif
                </div>
                <a href="{{ route('resident.permits.index') }}" class="ui-focus-ring mt-3 inline-flex items-center gap-1 rounded-md text-xs font-medium text-emerald-600 transition hover:text-emerald-700">
                    View all <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>

            {{-- Complaints --}}
            <div class="group ui-surface-card ui-surface-card-hover h-full p-5">
                <div class="flex items-center justify-between">
                    <div class="ui-icon-chip bg-amber-50 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ (int) $complaints->total }}</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold tracking-tight text-gray-900">Complaints</h3>
                <div class="mt-1.5 flex flex-wrap gap-x-3 text-xs text-gray-500">
                    @if ((int) $complaints->pending > 0)<span class="text-yellow-600 font-medium">{{ (int) $complaints->pending }} pending</span>@endif
                    @if ((int) $complaints->in_progress > 0)<span class="text-blue-600 font-medium">{{ (int) $complaints->in_progress }} in progress</span>@endif
                    @if ((int) $complaints->resolved > 0)<span class="text-green-600">{{ (int) $complaints->resolved }} resolved</span>@endif
                    @if ((int) $complaints->total === 0)<span>No complaints filed</span>@endif
                </div>
                <a href="{{ route('resident.issues.index') }}" class="ui-focus-ring mt-3 inline-flex items-center gap-1 rounded-md text-xs font-medium text-amber-600 transition hover:text-amber-700">
                    View all <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>

            {{-- Blotter Requests --}}
            <div class="group ui-surface-card ui-surface-card-hover h-full p-5">
                <div class="flex items-center justify-between">
                    <div class="ui-icon-chip bg-violet-50 text-violet-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ (int) $blotterRequests->total }}</span>
                </div>
                <h3 class="mt-3 text-sm font-semibold tracking-tight text-gray-900">Blotter Requests</h3>
                <div class="mt-1.5 flex flex-wrap gap-x-3 text-xs text-gray-500">
                    @if ((int) $blotterRequests->pending > 0)<span class="text-yellow-600 font-medium">{{ (int) $blotterRequests->pending }} pending</span>@endif
                    @if ((int) $blotterRequests->approved > 0)<span class="text-green-600 font-medium">{{ (int) $blotterRequests->approved }} approved</span>@endif
                    @if ((int) $blotterRequests->released > 0)<span>{{ (int) $blotterRequests->released }} released</span>@endif
                    @if ((int) $blotterRequests->total === 0)<span>No requests yet</span>@endif
                </div>
                <a href="{{ route('resident.blotter-requests.index') }}" class="ui-focus-ring mt-3 inline-flex items-center gap-1 rounded-md text-xs font-medium text-violet-600 transition hover:text-violet-700">
                    View all <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Recent Announcements --}}
    <section>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Recent Announcements</h2>
            <a href="{{ route('resident.announcements.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">View all &rarr;</a>
        </div>
        @if ($recentAnnouncements->isEmpty())
            <div class="ui-surface-card p-10 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/></svg>
                <p class="mt-2 text-sm text-gray-500">No announcements at the moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach ($recentAnnouncements as $announcement)
                    <a href="{{ route('resident.announcements.show', $announcement) }}" class="group ui-surface-card ui-surface-card-hover ui-focus-ring overflow-hidden">
                        @if ($announcement->image)
                            <div class="h-36 overflow-hidden bg-gray-100">
                                <img src="{{ asset('storage/' . $announcement->image) }}" alt="" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div class="h-1.5 bg-blue-500"></div>
                        @endif
                        <div class="p-5">
                            <h3 class="font-semibold tracking-tight text-gray-800 group-hover:text-blue-600 transition line-clamp-2">{{ $announcement->title }}</h3>
                            <p class="mt-2 text-sm text-gray-500 line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($announcement->content), 120) }}</p>
                            <p class="mt-3 text-xs text-gray-400">{{ $announcement->published_at?->format('M d, Y') ?? $announcement->created_at->format('M d, Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection
