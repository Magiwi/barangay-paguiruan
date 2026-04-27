@extends($layout ?? 'layouts.admin')

@section('title', 'Hearing Management - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <a href="{{ route($rp . '.blotters.summons.index', $blotter) }}" class="mb-2 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Summons
                </a>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Hearing Management</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Case <span class="font-medium">{{ $blotter->blotter_number }}</span> &middot;
                    {{ $hearings->count() }} hearing(s) tracked
                </p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm shadow-sm">
                <p class="font-medium text-gray-700">Summon Progress</p>
                <p class="text-lg font-semibold text-blue-700">{{ $summons->count() }}/3</p>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert type="error">
                <p class="font-medium">Please review the hearing form details.</p>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if ($blotter->trashed())
            <x-ui.alert type="info">
                This blotter record is <strong>archived</strong>. You can review the hearing timeline below, but scheduling and hearing actions are disabled.
            </x-ui.alert>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Create Hearing</h2>

                @if ($blotter->trashed())
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        Archived cases cannot have new hearings scheduled. Restore the blotter from the e-Blotter list if corrections are required.
                    </div>
                @elseif ($summons->isEmpty())
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Cannot create hearing without an existing summon.
                    </div>
                @elseif (empty($luponAssignees))
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        No eligible assignees found. Only users with assigned official position and blotter access appear in the dropdown.
                    </div>
                @endif

                @if (! $blotter->trashed())
                <form method="POST" action="{{ route($rp . '.blotters.hearings.store', $blotter) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="summon_id" class="mb-1 block text-sm font-medium text-gray-700">Linked Summon</label>
                        <select id="summon_id" name="summon_id" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" required @disabled($summons->isEmpty())>
                            <option value="">Select summon</option>
                            @foreach ($summons as $summon)
                                <option value="{{ $summon->id }}" @selected(old('summon_id') == $summon->id)>
                                    Summon {{ $summon->summon_number }} of 3 ({{ str_replace('_', ' ', ucfirst($summon->status)) }})
                                </option>
                            @endforeach
                        </select>
                        @error('summon_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hearing_date" class="mb-1 block text-sm font-medium text-gray-700">Hearing Date</label>
                        <input type="date" id="hearing_date" name="hearing_date" value="{{ old('hearing_date') }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                               required @disabled($summons->isEmpty())>
                        @error('hearing_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="hearing_time" class="mb-1 block text-sm font-medium text-gray-700">Hearing Time</label>
                        <input type="time" id="hearing_time" name="hearing_time" value="{{ old('hearing_time') }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                               required @disabled($summons->isEmpty())>
                        @error('hearing_time')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lupon_user_id" class="mb-1 block text-sm font-medium text-gray-700">Assigned Officer</label>
                        <select id="lupon_user_id" name="lupon_user_id"
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                required @disabled($summons->isEmpty() || empty($luponAssignees))>
                            <option value="">Select assignee</option>
                            @foreach(($luponAssignees ?? []) as $userId => $label)
                                <option value="{{ $userId }}" @selected((string) old('lupon_user_id') === (string) $userId)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('lupon_user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="ui-btn ui-btn-primary w-full rounded-lg py-2.5 disabled:cursor-not-allowed disabled:opacity-60"
                            @disabled($summons->isEmpty() || empty($luponAssignees))>
                        Schedule Hearing
                    </button>
                </form>
                @endif
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Hearing Timeline</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Summon</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Attendance</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Result</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($hearings as $hearing)
                                @php
                                    $statusBadge = match($hearing->status) {
                                        'scheduled' => 'bg-yellow-100 text-yellow-700',
                                        'ongoing' => 'bg-blue-100 text-blue-700',
                                        'done' => 'bg-green-100 text-green-700',
                                        'no_show' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };

                                    // Notes-only states:
                                    // - no_show
                                    // - rescheduled (status or result marker)
                                    // - done / settled
                                    $isNotesOnlyStatus = in_array($hearing->status, ['no_show', 'rescheduled', 'done', 'settled'], true);
                                    $isRescheduled = $hearing->result === 'reschedule';
                                    $isSettled = $hearing->result === 'settled';
                                    $notesOnlyActions = $isNotesOnlyStatus || $isRescheduled || $isSettled;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $hearing->hearing_date->format('M d, Y') }}
                                        at {{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', strlen($hearing->hearing_time) === 5 ? $hearing->hearing_time . ':00' : $hearing->hearing_time)->format('h:i A') }}
                                        <div class="text-xs text-gray-500">Lupon: {{ $hearing->lupon_assigned }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        @if($hearing->summon)
                                            Summon {{ $hearing->summon->summon_number }} of 3
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        C: {{ ucfirst($hearing->complainant_attendance ?? '—') }}<br>
                                        R: {{ ucfirst($hearing->respondent_attendance ?? '—') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusBadge }}">
                                            {{ str_replace('_', ' ', ucfirst($hearing->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $hearing->result ? str_replace('_', ' ', ucfirst($hearing->result)) : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex flex-wrap justify-end gap-2">
                                            @if (! $blotter->trashed())
                                            @if ($hearing->status === 'scheduled' && ! $notesOnlyActions)
                                                <button type="button"
                                                    class="ui-btn ui-btn-primary ui-btn-sm rounded-lg"
                                                    data-hearing-action="start"
                                                    data-hearing-id="{{ $hearing->id }}"
                                                    data-hearing-status="{{ $hearing->status }}"
                                                    data-start-url="{{ route($rp . '.blotters.hearings.start', [$blotter, $hearing]) }}"
                                                >
                                                    Start
                                                </button>
                                            @endif

                                            @if (! $notesOnlyActions)
                                                <button type="button"
                                                    class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                                                    data-hearing-action="no_show"
                                                    data-hearing-id="{{ $hearing->id }}"
                                                    data-hearing-status="{{ $hearing->status }}"
                                                    data-no-show-url="{{ route($rp . '.blotters.hearings.no-show', [$blotter, $hearing]) }}"
                                                >
                                                    No Show
                                                </button>

                                                <button type="button"
                                                    class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700"
                                                    data-hearing-action="complete"
                                                    data-hearing-id="{{ $hearing->id }}"
                                                    data-hearing-status="{{ $hearing->status }}"
                                                    data-complete-url="{{ route($rp . '.blotters.hearings.complete', [$blotter, $hearing]) }}"
                                                >
                                                    Complete
                                                </button>

                                                <button type="button"
                                                    class="rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-700"
                                                    data-hearing-action="reschedule"
                                                    data-hearing-id="{{ $hearing->id }}"
                                                    data-hearing-status="{{ $hearing->status }}"
                                                    data-reschedule-url="{{ route($rp . '.blotters.hearings.reschedule', [$blotter, $hearing]) }}"
                                                >
                                                    Reschedule
                                                </button>
                                            @endif

                                            <button type="button"
                                                class="rounded-lg bg-gray-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-800"
                                                data-hearing-action="notes"
                                                data-hearing-id="{{ $hearing->id }}"
                                                data-hearing-status="{{ $hearing->status }}"
                                                data-notes-url="{{ route($rp . '.blotters.hearings.notes', [$blotter, $hearing]) }}"
                                                data-current-notes="{{ e((string) ($hearing->notes ?? '')) }}"
                                            >
                                                Notes
                                            </button>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if ($hearing->reschedules->isNotEmpty())
                                    <tr class="bg-gray-50/60">
                                        <td colspan="6" class="px-6 pb-4 pt-0">
                                            <div class="mt-2 rounded-lg border border-gray-200 bg-white p-3">
                                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Reschedule History</p>
                                                <ul class="space-y-1 text-xs text-gray-600">
                                                    @foreach($hearing->reschedules as $history)
                                                        <li>
                                                            {{ $history->old_hearing_date->format('M d, Y') }} {{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', strlen($history->old_hearing_time) === 5 ? $history->old_hearing_time . ':00' : $history->old_hearing_time)->format('h:i A') }}
                                                            →
                                                            {{ $history->new_hearing_date->format('M d, Y') }} {{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', strlen($history->new_hearing_time) === 5 ? $history->new_hearing_time . ':00' : $history->new_hearing_time)->format('h:i A') }}
                                                            @if($history->reason)
                                                                ({{ $history->reason }})
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                 @endif
                             @empty
                                 <tr>
                                     <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                                         No hearings scheduled yet for this blotter case.
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

<div id="hearingActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
    <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h3 id="hearingModalTitle" class="text-base font-semibold text-gray-900">Hearing Action</h3>
                <p id="hearingModalSubtitle" class="text-xs text-gray-500">Select action details</p>
            </div>
            <button type="button" id="closeHearingModal" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                Close
            </button>
        </div>

        <div class="p-5">
            <form id="hearingFormStart" method="POST" class="hidden space-y-4">
                @csrf
                @method('PUT')
                <p class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                    This will mark the hearing as <strong>ongoing</strong>.
                </p>
                <button type="submit" class="ui-btn ui-btn-primary w-full rounded-lg py-2.5">
                    Confirm Start Hearing
                </button>
            </form>

            <form id="hearingFormNoShow" method="POST" class="hidden space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Complainant Attendance</label>
                    <select name="complainant_attendance" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">No Show Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Reason and context..."></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700">
                    Mark As No Show
                </button>
            </form>

            <form id="hearingFormComplete" method="POST" class="hidden space-y-3">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Complainant Attendance</label>
                        <select name="complainant_attendance" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Select</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Respondent Attendance</label>
                        <select name="respondent_attendance" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Select</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Result</label>
                    <select name="result" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Select result</option>
                        <option value="settled">Settled</option>
                        <option value="not_settled">Not Settled</option>
                        <option value="reschedule">For Further Hearing</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Discussion / Agreement Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Summary of mediation..."></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                    Mark As Done
                </button>
            </form>

            <form id="hearingFormReschedule" method="POST" class="hidden space-y-3">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">New Date</label>
                        <input type="date" name="new_hearing_date" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">New Time</label>
                        <input type="time" name="new_hearing_time" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reason</label>
                    <input type="text" name="reason" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Reason for rescheduling">
                </div>
                <button type="submit" class="w-full rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-700">
                    Save Reschedule
                </button>
            </form>

            <form id="hearingFormNotes" method="POST" class="hidden space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Hearing Notes</label>
                    <textarea name="notes" id="hearingNotesField" rows="4" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Add case notes..."></textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-gray-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-gray-800">
                    Save Notes
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('hearingActionModal');
    const titleEl = document.getElementById('hearingModalTitle');
    const subtitleEl = document.getElementById('hearingModalSubtitle');
    const closeBtn = document.getElementById('closeHearingModal');
    const notesField = document.getElementById('hearingNotesField');

    const forms = {
        start: document.getElementById('hearingFormStart'),
        no_show: document.getElementById('hearingFormNoShow'),
        complete: document.getElementById('hearingFormComplete'),
        reschedule: document.getElementById('hearingFormReschedule'),
        notes: document.getElementById('hearingFormNotes'),
    };

    const labels = {
        start: 'Start Hearing',
        no_show: 'Mark No Show',
        complete: 'Complete Hearing',
        reschedule: 'Reschedule Hearing',
        notes: 'Add Notes',
    };

    function hideAllForms() {
        Object.values(forms).forEach((form) => form.classList.add('hidden'));
    }

    function openModal(action, hearingId, status, actionUrl, currentNotes) {
        hideAllForms();
        const form = forms[action];
        if (!form) return;

        form.action = actionUrl;
        form.classList.remove('hidden');
        titleEl.textContent = labels[action] || 'Hearing Action';
        subtitleEl.textContent = `Hearing #${hearingId} • Current status: ${String(status || '').replace('_', ' ')}`;

        if (action === 'notes') {
            notesField.value = currentNotes || '';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        hideAllForms();
    }

    document.querySelectorAll('[data-hearing-action]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-hearing-action');
            const hearingId = btn.getAttribute('data-hearing-id');
            const status = btn.getAttribute('data-hearing-status');
            const currentNotes = btn.getAttribute('data-current-notes') || '';

            const urlMap = {
                start: btn.getAttribute('data-start-url'),
                no_show: btn.getAttribute('data-no-show-url'),
                complete: btn.getAttribute('data-complete-url'),
                reschedule: btn.getAttribute('data-reschedule-url'),
                notes: btn.getAttribute('data-notes-url'),
            };

            openModal(action, hearingId, status, urlMap[action], currentNotes);
        });
    });

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>
@endsection
