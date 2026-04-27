@extends('layouts.admin')

@section('title', 'Resident Profile - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                @php
                    $headerPositionName = $user->position?->name ?? $user->position_title;
                    $headerPositionMap = $positionDisplayNames ?? [];
                    $headerPositionDisplay = $headerPositionName ? ($headerPositionMap[$headerPositionName] ?? $headerPositionName) : null;
                @endphp
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    {{ $user->last_name }}, {{ $user->first_name }}
                    @if ($user->middle_name)
                        <span class="text-gray-500">{{ $user->middle_name }}</span>
                    @endif
                    @if ($headerPositionDisplay)
                        <span class="ml-2 inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 align-middle">
                            {{ $headerPositionDisplay }}
                        </span>
                    @endif
                </h1>
                <p class="text-sm text-gray-600">Resident profile</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.residents.edit', $user) }}" class="ui-btn ui-btn-primary rounded-lg">
                    Edit Resident
                </a>
                <a href="{{ route('admin.residents.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">
                    Back to list
                </a>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="error">
                {{ $errors->first() }}
            </x-ui.alert>
        @endif
        @php
            $isHeadHousehold = $user->isHeadOfFamily() && $user->head_of_family_id === null;
            $linkedHead = $isHeadHousehold ? $user : $user->headOfFamilyUser;
            $householdMembersCount = $isHeadHousehold
                ? ($user->familyMembers?->count() ?? 0)
                : ($user->headOfFamilyUser?->familyMembers?->count() ?? 0);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Personal Information</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-600">Full name</dt>
                            <dd class="text-gray-900">{{ $user->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Gender</dt>
                            <dd class="text-gray-900">{{ ucfirst($user->gender) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Birthdate</dt>
                            <dd class="text-gray-900">{{ optional($user->birthdate)->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Age</dt>
                            <dd class="text-gray-900">{{ $user->age }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Civil status</dt>
                            <dd class="text-gray-900">{{ ucfirst($user->civil_status) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Contact number</dt>
                            <dd class="text-gray-900">{{ $user->contact_number }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Address & Household</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-600">Address</dt>
                            <dd class="text-gray-900">
                                @php
                                    $purokLabel = trim((string) (optional($user->purokRelation)->name ?? $user->purok));
                                    $normalizedPurok = preg_match('/^purok\b/i', $purokLabel) ? $purokLabel : ('Purok ' . $purokLabel);
                                    $houseNo = trim((string) $user->house_no);
                                    $houseNoIsPlaceholder = in_array(strtolower($houseNo), ['n/a', 'na', 'none', '-'], true);
                                    $addressPrimary = trim($houseNoIsPlaceholder ? '' : $houseNo);
                                    $addressPrefix = $addressPrimary !== '' ? $addressPrimary . ', ' : '';
                                @endphp
                                {{ $addressPrefix }}{{ $normalizedPurok }}, Barangay Paguiruan, Floridablanca
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Resident type</dt>
                            <dd class="text-gray-900">{{ ucfirst($user->resident_type) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Head of Family</dt>
                            <dd class="text-gray-900">
                                @if ($user->isHeadOfFamily())
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800">Yes</span>
                                @else
                                    <span class="text-gray-900">No</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Household Link Status</dt>
                            <dd class="text-gray-900">
                                @if ($isHeadHousehold)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800">Head Household</span>
                                @elseif ($user->head_of_family_id && $linkedHead)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">Linked</span>
                                @elseif ($user->family_link_status === 'pending_link')
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">Pending Link</span>
                                @else
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">Unlinked</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Linked Head</dt>
                            <dd class="text-gray-900">{{ $linkedHead?->full_name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Relationship to head</dt>
                            @php
                                $userRelationshipDisplay = null;
                                $relationship = trim((string) ($user->relationship_to_head ?? ''));
                                if ($relationship !== '') {
                                    $userRelationshipDisplay = ucwords(str_replace('_', ' ', strtolower($relationship)));
                                } else {
                                    $connectionTypeRaw = strtolower(trim((string) ($user->household_connection_type ?? '')));
                                    $connectionTypeLabel = trim((string) ($user->householdConnectionTypeLabel() ?? ''));
                                    $connectionNote = trim((string) ($user->connection_note ?? ''));

                                    if ($connectionTypeLabel !== '') {
                                        $userRelationshipDisplay = ($connectionTypeRaw === 'other' && $connectionNote !== '')
                                            ? ($connectionTypeLabel . ' - ' . $connectionNote)
                                            : $connectionTypeLabel;
                                    } elseif ($connectionNote !== '') {
                                        $userRelationshipDisplay = $connectionNote;
                                    }
                                }
                            @endphp
                            <dd class="text-gray-900">{{ $isHeadHousehold ? 'Head' : ($userRelationshipDisplay ?? '—') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Household connection type</dt>
                            <dd class="text-gray-900">{{ $isHeadHousehold ? 'Head Household' : ($user->householdConnectionTypeLabel() ?? '—') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Connection note</dt>
                            <dd class="text-gray-900">{{ $user->connection_note ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Household Members</dt>
                            <dd class="text-gray-900">{{ $householdMembersCount }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Family Linking Section (Admin controls) --}}
                <div id="family-linking" class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Family Linking</h2>

                    @if ($user->isHeadOfFamily())
                        {{-- Show family members --}}
                        <div class="mb-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800">
                                Head of Family
                            </span>
                            <span class="ml-2 text-xs text-gray-500">{{ $user->familyMembers->count() }} {{ Str::plural('member', $user->familyMembers->count()) }}</span>
                        </div>

                        @if ($user->familyMembers->count() > 0)
                            <div class="border border-gray-200 rounded-lg divide-y divide-gray-100 mt-3">
                                <div class="px-4 py-3 flex items-center justify-between text-sm bg-red-50/40">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700 font-medium text-xs">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $user->full_name }}</p>
                                            <div class="text-xs text-gray-500">
                                                {{ ucfirst($user->gender) }} | {{ $user->birthdate ? $user->birthdate->age . ' yrs' : '—' }} | Current Head
                                            </div>
                                        </div>
                                    </div>
                                    <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700">Current Head</span>
                                </div>
                                @foreach ($user->familyMembers as $member)
                                    <div class="px-4 py-3 flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 font-medium text-xs">
                                                {{ strtoupper(substr($member->first_name, 0, 1)) }}{{ strtoupper(substr($member->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.residents.show', $member) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                                    {{ $member->full_name }}
                                                </a>
                                                @php
                                                    $memberRelationshipDisplay = null;
                                                    $memberRelationship = trim((string) ($member->relationship_to_head ?? ''));
                                                    if ($memberRelationship !== '') {
                                                        $memberRelationshipDisplay = ucwords(str_replace('_', ' ', strtolower($memberRelationship)));
                                                    } else {
                                                        $memberConnectionTypeRaw = strtolower(trim((string) ($member->household_connection_type ?? '')));
                                                        $memberConnectionTypeLabel = trim((string) ($member->householdConnectionTypeLabel() ?? ''));
                                                        $memberConnectionNote = trim((string) ($member->connection_note ?? ''));

                                                        if ($memberConnectionTypeLabel !== '') {
                                                            $memberRelationshipDisplay = ($memberConnectionTypeRaw === 'other' && $memberConnectionNote !== '')
                                                                ? ($memberConnectionTypeLabel . ' - ' . $memberConnectionNote)
                                                                : $memberConnectionTypeLabel;
                                                        } elseif ($memberConnectionNote !== '') {
                                                            $memberRelationshipDisplay = $memberConnectionNote;
                                                        }
                                                    }
                                                @endphp
                                                <div class="text-xs text-gray-500">
                                                    {{ ucfirst($member->gender) }} | {{ $member->birthdate ? $member->birthdate->age . ' yrs' : '—' }}
                                                    @if ($memberRelationshipDisplay)
                                                        | {{ $memberRelationshipDisplay }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button
                                                type="button"
                                                class="text-xs font-medium text-blue-600 hover:text-blue-700"
                                                onclick="openDirectHeadTransferModal(this)"
                                                data-transfer-form-id="direct-transfer-form-{{ $member->id }}"
                                                data-member-name="{{ $member->full_name }}"
                                            >
                                                Make Head
                                            </button>
                                            <form method="POST" action="{{ route('admin.residents.unlinkFamily', $member) }}" onsubmit="return openHeadChangeReasonModal(event, this, 'unlink')">
                                                @csrf
                                                <input type="hidden" name="transfer_reason_code" data-role="transfer-reason-code">
                                                <input type="hidden" name="transfer_reason_details" data-role="transfer-reason-details">
                                                <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Unlink</button>
                                            </form>
                                            <form id="direct-transfer-form-{{ $member->id }}" method="POST" action="{{ route('admin.residents.transferHead', [$user, $member]) }}" class="hidden">
                                                @csrf
                                                <input type="hidden" name="direct_transfer_reason" value="">
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 mt-2">No members linked to this head yet.</p>
                        @endif

                    @else
                        {{-- Not head - show current link or allow linking --}}
                        <div class="space-y-3">
                            @if ($user->head_of_family_id && $user->headOfFamilyUser)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Linked to:
                                            <a href="{{ route('admin.residents.show', $user->headOfFamilyUser) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                                {{ $user->headOfFamilyUser->full_name }}
                                            </a>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800">Linked</span>
                                        </p>
                                    </div>
                                    <form method="POST" action="{{ route('admin.residents.unlinkFamily', $user) }}" onsubmit="return openHeadChangeReasonModal(event, this, 'unlink')">
                                        @csrf
                                        <input type="hidden" name="transfer_reason_code" data-role="transfer-reason-code">
                                        <input type="hidden" name="transfer_reason_details" data-role="transfer-reason-details">
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Unlink</button>
                                    </form>
                                </div>
                            @else
                                @if ($user->head_first_name || $user->head_last_name)
                                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                                        <p class="font-medium">Declared head of family (not yet linked):</p>
                                        <p>{{ $user->head_first_name }} {{ $user->head_middle_name }} {{ $user->head_last_name }}</p>
                                    </div>
                                @endif

                                <p class="text-xs text-gray-500">
                                    @if ($user->family_link_status === 'pending_link')
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">Pending Link</span>
                                    @elseif ($user->family_link_status === 'unlinked')
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">Unlinked</span>
                                    @else
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">Not Linked</span>
                                    @endif
                                </p>
                            @endif

                            {{-- Manual link form --}}
                            <form method="POST" action="{{ route('admin.residents.linkFamily', $user) }}" class="flex items-end gap-3 mt-2" onsubmit="return openHeadChangeReasonModal(event, this, 'link')">
                                @csrf
                                <input type="hidden" name="transfer_reason_code" data-role="transfer-reason-code" value="{{ old('transfer_reason_code') }}">
                                <input type="hidden" name="transfer_reason_details" data-role="transfer-reason-details" value="{{ old('transfer_reason_details') }}">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Link to Head of Family</label>
                                    <select name="head_of_family_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                        <option value="">Select head...</option>
                                        @foreach ($familyHeads as $head)
                                            <option value="{{ $head->id }}" @selected(old('head_of_family_id') == $head->id)>
                                                {{ $head->last_name }}, {{ $head->first_name }} {{ $head->middle_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm rounded-lg">
                                    Link
                                </button>
                            </form>
                            <p class="text-[11px] text-gray-500">A structured reason is required for every head-of-family reassignment.</p>
                        </div>
                    @endif
                </div>

                {{-- Head Transfer History --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Head Transfer History</h2>
                    @if (($headTransferLogs ?? collect())->isEmpty())
                        <p class="text-xs text-gray-500">No transfer logs yet for this resident.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($headTransferLogs as $log)
                                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-xs font-medium text-gray-700">
                                            {{ ucfirst($log->action) }} | {{ $log->reasonLabel() }}
                                            @if ($log->reason_details)
                                                - {{ $log->reason_details }}
                                            @endif
                                        </p>
                                        <span class="text-[11px] text-gray-500">{{ $log->created_at->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-600">
                                        From: {{ $log->oldHeadUser?->full_name ?? 'None' }} -> To: {{ $log->newHeadUser?->full_name ?? 'None' }}
                                    </p>
                                    <p class="text-[11px] text-gray-500">
                                        By: {{ $log->changedByUser?->full_name ?? 'System' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Account</h2>
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-600">Email</dt>
                            <dd class="text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Role</dt>
                            <dd class="text-gray-900">{{ ucfirst($user->role) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Position</dt>
                            <dd class="text-gray-900">{{ $user->position?->name ?? $user->position_title ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Display name</dt>
                            <dd class="text-gray-900">{{ $user->display_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Approval status</dt>
                            <dd class="text-gray-900">{{ ucfirst($user->status) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Suspended</dt>
                            <dd class="text-gray-900">
                                {{ $user->is_suspended ? 'Yes' : 'No' }}
                                @if ($user->is_suspended && $user->suspended_at)
                                    <span class="text-xs text-gray-500">since {{ $user->suspended_at->format('M d, Y H:i') }}</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-600">Registered at</dt>
                            <dd class="text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Official Position --}}
                @if (in_array($user->role, ['staff', 'admin']))
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Official Position</h2>
                        <form method="POST" action="{{ route('admin.residents.updatePosition', $user) }}">
                            @csrf
                            <div class="space-y-3">
                                <div>
                                    <label for="position_id" class="block text-xs font-medium text-gray-600 mb-1">Assign Position</label>
                                    <select name="position_id" id="position_id"
                                        class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">— No Position —</option>
                                        @php
                                            $positionsByName = ($positions ?? collect())->keyBy('name');
                                            $orderedPositionNames = [
                                                'Barangay Chairman',
                                                'Barangay Secretary',
                                                'Barangay Treasurer',
                                                'Barangay Investigator',
                                                'Kagawad',
                                                'SK Chairman',
                                                'SK Kagawad',
                                            ];
                                        @endphp
                                        @foreach ($orderedPositionNames as $positionName)
                                            @php
                                                $position = $positionsByName->get($positionName);
                                            @endphp
                                            @if ($position)
                                                @php
                                                    $remaining = $position->remainingSeats($user->id);
                                                    $isFull = $remaining <= 0;
                                                    $seatWord = $position->max_seats === 1 ? 'seat' : 'seats';
                                                    $displayPositionName = ($positionDisplayNames ?? [])[$position->name] ?? $position->name;
                                                @endphp
                                                <option value="{{ $position->id }}"
                                                    @selected(old('position_id', $user->position_id) == $position->id)
                                                    @disabled($isFull && $user->position_id != $position->id)>
                                                    {{ $displayPositionName }} ({{ $remaining }}/{{ $position->max_seats }} {{ $seatWord }} left)
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="position_reason_code" class="block text-xs font-medium text-gray-600 mb-1">Change Reason</label>
                                    <select name="position_reason_code" id="position_reason_code"
                                        class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="">— Select Reason —</option>
                                        @foreach (($positionReasonOptions ?? []) as $reasonCode => $reasonLabel)
                                            <option value="{{ $reasonCode }}" @selected(old('position_reason_code') === $reasonCode)>
                                                {{ $reasonLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('position_reason_code')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="position_reason_details" class="block text-xs font-medium text-gray-600 mb-1">Reason details (optional)</label>
                                    <textarea name="position_reason_details" id="position_reason_details" rows="2"
                                        class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Add extra context for this position change">{{ old('position_reason_details') }}</textarea>
                                    <p id="position_reason_details_hint" class="mt-1 hidden text-[11px] text-gray-500">
                                        Reason details are required when "Other" is selected.
                                    </p>
                                    @error('position_reason_details')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="w-full ui-btn ui-btn-primary ui-btn-sm rounded-lg">
                                    Save Position
                                </button>
                            </div>
                        </form>
                        <div class="mt-4 border-t border-gray-100 pt-4">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Position Change History</h3>
                            @if (($positionChangeLogs ?? collect())->isEmpty())
                                <p class="text-xs text-gray-500">No position change history yet.</p>
                            @else
                                <ul class="space-y-2">
                                    @foreach ($positionChangeLogs as $changeLog)
                                        @php
                                            $oldDisplayName = ($positionDisplayNames ?? [])[$changeLog->oldPosition?->name ?? ''] ?? ($changeLog->oldPosition?->name ?? 'No Position');
                                            $newDisplayName = ($positionDisplayNames ?? [])[$changeLog->newPosition?->name ?? ''] ?? ($changeLog->newPosition?->name ?? 'No Position');
                                        @endphp
                                        <li class="rounded-lg border border-gray-100 bg-gray-50 p-2">
                                            <p class="text-xs text-gray-700">
                                                <span class="font-medium">{{ $oldDisplayName }}</span>
                                                <span class="text-gray-400">-></span>
                                                <span class="font-medium">{{ $newDisplayName }}</span>
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                {{ $changeLog->reasonLabel() }}
                                                @if ($changeLog->reason_details)
                                                    - {{ $changeLog->reason_details }}
                                                @endif
                                            </p>
                                            <p class="text-[11px] text-gray-500">
                                                {{ optional($changeLog->created_at)->format('M d, Y h:i A') }} by {{ $changeLog->changedByUser?->display_name ?? 'System' }}
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Module Access Permissions --}}
                @if (in_array($user->role, ['staff', 'admin']))
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Module Access</h2>
                        <p class="text-xs text-gray-500 mb-4">Control which modules this official can manage.</p>
                        <form method="POST" action="{{ route('admin.residents.updatePermissions', $user) }}">
                            @csrf
                            @php
                                $perm = $user->staffPermission;
                            @endphp
                            <div class="space-y-3">
                                {{-- Registration Management --}}
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm text-gray-700">Registration Management</span>
                                    <div class="relative">
                                        <input type="hidden" name="can_manage_registrations" value="0">
                                        <input type="checkbox" name="can_manage_registrations" value="1"
                                            class="sr-only peer"
                                            {{ $perm?->can_manage_registrations ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--brand-100)] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--brand-700)]"></div>
                                    </div>
                                </label>
                                {{-- e-Blotter --}}
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm text-gray-700">e-Blotter</span>
                                    <div class="relative">
                                        <input type="hidden" name="can_manage_blotter" value="0">
                                        <input type="checkbox" name="can_manage_blotter" value="1"
                                            class="sr-only peer"
                                            {{ $perm?->can_manage_blotter ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--brand-100)] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--brand-700)]"></div>
                                    </div>
                                </label>
                                {{-- Announcements --}}
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm text-gray-700">Announcements</span>
                                    <div class="relative">
                                        <input type="hidden" name="can_manage_announcements" value="0">
                                        <input type="checkbox" name="can_manage_announcements" value="1"
                                            class="sr-only peer"
                                            {{ $perm?->can_manage_announcements ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--brand-100)] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--brand-700)]"></div>
                                    </div>
                                </label>
                                {{-- Complaints --}}
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm text-gray-700">Complaints</span>
                                    <div class="relative">
                                        <input type="hidden" name="can_manage_complaints" value="0">
                                        <input type="checkbox" name="can_manage_complaints" value="1"
                                            class="sr-only peer"
                                            {{ $perm?->can_manage_complaints ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--brand-100)] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--brand-700)]"></div>
                                    </div>
                                </label>
                                {{-- Reports --}}
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-sm text-gray-700">Reports & Analytics</span>
                                    <div class="relative">
                                        <input type="hidden" name="can_manage_reports" value="0">
                                        <input type="checkbox" name="can_manage_reports" value="1"
                                            class="sr-only peer"
                                            {{ $perm?->can_manage_reports ? 'checked' : '' }}>
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[var(--brand-100)] rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--brand-700)]"></div>
                                    </div>
                                </label>
                            </div>
                            <button type="submit" class="mt-4 w-full ui-btn ui-btn-primary ui-btn-sm rounded-lg">
                                Save Permissions
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Classification --}}
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Classification</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <dt class="font-medium text-gray-600">PWD</dt>
                            <dd>
                                @if ($user->is_pwd)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($user->pwd_status ?? 'Yes') }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">No</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="font-medium text-gray-600">Senior Citizen</dt>
                            <dd>
                                @if ($user->is_senior)
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-800">{{ ucfirst($user->senior_status ?? 'Yes') }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">No</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Permanent Address --}}
                @if ($user->permanent_house_no || $user->permanent_street)
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
                        <h2 class="text-sm font-semibold text-gray-700 mb-3">Permanent Address</h2>
                        <p class="text-sm text-gray-900">
                            {{ $user->permanent_house_no }} {{ $user->permanent_street }}<br>
                            {{ $user->permanent_barangay }}, {{ $user->permanent_city }}<br>
                            {{ $user->permanent_province }}@if($user->permanent_region), {{ $user->permanent_region }}@endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<div id="headChangeReasonModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-semibold text-gray-800">Head Transfer Reason</h3>
            <p class="mt-1 text-xs text-gray-500">Please provide a reason before continuing this action.</p>
        </div>
        <div class="space-y-4 px-5 py-4">
            <div>
                <label for="modalTransferReasonCode" class="block text-xs font-medium text-gray-600 mb-1">Reason code <span class="text-red-600">*</span></label>
                <select id="modalTransferReasonCode" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Select reason</option>
                    @foreach (($transferReasonOptions ?? []) as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div id="modalTransferReasonDetailsWrap" class="hidden">
                <label for="modalTransferReasonDetails" class="block text-xs font-medium text-gray-600 mb-1">Details (required for Other)</label>
                <textarea id="modalTransferReasonDetails" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" maxlength="255" placeholder="Provide details"></textarea>
            </div>
            <p id="modalTransferReasonError" class="hidden text-xs text-red-600"></p>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50" onclick="closeHeadChangeReasonModal()">
                Cancel
            </button>
            <button type="button" class="ui-btn ui-btn-primary ui-btn-sm rounded-lg" onclick="submitHeadChangeReasonModal()">
                Continue
            </button>
        </div>
    </div>
</div>

<div id="directHeadTransferModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
    <div class="w-full max-w-md rounded-xl bg-white shadow-xl">
        <div class="border-b border-gray-200 px-5 py-4">
            <h3 class="text-sm font-semibold text-gray-800">Direct Head Transfer</h3>
            <p class="mt-1 text-xs text-gray-500">
                This will immediately transfer head-of-family role to the selected member.
            </p>
        </div>
        <div class="space-y-4 px-5 py-4">
            <p class="text-sm text-gray-700">
                New Head: <span id="directTransferMemberName" class="font-semibold text-gray-900"></span>
            </p>
            <div>
                <label for="directTransferReasonInput" class="block text-xs font-medium text-gray-600 mb-1">Reason (optional)</label>
                <textarea
                    id="directTransferReasonInput"
                    rows="3"
                    maxlength="255"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                    placeholder="Add context for audit trail"
                ></textarea>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                onclick="closeDirectHeadTransferModal()"
            >
                Cancel
            </button>
            <button
                type="button"
                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                onclick="submitDirectHeadTransferModal()"
            >
                Confirm Transfer
            </button>
        </div>
    </div>
</div>

<script>
let pendingHeadChangeForm = null;
let pendingDirectTransferForm = null;

function openHeadChangeReasonModal(event, form) {
    event.preventDefault();
    pendingHeadChangeForm = form;

    const modal = document.getElementById('headChangeReasonModal');
    const reasonCode = document.getElementById('modalTransferReasonCode');
    const reasonDetails = document.getElementById('modalTransferReasonDetails');
    const reasonWrap = document.getElementById('modalTransferReasonDetailsWrap');
    const error = document.getElementById('modalTransferReasonError');

    reasonCode.value = '';
    reasonDetails.value = '';
    reasonWrap.classList.add('hidden');
    error.classList.add('hidden');
    error.textContent = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    return false;
}

function closeHeadChangeReasonModal() {
    const modal = document.getElementById('headChangeReasonModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    pendingHeadChangeForm = null;
}

function openDirectHeadTransferModal(buttonEl) {
    const formId = buttonEl?.dataset?.transferFormId || '';
    const memberName = buttonEl?.dataset?.memberName || '';
    const form = formId ? document.getElementById(formId) : null;
    if (!form) {
        return false;
    }

    pendingDirectTransferForm = form;
    const modal = document.getElementById('directHeadTransferModal');
    const nameEl = document.getElementById('directTransferMemberName');
    const reasonEl = document.getElementById('directTransferReasonInput');

    if (nameEl) {
        nameEl.textContent = memberName;
    }
    if (reasonEl) {
        reasonEl.value = '';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    return false;
}

function closeDirectHeadTransferModal() {
    const modal = document.getElementById('directHeadTransferModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    pendingDirectTransferForm = null;
}

function submitDirectHeadTransferModal() {
    if (!pendingDirectTransferForm) {
        closeDirectHeadTransferModal();
        return;
    }

    const reasonEl = document.getElementById('directTransferReasonInput');
    const reason = ((reasonEl && reasonEl.value) || '').trim();
    const reasonInput = pendingDirectTransferForm.querySelector('input[name="direct_transfer_reason"]');
    if (reasonInput) {
        reasonInput.value = reason;
    }
    pendingDirectTransferForm.submit();
}

function submitHeadChangeReasonModal() {
    if (!pendingHeadChangeForm) {
        closeHeadChangeReasonModal();
        return;
    }

    const reasonCodeEl = document.getElementById('modalTransferReasonCode');
    const reasonDetailsEl = document.getElementById('modalTransferReasonDetails');
    const reasonWrap = document.getElementById('modalTransferReasonDetailsWrap');
    const error = document.getElementById('modalTransferReasonError');
    const reasonCode = (reasonCodeEl.value || '').trim();
    const reasonDetails = (reasonDetailsEl.value || '').trim();

    reasonWrap.classList.toggle('hidden', reasonCode !== 'other');

    if (!reasonCode) {
        error.textContent = 'Reason code is required.';
        error.classList.remove('hidden');
        return;
    }

    if (reasonCode === 'other' && reasonDetails.length < 5) {
        error.textContent = 'Please provide at least 5 characters for details.';
        error.classList.remove('hidden');
        return;
    }

    const codeInput = pendingHeadChangeForm.querySelector('[data-role="transfer-reason-code"]');
    const detailsInput = pendingHeadChangeForm.querySelector('[data-role="transfer-reason-details"]');

    if (!codeInput || !detailsInput) {
        error.textContent = 'Unable to capture transfer reason. Please reload and try again.';
        error.classList.remove('hidden');
        return;
    }

    codeInput.value = reasonCode;
    detailsInput.value = reasonDetails;
    pendingHeadChangeForm.submit();
}

document.getElementById('modalTransferReasonCode')?.addEventListener('change', function () {
    const showDetails = this.value === 'other';
    document.getElementById('modalTransferReasonDetailsWrap')?.classList.toggle('hidden', !showDetails);
});

const positionReasonCodeEl = document.getElementById('position_reason_code');
const positionReasonDetailsEl = document.getElementById('position_reason_details');
const positionReasonDetailsHintEl = document.getElementById('position_reason_details_hint');

function syncPositionReasonDetailsRequirement() {
    if (!positionReasonCodeEl || !positionReasonDetailsEl || !positionReasonDetailsHintEl) {
        return;
    }

    const requiresDetails = positionReasonCodeEl.value === 'other';
    positionReasonDetailsEl.required = requiresDetails;
    positionReasonDetailsHintEl.classList.toggle('hidden', !requiresDetails);
}

positionReasonCodeEl?.addEventListener('change', syncPositionReasonDetailsRequirement);
syncPositionReasonDetailsRequirement();
</script>
@endsection
