@extends($layout ?? 'layouts.admin')

@section('title', 'Announcements - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">Create and manage barangay announcements.</p>
            </div>
            <a href="{{ route($rp . '.announcements.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                Create Announcement
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        {{-- Status Tabs --}}
        <div class="mb-6 flex items-center gap-1 overflow-x-auto border-b border-gray-200">
            <a href="{{ route($rp . '.announcements.index', array_merge(request()->except('tab', 'page'), ['tab' => 'active'])) }}"
               class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition {{ $filter === 'active' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All Active
                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $counts['active'] }}</span>
            </a>
            <a href="{{ route($rp . '.announcements.index', array_merge(request()->except('tab', 'page'), ['tab' => 'pending'])) }}"
               class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition {{ $filter === 'pending' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending Approval
                @if ($counts['pending'] > 0)
                    <span class="ml-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">{{ $counts['pending'] }}</span>
                @else
                    <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">0</span>
                @endif
            </a>
            <a href="{{ route($rp . '.announcements.index', array_merge(request()->except('tab', 'page'), ['tab' => 'rejected'])) }}"
               class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition {{ $filter === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Rejected
                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $counts['rejected'] }}</span>
            </a>
            <a href="{{ route($rp . '.announcements.index', array_merge(request()->except('tab', 'page'), ['tab' => 'archived'])) }}"
               class="whitespace-nowrap px-4 py-2.5 text-sm font-medium border-b-2 transition {{ $filter === 'archived' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Archived
                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $counts['archived'] }}</span>
            </a>
        </div>

        {{-- Label Filter --}}
        @if ($labels->isNotEmpty())
            <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm px-5 py-4">
                <form method="GET" action="{{ route($rp . '.announcements.index') }}" class="flex flex-wrap items-end gap-4">
                    <input type="hidden" name="tab" value="{{ $filter }}">
                    <div class="flex-1 min-w-[180px]">
                        <label for="label" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Filter by Label</label>
                        <select name="label" id="label" onchange="this.form.submit()" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="">All Labels</option>
                            @foreach ($labels as $label)
                                <option value="{{ $label->slug }}" @selected(request('label') === $label->slug)>{{ $label->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (request('label'))
                        <a href="{{ route($rp . '.announcements.index', ['tab' => $filter]) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Clear Filter
                        </a>
                    @endif
                </form>
            </div>
        @endif

        @if ($announcements->isEmpty())
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-12 text-center text-gray-600">
                @if ($filter === 'archived')
                    <p>No archived announcements.</p>
                @else
                    <p>No announcements yet.</p>
                    <a href="{{ route($rp . '.announcements.create') }}" class="mt-3 inline-block text-sm font-medium text-blue-600 hover:text-blue-700">Create one</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($announcements as $announcement)
                    @php $isEmergency = $announcement->labels->contains('slug', 'emergency'); @endphp
                    <article class="flex flex-col overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-shadow {{ $announcement->trashed() ? 'opacity-60 border border-gray-300 bg-gray-50' : ($isEmergency ? 'border-2 border-red-300 bg-red-50/50' : 'border border-gray-200 bg-white') }}">
                        @if ($announcement->image)
                            <img src="{{ asset('storage/' . $announcement->image) }}" alt="" class="h-40 w-full object-cover object-center">
                        @else
                            @if ($isEmergency)
                                <div class="h-2 bg-gradient-to-r from-red-500 to-red-600" aria-hidden="true"></div>
                            @elseif ($announcement->status === 'approved')
                                <div class="h-2 bg-gradient-to-r from-green-600 to-green-700" aria-hidden="true"></div>
                            @elseif ($announcement->status === 'pending')
                                <div class="h-2 bg-gradient-to-r from-amber-500 to-amber-600" aria-hidden="true"></div>
                            @elseif ($announcement->status === 'rejected')
                                <div class="h-2 bg-gradient-to-r from-red-400 to-red-500" aria-hidden="true"></div>
                            @else
                                <div class="h-2 bg-gradient-to-r from-gray-400 to-gray-500" aria-hidden="true"></div>
                            @endif
                        @endif
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                @if ($announcement->trashed())
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-gray-200 text-gray-600">Archived</span>
                                @elseif ($announcement->status === 'approved')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                @elseif ($announcement->status === 'pending')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                @elseif ($announcement->status === 'rejected')
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                @else
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-gray-100 text-gray-700">Draft</span>
                                @endif
                                @if ($isEmergency)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-700 border border-red-200">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Emergency
                                    </span>
                                @endif
                            </div>
                            <h2 class="font-semibold text-gray-900 line-clamp-2">{{ $announcement->title }}</h2>
                            @if ($announcement->labels->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($announcement->labels as $label)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $label->color }}">{{ $label->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <p class="mt-2 text-sm text-gray-600 line-clamp-3 flex-1">
                                {{ Str::limit(strip_tags($announcement->content), 120) }}
                            </p>
                            <p class="mt-4 text-xs text-gray-500">
                                {{ $announcement->published_at ? $announcement->published_at->format('M d, Y') : $announcement->created_at->format('M d, Y') }}
                            </p>
                            {{-- Actions --}}
                            <div class="mt-4 pt-4 border-t {{ $isEmergency && !$announcement->trashed() ? 'border-red-100' : 'border-gray-100' }} flex flex-wrap items-center gap-2">
                                @if ($announcement->trashed())
                                    <form method="POST" action="{{ route($rp . '.announcements.restore', $announcement->id) }}" class="inline" onsubmit="return confirm('Restore this announcement?');">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-green-600 hover:text-green-700">Restore</button>
                                    </form>
                                @else
                                    {{-- Approve / Reject actions for pending or rejected --}}
                                    @if (in_array($announcement->status, ['pending', 'rejected', 'draft']))
                                        <form method="POST" action="{{ route($rp . '.announcements.approve', $announcement) }}" class="inline" onsubmit="return confirm('Approve and publish this announcement?');">
                                            @csrf
                                            <button type="submit" class="rounded-md bg-green-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-green-700 transition">Approve</button>
                                        </form>
                                    @endif
                                    @if (in_array($announcement->status, ['pending', 'approved']))
                                        <form method="POST" action="{{ route($rp . '.announcements.reject', $announcement) }}" class="inline" onsubmit="return confirm('Reject this announcement?');">
                                            @csrf
                                            <button type="submit" class="rounded-md bg-red-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-red-700 transition">Reject</button>
                                        </form>
                                    @endif
                                    <a href="{{ route($rp . '.announcements.edit', $announcement) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Edit</a>
                                    <span class="text-gray-300">|</span>
                                    <form method="POST" action="{{ route($rp . '.announcements.destroy', $announcement) }}" class="inline" onsubmit="return confirm('Archive this announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-amber-600 hover:text-amber-700">Archive</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $announcements->withQueryString()->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
