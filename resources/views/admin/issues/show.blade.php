@extends($layout ?? 'layouts.admin')

@section('title', 'Complaint #' . $issue->id . ' - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">

        {{-- Header --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route($rp . '.issues.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">&larr; Back to complaints</a>
                <h1 class="mt-1 text-xl font-semibold tracking-tight text-gray-800">Complaint #{{ $issue->id }}</h1>
            </div>
            <div>
                @if ($issue->status === 'pending')
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                @elseif ($issue->status === 'in_progress')
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800">In Progress</span>
                @elseif ($issue->status === 'resolved')
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">Resolved</span>
                @else
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-gray-200 text-gray-700">Closed</span>
                @endif
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="error">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ═══════════════════════════════════════════ --}}
            {{-- LEFT COLUMN: Complaint Details             --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Complaint Info Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">{{ $issue->subject }}</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        {{-- Complainant --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Complainant</p>
                            <p class="text-sm font-medium text-gray-900">{{ $issue->user->first_name }} {{ $issue->user->last_name }}</p>
                            <p class="text-xs text-gray-500">{{ $issue->user->email }}</p>
                            @if ($issue->user->contact_number)
                                <p class="text-xs text-gray-500">{{ $issue->user->contact_number }}</p>
                            @endif
                        </div>

                        {{-- Category --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Category</p>
                            @if ($issue->category)
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $issue->category }}</span>
                            @else
                                <span class="text-sm text-gray-400">Uncategorized</span>
                            @endif
                        </div>

                        {{-- Location --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Location</p>
                            @if ($issue->location)
                                <p class="text-sm text-gray-900 flex items-center gap-1">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $issue->location }}
                                </p>
                            @else
                                <span class="text-sm text-gray-400">Not specified</span>
                            @endif
                        </div>

                        {{-- Purok --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Purok</p>
                            <p class="text-sm text-gray-900">{{ $issue->purok->name ?? '—' }}</p>
                        </div>

                        {{-- Filed --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-1">Date Filed</p>
                            <p class="text-sm text-gray-900">{{ $issue->created_at->format('M d, Y h:i A') }}</p>
                        </div>

                        {{-- Closed --}}
                        @if ($issue->closed_at)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase mb-1">Date Closed</p>
                                <p class="text-sm text-gray-900">{{ $issue->closed_at->format('M d, Y h:i A') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-2">Description</p>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($issue->description)) !!}
                        </div>
                    </div>

                    {{-- Attachment --}}
                    @if ($issue->attachment_path)
                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Attachment</p>
                            @php
                                $ext = strtolower(pathinfo($issue->attachment_path, PATHINFO_EXTENSION));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
                            @endphp
                            @if ($isImage)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $issue->attachment_path) }}"
                                         alt="Complaint attachment"
                                         class="max-w-md rounded-lg border border-gray-200 shadow-sm cursor-pointer"
                                         onclick="document.getElementById('imagePreviewModal').classList.remove('hidden')">
                                </div>
                                {{-- Image Preview Modal --}}
                                <div id="imagePreviewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                                     onclick="this.classList.add('hidden')">
                                    <img src="{{ asset('storage/' . $issue->attachment_path) }}" alt="Attachment full preview"
                                         class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
                                </div>
                            @endif
                            <a href="{{ asset('storage/' . $issue->attachment_path) }}" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download {{ strtoupper($ext) }}
                            </a>
                        </div>
                    @endif
                </div>

                {{-- ══════════════════════════════════════ --}}
                {{-- Resolution Summary (when resolved)     --}}
                {{-- ══════════════════════════════════════ --}}
                @if ($issue->status === 'resolved' || $issue->status === 'closed')
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                        <h3 class="text-base font-bold text-gray-900 mb-3">Resolution Summary</h3>
                        <div class="space-y-4">
                            @if ($issue->resolution_notes)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Resolution Notes</p>
                                    <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-gray-700 whitespace-pre-line">{{ $issue->resolution_notes }}</div>
                                </div>
                            @elseif ($issue->remarks)
                                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-gray-700 whitespace-pre-line">{{ $issue->remarks }}</div>
                            @endif
                            @if ($issue->action_taken)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Action Taken</p>
                                    <p class="text-sm text-gray-900">{{ $issue->action_taken }}</p>
                                </div>
                            @endif
                            @if ($issue->other_details)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Other Details</p>
                                    <p class="text-sm text-gray-700">{{ $issue->other_details }}</p>
                                </div>
                            @endif
                            @if ($issue->after_photo_path)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">After Photo</p>
                                    @php $ext = strtolower(pathinfo($issue->after_photo_path, PATHINFO_EXTENSION)); $isImg = in_array($ext, ['jpg', 'jpeg', 'png']); @endphp
                                    @if ($isImg)
                                        <img src="{{ asset('storage/' . $issue->after_photo_path) }}" alt="After resolution" class="max-w-md rounded-lg border border-gray-200 shadow-sm">
                                    @endif
                                    <a href="{{ asset('storage/' . $issue->after_photo_path) }}" target="_blank" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-700">View / Download</a>
                                </div>
                            @endif
                            @if ($issue->resolvedBy)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Resolved By</p>
                                    <p class="text-sm text-gray-900">{{ $issue->resolvedBy->first_name }} {{ $issue->resolvedBy->last_name }}</p>
                                </div>
                            @endif
                            @if ($issue->resolved_at)
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Date Resolved</p>
                                    <p class="text-sm text-gray-900">{{ $issue->resolved_at->format('M d, Y h:i A') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════ --}}
                {{-- Case Notes Timeline                    --}}
                {{-- ══════════════════════════════════════ --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-gray-900">Case Notes</h3>
                        <span class="text-xs text-gray-500">{{ $issue->notes->count() }} note{{ $issue->notes->count() !== 1 ? 's' : '' }}</span>
                    </div>

                    @if ($issue->notes->isEmpty())
                        <p class="text-sm text-gray-500 py-4 text-center">No notes yet.</p>
                    @else
                        <div class="space-y-0">
                            @foreach ($issue->notes->sortBy('created_at') as $note)
                                <div class="relative pl-8 pb-6 {{ !$loop->last ? 'border-l-2 border-gray-200 ml-3' : 'ml-3' }}">
                                    {{-- Timeline dot --}}
                                    <div class="absolute -left-[5px] top-1 h-[10px] w-[10px] rounded-full {{ $note->author && $note->author->role === 'admin' ? 'bg-red-500' : 'bg-blue-500' }} ring-2 ring-white"></div>

                                    <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3">
                                        <div class="flex items-center justify-between mb-1.5">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $note->author->first_name ?? 'Unknown' }} {{ $note->author->last_name ?? '' }}
                                                @if ($note->author)
                                                    <span class="ml-1 inline-flex rounded-full px-1.5 py-0.5 text-[10px] font-medium
                                                        {{ $note->author->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                                        {{ ucfirst($note->author->role) }}
                                                    </span>
                                                @endif
                                            </p>
                                            <time class="text-[11px] text-gray-500">{{ $note->created_at->format('M d, Y h:i A') }}</time>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $note->note }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add Note Form --}}
                    @php
                        $canAddNote = in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN], true)
                            || (int) $issue->assigned_to === (int) auth()->id();
                    @endphp
                    @if ($canAddNote && ! $issue->isClosed())
                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <form method="POST" action="{{ route($rp . '.issues.notes.store', $issue) }}">
                                @csrf
                                <label for="note" class="block text-sm font-medium text-gray-700 mb-1">Add Note</label>
                                <textarea name="note" id="note" rows="3" required
                                          placeholder="Enter case note..."
                                          class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('note') border-red-500 @enderror">{{ old('note') }}</textarea>
                                <div class="mt-2 flex justify-end">
                                    <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 transition shadow-sm">
                                        Add Note
                                    </button>
                                </div>
                            </form>
                        </div>
                    @elseif ($issue->isClosed())
                        <div class="border-t border-gray-100 pt-4 mt-4">
                            <p class="text-sm text-gray-400 text-center">This case is closed. Notes cannot be added.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- RIGHT COLUMN: Actions Sidebar              --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="space-y-6">

                {{-- Status Transition Card --}}
                @php
                    $semanticActions = [
                        'in_progress' => ['label' => 'Start Investigation', 'helper' => 'Next action: Start Investigation'],
                        'resolved' => ['label' => 'Resolve Complaint', 'helper' => 'Next action: Resolve Complaint'],
                        'closed' => ['label' => 'Close Complaint', 'helper' => 'Next action: Close Complaint'],
                    ];
                    $actionConfig = $nextStatus ? ($semanticActions[$nextStatus] ?? ['label' => 'Advance to ' . ucwords(str_replace('_', ' ', $nextStatus)), 'helper' => '']) : null;
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Status Transition</h3>

                    @if ($issue->isClosed())
                        <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-6 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <p class="text-sm font-medium text-gray-500">Case Closed</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $issue->closed_at->format('M d, Y') }}</p>
                        </div>
                    @elseif ($nextStatus)
                        @php
                            $canUpdate = in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN], true)
                                || (int) $issue->assigned_to === (int) auth()->id();
                            $remarksRequired = \App\Models\IssueReport::requiresRemarks($nextStatus);
                        @endphp
                        <div>
                            @if ($actionConfig && $actionConfig['helper'])
                                <p class="text-xs text-gray-500 mb-3">{{ $actionConfig['helper'] }}</p>
                            @endif

                            @if ($nextStatus === 'resolved')
                                {{-- Resolve: button opens modal (no direct form) --}}
                                @if ($canUpdate)
                                    <button type="button" onclick="openResolveModal()"
                                            class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white transition shadow-sm hover:bg-green-700">
                                        {{ $actionConfig['label'] ?? 'Resolve Complaint' }}
                                    </button>
                                @else
                                    <button type="button" disabled title="Only assigned officer or admin can perform this action"
                                            class="w-full cursor-not-allowed rounded-lg bg-gray-300 px-4 py-2.5 text-sm font-medium text-gray-500">
                                        {{ $actionConfig['label'] ?? 'Resolve Complaint' }}
                                    </button>
                                    <p class="mt-1 text-[11px] text-gray-500 text-center">Only assigned officer or admin can perform this action</p>
                                @endif
                            @else
                                {{-- Start / Close: inline form --}}
                                <form id="transitionForm" method="POST" action="{{ route($rp . '.issues.update', $issue) }}" {{ $nextStatus === 'closed' ? '' : '' }}>
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="{{ $nextStatus }}">
                                    <div class="mb-3">
                                        <label for="remarks" class="block text-xs font-medium text-gray-600 mb-1">
                                            {{ $nextStatus === 'closed' ? 'Closing Remarks' : 'Remarks' }}
                                            @if ($remarksRequired)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </label>
                                        <textarea name="remarks" id="remarks" rows="4"
                                                  placeholder="{{ $remarksRequired ? 'Required: Provide a summary...' : 'Optional...' }}"
                                                  {{ $remarksRequired ? 'required' : '' }}
                                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('remarks') border-red-500 @enderror">{{ old('remarks', $issue->remarks) }}</textarea>
                                    </div>
                                    @if ($canUpdate)
                                        <button type="button" onclick="openConfirmModal()"
                                                class="w-full rounded-lg px-4 py-2.5 text-sm font-medium text-white transition shadow-sm
                                                {{ $nextStatus === 'in_progress' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-700 hover:bg-gray-800' }}">
                                            {{ $actionConfig['label'] ?? 'Advance' }}
                                        </button>
                                    @else
                                        <button type="button" disabled title="Only assigned officer or admin can perform this action"
                                                class="w-full cursor-not-allowed rounded-lg bg-gray-300 px-4 py-2.5 text-sm font-medium text-gray-500">
                                            {{ $actionConfig['label'] ?? 'Advance' }}
                                        </button>
                                        <p class="mt-1 text-[11px] text-gray-500 text-center">Only assigned officer or admin can perform this action</p>
                                    @endif
                                </form>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Officer Assignment Card (Admin only) --}}
                @if (in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN], true))
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Assigned Officer</h3>

                        @if ($issue->assignedOfficer)
                            <div class="flex items-center gap-3 mb-3 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($issue->assignedOfficer->first_name, 0, 1)) }}{{ strtoupper(substr($issue->assignedOfficer->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $issue->assignedOfficer->first_name }} {{ $issue->assignedOfficer->last_name }}</p>
                                    <p class="text-[11px] text-gray-500">{{ ucfirst($issue->assignedOfficer->role) }}</p>
                                </div>
                            </div>
                        @endif

                        @if (! $issue->isClosed())
                            <form method="POST" action="{{ route($rp . '.issues.assign', $issue) }}">
                                @csrf
                                <select name="assigned_to" required
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm mb-2 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                    <option value="">{{ $issue->assignedOfficer ? 'Reassign officer...' : 'Select officer...' }}</option>
                                    @foreach ($officers as $officer)
                                        <option value="{{ $officer->id }}" @selected($issue->assigned_to == $officer->id)>
                                            {{ $officer->first_name }} {{ $officer->last_name }} ({{ ucfirst($officer->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition shadow-sm">
                                    {{ $issue->assignedOfficer ? 'Reassign' : 'Assign Officer' }}
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-gray-400 text-center">Cannot reassign a closed case.</p>
                        @endif
                    </div>
                @else
                    {{-- Non-admin: read-only + Assign to me when unassigned --}}
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                        <h3 class="text-sm font-bold text-gray-900 mb-3">Assigned Officer</h3>
                        @if ($issue->assignedOfficer)
                            <div class="flex items-center gap-3 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($issue->assignedOfficer->first_name, 0, 1)) }}{{ strtoupper(substr($issue->assignedOfficer->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $issue->assignedOfficer->first_name }} {{ $issue->assignedOfficer->last_name }}</p>
                                    <p class="text-[11px] text-gray-500">{{ ucfirst($issue->assignedOfficer->role) }}</p>
                                </div>
                            </div>
                        @else
                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Unassigned</span>
                            @if (! $issue->isClosed())
                                <form method="POST" action="{{ route($rp . '.issues.assign-me', $issue) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                                        Assign to me
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endif

                {{-- Quick Info Card --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Quick Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">ID</dt>
                            <dd class="font-medium text-gray-900">#{{ $issue->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Filed</dt>
                            <dd class="font-medium text-gray-900">{{ $issue->created_at->diffForHumans() }}</dd>
                        </div>
                        @if ($issue->closed_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Resolution time</dt>
                                <dd class="font-medium text-gray-900">{{ $issue->created_at->diffForHumans($issue->closed_at, true) }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Notes</dt>
                            <dd class="font-medium text-gray-900">{{ $issue->notes->count() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- Resolve Complaint Modal (structured resolution)    --}}
{{-- ═══════════════════════════════════════════════════ --}}
@if (isset($nextStatus) && $nextStatus === 'resolved' && ! $issue->isClosed())
@php
    $infraCategories = \App\Models\IssueReport::INFRAS_REQUIRE_PHOTO;
    $otherCategories = \App\Models\IssueReport::OTHER_REQUIRES_DETAILS;
    $actionOptions = \App\Models\IssueReport::ACTION_TAKEN_OPTIONS;
    $category = $issue->category ?? '';
    $needsPhoto = in_array($category, $infraCategories, true);
    $needsOther = in_array($category, $otherCategories, true);
    $needsAction = in_array($category, ['Infrastructure', 'Flooding', 'Sanitation'], true)
        ? $actionOptions['infra']
        : (in_array($category, ['Noise'], true) ? $actionOptions['noise'] : (in_array($category, ['Safety', 'Illegal Activity'], true) ? $actionOptions['safety'] : []));
@endphp
<div id="resolveModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeResolveModal()"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-xl p-6 z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Resolve Complaint</h3>
            <form id="resolveForm" method="POST" action="{{ route($rp . '.issues.update', $issue) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="resolved">
                <div class="space-y-4">
                    <div>
                        <label for="resolution_notes" class="block text-xs font-medium text-gray-600 mb-1">Resolution Notes <span class="text-red-600">*</span></label>
                        <textarea name="resolution_notes" id="resolution_notes" rows="4" required
                                  placeholder="Describe what was done to resolve this complaint..."
                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('resolution_notes') border-red-500 @enderror">{{ old('resolution_notes') }}</textarea>
                        @error('resolution_notes')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    @if (count($needsAction) > 0)
                        <div>
                            <label for="action_taken" class="block text-xs font-medium text-gray-600 mb-1">Action Taken</label>
                            <select name="action_taken" id="action_taken" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Select...</option>
                                @foreach ($needsAction as $opt)
                                    <option value="{{ $opt }}" @selected(old('action_taken') === $opt)>{{ ucfirst($opt) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($needsPhoto)
                        <div>
                            <label for="after_photo" class="block text-xs font-medium text-gray-600 mb-1">After Photo <span class="text-red-600">*</span></label>
                            <input type="file" name="after_photo" id="after_photo" accept=".jpg,.jpeg,.png"
                                   class="block w-full text-sm text-gray-600 rounded-lg border border-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 @error('after_photo') border-red-500 @enderror">
                            <p class="mt-1 text-[11px] text-gray-500">JPG or PNG. Max 5MB.</p>
                            @error('after_photo')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                    @if ($needsOther)
                        <div>
                            <label for="other_details" class="block text-xs font-medium text-gray-600 mb-1">Please specify the issue <span class="text-red-600">*</span></label>
                            <textarea name="other_details" id="other_details" rows="3" required
                                      placeholder="Provide details of the issue and resolution..."
                                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('other_details') border-red-500 @enderror">{{ old('other_details') }}</textarea>
                            @error('other_details')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeResolveModal()" class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                        Resolve Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function openResolveModal() { document.getElementById('resolveModal').classList.remove('hidden'); }
    function closeResolveModal() { document.getElementById('resolveModal').classList.add('hidden'); }
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeResolveModal(); });
</script>
@endif

{{-- ═══════════════════════════════════════════════════ --}}
{{-- Confirmation Modal for Status Transitions          --}}
{{-- ═══════════════════════════════════════════════════ --}}
@if (isset($nextStatus) && $nextStatus && $nextStatus !== 'resolved' && ! $issue->isClosed())
<div id="confirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-full items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmModal()"></div>

        {{-- Modal panel --}}
        <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-xl p-6 z-10">
            <div class="text-center">
                @if ($nextStatus === 'closed')
                    {{-- Extra warning for closing --}}
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Close This Case?</h3>
                    <p class="text-sm text-gray-600 mb-1">This action is <strong>irreversible</strong>. Once closed, the case cannot be reopened or modified.</p>
                    <p class="text-xs text-gray-500 mb-5">Ensure the resolution summary is complete before proceeding.</p>
                @else
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100">
                        <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Advance Status?</h3>
                    <p class="text-sm text-gray-600 mb-5">
                        Move this complaint from
                        <span class="font-semibold">{{ ucwords(str_replace('_', ' ', $issue->status)) }}</span>
                        to
                        <span class="font-semibold">{{ ucwords(str_replace('_', ' ', $nextStatus)) }}</span>?
                    </p>
                @endif

                <div class="flex gap-3">
                    <button type="button" onclick="closeConfirmModal()"
                            class="flex-1 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="button" onclick="submitTransition()"
                            class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium text-white transition shadow-sm
                            {{ $nextStatus === 'closed' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                        {{ $nextStatus === 'closed' ? 'Yes, Close Case' : 'Yes, Advance' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openConfirmModal() {
        // Validate remarks if required before showing modal
        const form = document.getElementById('transitionForm');
        const remarks = form.querySelector('textarea[name="remarks"]');
        if (remarks && remarks.required && !remarks.value.trim()) {
            remarks.focus();
            remarks.classList.add('border-red-500');
            return;
        }
        document.getElementById('confirmModal').classList.remove('hidden');
    }
    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }
    function submitTransition() {
        document.getElementById('transitionForm').submit();
    }
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeConfirmModal();
    });
</script>
@endif
@endsection
