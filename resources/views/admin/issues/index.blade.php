@extends($layout ?? 'layouts.admin')

@section('title', 'Complaints Management - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Complaints Management</h1>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        {{-- ════════════════════════════════════ --}}
        {{-- Statistics Dashboard (clickable)     --}}
        {{-- ════════════════════════════════════ --}}
        <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Total --}}
            <a href="{{ route($rp . '.issues.index') }}" class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-center hover:border-gray-300 transition block">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats->total ?? 0) }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1">Total</p>
            </a>
            {{-- Pending --}}
            <a href="{{ route($rp . '.issues.index', ['status' => 'pending']) }}" class="rounded-2xl border border-amber-200 bg-amber-50 shadow-sm p-4 text-center hover:border-amber-300 transition block">
                <p class="text-2xl font-bold text-amber-700">{{ number_format($stats->pending ?? 0) }}</p>
                <p class="text-xs font-medium text-amber-600 mt-1">Pending</p>
            </a>
            {{-- In Progress --}}
            <a href="{{ route($rp . '.issues.index', ['status' => 'in_progress']) }}" class="rounded-2xl border border-blue-200 bg-blue-50 shadow-sm p-4 text-center hover:border-blue-300 transition block">
                <p class="text-2xl font-bold text-blue-700">{{ number_format($stats->in_progress ?? 0) }}</p>
                <p class="text-xs font-medium text-blue-600 mt-1">In Progress</p>
            </a>
            {{-- Resolved --}}
            <a href="{{ route($rp . '.issues.index', ['status' => 'resolved']) }}" class="rounded-2xl border border-green-200 bg-green-50 shadow-sm p-4 text-center hover:border-green-300 transition block">
                <p class="text-2xl font-bold text-green-700">{{ number_format($stats->resolved ?? 0) }}</p>
                <p class="text-xs font-medium text-green-600 mt-1">Resolved</p>
            </a>
            {{-- Closed --}}
            <a href="{{ route($rp . '.issues.index', ['status' => 'closed']) }}" class="rounded-2xl border border-gray-200 bg-gray-50 shadow-sm p-4 text-center hover:border-gray-300 transition block">
                <p class="text-2xl font-bold text-gray-700">{{ number_format($stats->closed ?? 0) }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1">Closed</p>
            </a>
            {{-- Avg Resolution --}}
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 text-center">
                @php
                    $hours = $stats->avg_resolution_hours ?? 0;
                    if ($hours >= 24) {
                        $display = number_format($hours / 24, 1) . 'd';
                    } else {
                        $display = number_format($hours, 1) . 'h';
                    }
                @endphp
                <p class="text-2xl font-bold text-gray-900">{{ $hours > 0 ? $display : '—' }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1">Avg Resolution</p>
            </div>
        </div>

        {{-- ════════════════════════════════════ --}}
        {{-- Filters                              --}}
        {{-- ════════════════════════════════════ --}}
        <form method="GET" action="{{ route($rp . '.issues.index') }}" class="mb-5 rounded-2xl border border-gray-200 bg-white shadow-sm p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                {{-- Search --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject, name..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        <option value="">All Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                        <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    </select>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <select name="category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Assigned Officer --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Assigned Officer</label>
                    <select name="assigned_to" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        <option value="">All Officers</option>
                        @foreach ($officers as $officer)
                            <option value="{{ $officer->id }}" @selected(request('assigned_to') == $officer->id)>
                                {{ $officer->first_name }} {{ $officer->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Purok --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Purok</label>
                    <select name="purok_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        <option value="">All Puroks</option>
                        @foreach ($puroks as $purok)
                            <option value="{{ $purok->id }}" @selected(request('purok_id') == $purok->id)>{{ $purok->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-2">
                <button type="submit" class="ui-btn ui-btn-primary rounded-lg">
                    Apply Filters
                </button>
                @if (request()->anyFilled(['search', 'status', 'category', 'assigned_to', 'purok_id']))
                    <a href="{{ route($rp . '.issues.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear all</a>
                @endif
            </div>
        </form>

        {{-- ════════════════════════════════════ --}}
        {{-- Table                                --}}
        {{-- ════════════════════════════════════ --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @if ($issues->isEmpty())
                <div class="p-8 text-center text-gray-600">
                    @if (request()->anyFilled(['search', 'status', 'category', 'assigned_to', 'purok_id']))
                        No complaints match the current filters.
                    @else
                        No complaints yet.
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Complainant</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Officer</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Open</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($issues as $issue)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $issue->user->first_name }} {{ $issue->user->last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $issue->user->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-medium text-gray-900 flex items-center gap-1">
                                            @if ($issue->attachment_path)
                                                <span title="Has attachment">📎</span>
                                            @endif
                                            {{ Str::limit($issue->subject, 40) }}
                                        </p>
                                        @if ($issue->location)
                                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                                <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                {{ Str::limit($issue->location, 30) }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($issue->category)
                                            @php
                                                $highPriority = in_array($issue->category, ['Safety', 'Illegal Activity'], true);
                                                $medPriority = in_array($issue->category, ['Infrastructure', 'Flooding'], true);
                                            @endphp
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
                                                {{ $highPriority ? 'bg-red-100 text-red-700' : ($medPriority ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700') }}">
                                                @if ($highPriority || $medPriority)
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $highPriority ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                                                @endif
                                                {{ $issue->category }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($issue->status === 'pending')
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                        @elseif ($issue->status === 'in_progress')
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">In Progress</span>
                                        @elseif ($issue->status === 'resolved')
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-800">Resolved</span>
                                        @else
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-200 text-gray-700">Closed</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($issue->assignedOfficer)
                                            <p class="text-sm text-gray-900">{{ $issue->assignedOfficer->first_name }} {{ $issue->assignedOfficer->last_name }}</p>
                                        @else
                                            <span class="text-xs text-gray-400">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        @php $daysOpen = $issue->daysOpen(); @endphp
                                        <span class="font-medium {{ $daysOpen > 7 ? 'text-red-600' : ($daysOpen > 3 ? 'text-amber-600' : 'text-gray-600') }}">
                                            {{ $daysOpen }} day{{ $daysOpen === 1 ? '' : 's' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @php
                                            $canAct = in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN], true)
                                                || (int) $issue->assigned_to === (int) auth()->id();
                                            $tooltip = $canAct ? '' : 'title="Only assigned officer or admin can perform this action"';
                                        @endphp
                                        <div class="flex flex-wrap items-center justify-center gap-2">
                                            <a href="{{ route($rp . '.issues.show', $issue) }}" class="ui-btn ui-btn-primary ui-btn-sm inline-flex min-w-[62px] justify-center rounded-md" title="View">View</a>
                                            @if (! $issue->assigned_to && ! $issue->isClosed())
                                                <form method="POST" action="{{ route($rp . '.issues.assign-me', $issue) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="min-w-[62px] rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700" title="Assign to me">Assign</button>
                                                </form>
                                            @endif
                                            @if ($issue->status === 'pending' && $issue->canTransitionTo('in_progress'))
                                                <form method="POST" action="{{ route($rp . '.issues.update', $issue) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="in_progress">
                                                    <input type="hidden" name="remarks" value="">
                                                    @if ($canAct)
                                                        <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm min-w-[62px] rounded-md" title="Start Investigation">Start</button>
                                                    @else
                                                        <button type="button" disabled {{ $tooltip }} class="min-w-[62px] cursor-not-allowed rounded-md bg-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-500">Start</button>
                                                    @endif
                                                </form>
                                            @endif
                                            @if ($issue->status === 'in_progress' && $issue->canTransitionTo('resolved'))
                                                @if ($canAct)
                                                    <a href="{{ route($rp . '.issues.show', $issue) }}" class="inline-flex min-w-[62px] items-center justify-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700" title="Resolve (opens form on detail page)">Resolve</a>
                                                @else
                                                    <button type="button" disabled {{ $tooltip }} class="min-w-[62px] cursor-not-allowed rounded-md bg-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-500">Resolve</button>
                                                @endif
                                            @endif
                                            @if ($issue->status === 'resolved' && $issue->canTransitionTo('closed'))
                                                <form method="POST" action="{{ route($rp . '.issues.update', $issue) }}" class="inline" onsubmit="return confirm('Close this case? This cannot be undone.');">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="closed">
                                                    <input type="hidden" name="remarks" value="{{ $issue->resolution_notes ?? $issue->remarks ?? 'Closed' }}">
                                                    @if ($canAct)
                                                        <button type="submit" class="min-w-[62px] rounded-md bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800" title="Close">Close</button>
                                                    @else
                                                        <button type="button" disabled {{ $tooltip }} class="min-w-[62px] cursor-not-allowed rounded-md bg-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-500">Close</button>
                                                    @endif
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($issues->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $issues->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>
@endsection
