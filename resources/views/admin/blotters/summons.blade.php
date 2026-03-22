@extends($layout ?? 'layouts.admin')

@section('title', 'Summon Management - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <a href="{{ route($rp . '.blotters.index') }}" class="mb-2 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Blotter Records
                </a>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Summon Management</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Case <span class="font-medium">{{ $blotter->blotter_number }}</span> &middot;
                    Complainant: <span class="font-medium">{{ $blotter->complainant_name }}</span>
                </p>
                @if ($blotter->is_uncooperative)
                    <span class="mt-2 inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                        Uncooperative Case
                    </span>
                @endif
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm">
                <p class="font-medium text-gray-700">Summon Progress</p>
                <p class="text-lg font-semibold text-blue-700">{{ $summonCount }}/3</p>
            </div>
        </div>
        <div class="flex justify-end">
            <a href="{{ route($rp . '.blotters.hearings.index', $blotter) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                </svg>
                Manage Hearings
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-1">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Generate Summon</h2>

                @if ($summonCount >= 3)
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        Maximum of 3 summons reached for this case.
                    </div>
                @elseif (! $canGenerateNext)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Next summon can only be generated after the previous summon is marked as <strong>No Show</strong>.
                    </div>
                @elseif (empty($luponAssignees))
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        No eligible assignees found. Only users with assigned official position and blotter access appear in the dropdown.
                    </div>
                @endif

                <form method="POST" action="{{ route($rp . '.blotters.summons.store', $blotter) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="hearing_date" class="mb-1 block text-sm font-medium text-gray-700">Hearing Date</label>
                        <input
                            type="date"
                            id="hearing_date"
                            name="hearing_date"
                            value="{{ old('hearing_date') }}"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            required
                            @disabled(! $canGenerateNext || $summonCount >= 3)
                        >
                        @error('hearing_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="hearing_time" class="mb-1 block text-sm font-medium text-gray-700">Hearing Time</label>
                        <input
                            type="time"
                            id="hearing_time"
                            name="hearing_time"
                            value="{{ old('hearing_time') }}"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            required
                            @disabled(! $canGenerateNext || $summonCount >= 3)
                        >
                        @error('hearing_time')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="lupon_user_id" class="mb-1 block text-sm font-medium text-gray-700">Assigned Officer</label>
                        <select
                            id="lupon_user_id"
                            name="lupon_user_id"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            required
                            @disabled(! $canGenerateNext || $summonCount >= 3 || empty($luponAssignees))
                        >
                            <option value="">Select assignee</option>
                            @foreach(($luponAssignees ?? []) as $userId => $label)
                                <option value="{{ $userId }}" @selected((string) old('lupon_user_id') === (string) $userId)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('lupon_user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        @disabled(! $canGenerateNext || $summonCount >= 3 || empty($luponAssignees))
                    >
                        Generate Summon {{ min($summonCount + 1, 3) }} of 3
                    </button>
                </form>

                <div class="mt-6 border-t border-gray-200 pt-4">
                    <p class="mb-2 text-sm font-medium text-gray-700">Certification to File Action</p>
                    @if ($canGenerateCertification)
                        <a href="{{ route($rp . '.blotters.certification.print', $blotter) }}"
                           target="_blank"
                           class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                            Print Certification
                        </a>
                    @else
                        <p class="text-xs text-gray-500">
                            Available only when 3rd summon is marked as <strong>No Show</strong>.
                        </p>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Summon History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Summon #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Date Sent</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Hearing Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lupon Assigned</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($summons as $summon)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        Summon {{ $summon->summon_number }} of 3
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $summon->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $summon->hearing_date->format('M d, Y') }}
                                        at {{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', $summon->hearing_time)->format('h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $summon->lupon_assigned }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeClasses = match($summon->status) {
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'served' => 'bg-blue-100 text-blue-700',
                                                'no_show' => 'bg-red-100 text-red-700',
                                                'completed' => 'bg-green-100 text-green-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClasses }}">
                                            {{ str_replace('_', ' ', ucfirst($summon->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a
                                                href="{{ route($rp . '.blotters.summons.print', [$blotter, $summon]) }}"
                                                target="_blank"
                                                class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200"
                                            >
                                                Print
                                            </a>
                                            <form method="POST" action="{{ route($rp . '.blotters.summons.status', [$blotter, $summon]) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                                    @foreach (\App\Models\Summon::STATUSES as $status)
                                                        <option value="{{ $status }}" @selected($summon->status === $status)>
                                                            {{ str_replace('_', ' ', ucfirst($status)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700">
                                                    Update
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                        No summons generated for this blotter case yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
