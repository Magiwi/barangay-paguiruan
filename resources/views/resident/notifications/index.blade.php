@extends('layouts.resident')

@section('title', 'Notifications - e-Governance System')

@section('content')
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Notifications</h1>
                <p class="text-gray-600 mt-0.5">Stay updated on your requests and announcements.</p>
            </div>
            @php $unread = auth()->user()->unreadNotificationsCount(); @endphp
            @if ($unread > 0)
                <form method="POST" action="{{ route('resident.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="ui-focus-ring inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Mark all as read ({{ $unread }})
                    </button>
                </form>
            @endif
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        {{-- Notifications list --}}
        <div class="ui-surface-card overflow-hidden">
            @forelse ($notifications as $notification)
                <a href="{{ route('resident.notifications.open', $notification) }}"
                   class="ui-focus-ring flex items-start gap-3 border-b border-gray-100 px-5 py-4 transition hover:bg-gray-50 last:border-b-0 {{ $notification->is_read ? 'bg-white' : 'bg-blue-50/35 border-l-4 border-l-blue-500' }}">
                    {{-- Type icon --}}
                    <div class="mt-0.5 shrink-0">
                        @switch($notification->type)
                            @case('certificate')
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100">
                                    <svg class="h-4.5 w-4.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                                @break
                            @case('permit')
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-green-100">
                                    <svg class="h-4.5 w-4.5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                    </svg>
                                </div>
                                @break
                            @case('complaint')
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-yellow-100">
                                    <svg class="h-4.5 w-4.5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                </div>
                                @break
                            @case('blotter')
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-purple-100">
                                    <svg class="h-4.5 w-4.5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                    </svg>
                                </div>
                                @break
                            @case('announcement')
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-sky-100">
                                    <svg class="h-4.5 w-4.5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46"/>
                                    </svg>
                                </div>
                                @break
                            @default
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100">
                                    <svg class="h-4.5 w-4.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                    </svg>
                                </div>
                        @endswitch
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold tracking-tight text-gray-800 {{ $notification->is_read ? '' : 'text-blue-900' }}">
                            {{ $notification->title }}
                            @if (! $notification->is_read)
                                <span class="ml-1 inline-block h-2 w-2 rounded-full bg-blue-500"></span>
                            @endif
                        </p>
                        <p class="mt-0.5 text-sm text-gray-600 leading-relaxed">{{ $notification->message }}</p>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $notification->created_at->format('M d, Y h:i A') }}
                            <span class="text-gray-300 mx-1">&middot;</span>
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <svg class="h-14 w-14 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    <p class="mt-3 text-gray-600 font-medium">No notifications yet</p>
                    <p class="mt-1 text-sm text-gray-500">You'll be notified when your requests are updated.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</section>
@endsection
