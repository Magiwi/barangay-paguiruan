{{-- Head of the Family Tab --}}
@php
    $canManageOwnFamily = $canManageOwnFamily ?? $user->canManageOwnFamily();
    $canManageAnyFamily = $canManageAnyFamily ?? $user->canManageAnyFamily();
    $canManageFamily = $canManageOwnFamily;
    $isHead = $canManageOwnFamily;
    $headUser = $isHead ? $user : $user->headOfFamilyUser;
    $familyHeads = $familyHeads ?? collect();
    $members = isset($membersPaginator) && $membersPaginator
        ? collect($membersPaginator->items())
        : collect();
    $membersTotal = isset($membersPaginator) && $membersPaginator ? $membersPaginator->total() : $members->count();
    $recentlyRemovedMembers = $recentlyRemovedMembers ?? collect();
    $availableLinkedMembers = $availableLinkedMembers ?? collect();
    $latestHeadTransferRequest = $latestHeadTransferRequest ?? null;
    $pendingHeadTransferRequest = $pendingHeadTransferRequest ?? null;
    $hasEligibleLinkedMembers = $availableLinkedMembers->isNotEmpty();
    $eligibleLinkedMemberIds = $availableLinkedMembers->pluck('id')->map(fn ($id) => (int) $id)->all();
    $transferReasonLabels = \App\Models\HouseholdHeadTransferRequest::REASON_LABELS;
    $headAge = $headUser
        ? ($headUser->birthdate ? $headUser->birthdate->age : (int) ($headUser->age ?? 0))
        : 0;
@endphp

<div class="space-y-6">

    {{-- Family status card --}}
    <div class="ui-surface-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Family Information</h2>
            <div class="flex items-center gap-3">
                @if ($isHead)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"/></svg>
                        Head of Family
                    </span>
                @endif
            </div>
        </div>

        @if ($headUser)
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Head of Family</dt>
                    <dd class="mt-0.5 text-gray-900 font-medium">{{ $headUser->full_name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Contact</dt>
                    <dd class="mt-0.5 text-gray-900">{{ $headUser->contact_number }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Purok</dt>
                    <dd class="mt-0.5 text-gray-900">{{ optional($headUser->purokRelation)->name ?? $headUser->purok ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Total Members</dt>
                    <dd class="mt-0.5 text-gray-900 font-medium">{{ $membersTotal }}</dd>
                </div>
            </dl>
        @elseif (! $isHead && $user->head_of_family === 'no')
            <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                @if ($user->family_link_status === 'pending_link')
                    <p class="font-medium">Pending family linking</p>
                    <p class="mt-1 text-xs">Your head of family (<strong>{{ $user->head_first_name }} {{ $user->head_last_name }}</strong>) has not been linked yet. An admin will review and link your account.</p>
                @else
                    <p>You are not currently linked to a head of family. Contact the barangay office to update your family linkage.</p>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-500">No family information available.</p>
        @endif
    </div>

    {{-- Members list --}}
    @if ($headUser && $members->count() > 0)
        <div class="ui-surface-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Family Members</h3>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500">{{ $membersTotal }} {{ Str::plural('member', $membersTotal) }}</span>
                    @if ($canManageFamily)
                        <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                                class="ui-focus-ring inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition hover:bg-blue-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Member
                        </button>
                    @endif
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($members as $member)
                    <div class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 font-semibold text-sm">
                                {{ strtoupper(substr($member->first_name, 0, 1)) }}{{ strtoupper(substr($member->last_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $member->full_name }}</p>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5 text-xs text-gray-500">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 font-medium text-slate-700">Member</span>
                                    <span>{{ ucfirst($member->gender) }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span>{{ optional($member->birthdate)->format('M d, Y') ?? '—' }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span>{{ $member->birthdate ? $member->birthdate->age . ' yrs' : '—' }}</span>
                                    @if ($member->relationship_to_head)
                                        <span class="text-gray-300">|</span>
                                        <span class="font-medium text-gray-600">{{ ucwords(strtolower((string) $member->relationship_to_head)) }}</span>
                                    @endif
                                    @if ($member->linked_user_id)
                                        <span class="text-gray-300">|</span>
                                        <span class="text-emerald-700 font-medium">Linked account</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 hidden sm:inline">{{ $member->contact_number }}</span>
                            @if (
                                $isHead
                                && ! $pendingHeadTransferRequest
                                && ! empty($member->linked_user_id)
                                && in_array((int) $member->linked_user_id, $eligibleLinkedMemberIds, true)
                            )
                                <button
                                    type="button"
                                    data-transfer-open="transferRequestModal-{{ $member->list_id }}"
                                    class="ui-focus-ring rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 transition hover:bg-blue-100"
                                >
                                    Request as Head
                                </button>
                            @endif
                            @if ($isHead && !empty($member->can_manage) && !empty($member->family_member_id))
                                <button type="button" onclick="document.getElementById('editMemberModal-{{ $member->family_member_id }}').classList.remove('hidden')"
                                        class="ui-focus-ring rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-50" title="Edit">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('family.destroy', $member->family_member_id) }}" onsubmit="return confirm('Remove {{ $member->first_name }} from your family?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ui-focus-ring rounded-lg border border-red-200 px-2.5 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50" title="Remove">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if ($isHead && ! empty($member->linked_user_id))
                        <div id="transferRequestModal-{{ $member->list_id }}" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div class="fixed inset-0 bg-gray-900/50" data-transfer-close="transferRequestModal-{{ $member->list_id }}"></div>
                                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-gray-200 p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-base font-semibold text-gray-900">Request Head Transfer</h3>
                                        <button type="button" data-transfer-close="transferRequestModal-{{ $member->list_id }}" class="text-gray-400 hover:text-gray-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('profile.family-transfer-requests.store') }}" class="space-y-4" onsubmit="return confirm('Are you sure you want to transfer head role to {{ addslashes($member->full_name) }}?');">
                                        @csrf
                                        <input type="hidden" name="requested_head_user_id" value="{{ $member->linked_user_id }}">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-600">Selected Member</label>
                                            <input type="text" value="{{ $member->full_name }}" readonly class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-600">Reason</label>
                                            <select name="reason" required data-transfer-reason class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm @error('reason') border-red-500 @enderror">
                                                <option value="">Select reason</option>
                                                @foreach ($transferReasonLabels as $code => $label)
                                                    <option value="{{ $code }}" @selected((string) old('reason') === $code)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="hidden" data-transfer-details>
                                            <label class="mb-1 block text-xs font-medium text-gray-600">Details</label>
                                            <textarea name="details" rows="3" maxlength="255" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm @error('details') border-red-500 @enderror" placeholder="Please provide details for this request">{{ old('details') }}</textarea>
                                        </div>
                                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                            Confirmation: You are requesting to transfer the head role to <strong>{{ $member->full_name }}</strong>. This requires barangay verification.
                                        </p>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" data-transfer-close="transferRequestModal-{{ $member->list_id }}" class="ui-focus-ring rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                            <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Submit Request</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Edit modal for this member (head only) --}}
                    @if ($isHead && !empty($member->can_manage) && !empty($member->family_member_id))
                        <div id="editMemberModal-{{ $member->family_member_id }}" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div class="fixed inset-0 bg-gray-900/50" onclick="document.getElementById('editMemberModal-{{ $member->family_member_id }}').classList.add('hidden')"></div>
                                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-gray-200 p-6">
                                    <div class="flex items-center justify-between mb-5">
                                        <h3 class="text-base font-semibold text-gray-900">Edit Family Member</h3>
                                        <button type="button" onclick="document.getElementById('editMemberModal-{{ $member->family_member_id }}').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <form method="POST" action="{{ route('family.update', $member->family_member_id) }}" class="space-y-4 family-member-form" data-head-age="{{ (int) $headAge }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">First Name <span class="text-red-600">*</span></label>
                                                <input type="text" name="first_name" value="{{ $member->first_name }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Last Name <span class="text-red-600">*</span></label>
                                                <input type="text" name="last_name" value="{{ $member->last_name }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Middle Name</label>
                                                <input type="text" name="middle_name" value="{{ $member->middle_name }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Suffix</label>
                                                <select name="suffix" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                                    <option value="">None</option>
                                                    @foreach (['Jr.', 'Sr.', 'I', 'II', 'III', 'IV'] as $s)
                                                        <option value="{{ $s }}" @selected($member->suffix === $s)>{{ $s }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Birthdate <span class="text-red-600">*</span></label>
                                                <input type="date" name="birthdate" data-role="birthdate" value="{{ optional($member->birthdate)->toDateString() }}" required max="{{ now('Asia/Manila')->format('Y-m-d') }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Age</label>
                                                <input type="text" data-role="age" value="{{ optional($member->birthdate)->age }}" readonly class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Gender <span class="text-red-600">*</span></label>
                                                <select name="gender" data-role="gender" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                                    @foreach (['male' => 'Male', 'female' => 'Female'] as $v => $l)
                                                        <option value="{{ $v }}" @selected($member->gender === $v)>{{ $l }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Contact Number</label>
                                                <input
                                                    type="text"
                                                    name="contact_number"
                                                    data-role="contact_number"
                                                    value="{{ \Illuminate\Support\Str::startsWith((string) $member->contact_number, '+63') ? '0' . substr((string) preg_replace('/\D+/', '', (string) $member->contact_number), 2, 10) : substr((string) preg_replace('/\D+/', '', (string) $member->contact_number), 0, 11) }}"
                                                    maxlength="11"
                                                    placeholder="09XXXXXXXXX"
                                                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                                                >
                                                <p data-role="contact_helper" class="mt-1 text-[11px] text-gray-500"></p>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Relationship <span class="text-red-600">*</span></label>
                                                <select name="relationship_to_head" data-role="relationship_to_head" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition-all duration-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                                                    <option value="">Select</option>
                                                    <optgroup label="Immediate Family">
                                                        <option value="son" @selected(strcasecmp((string) $member->relationship_to_head, 'son') === 0)>Son</option>
                                                        <option value="daughter" @selected(strcasecmp((string) $member->relationship_to_head, 'daughter') === 0)>Daughter</option>
                                                        <option value="spouse" @selected(strcasecmp((string) $member->relationship_to_head, 'spouse') === 0)>Spouse</option>
                                                        <option value="father" @selected(strcasecmp((string) $member->relationship_to_head, 'father') === 0)>Father</option>
                                                        <option value="mother" @selected(strcasecmp((string) $member->relationship_to_head, 'mother') === 0)>Mother</option>
                                                    </optgroup>
                                                    <optgroup label="Siblings">
                                                        <option value="brother" @selected(strcasecmp((string) $member->relationship_to_head, 'brother') === 0)>Brother</option>
                                                        <option value="sister" @selected(strcasecmp((string) $member->relationship_to_head, 'sister') === 0)>Sister</option>
                                                    </optgroup>
                                                    <optgroup label="Extended Family">
                                                        <option value="grandfather" @selected(strcasecmp((string) $member->relationship_to_head, 'grandfather') === 0)>Grandfather</option>
                                                        <option value="grandmother" @selected(strcasecmp((string) $member->relationship_to_head, 'grandmother') === 0)>Grandmother</option>
                                                        <option value="uncle" @selected(strcasecmp((string) $member->relationship_to_head, 'uncle') === 0)>Uncle</option>
                                                        <option value="aunt" @selected(strcasecmp((string) $member->relationship_to_head, 'aunt') === 0)>Aunt</option>
                                                        <option value="cousin" @selected(strcasecmp((string) $member->relationship_to_head, 'cousin') === 0)>Cousin</option>
                                                        <option value="nephew" @selected(strcasecmp((string) $member->relationship_to_head, 'nephew') === 0)>Nephew</option>
                                                        <option value="niece" @selected(strcasecmp((string) $member->relationship_to_head, 'niece') === 0)>Niece</option>
                                                    </optgroup>
                                                    <optgroup label="Other">
                                                        <option value="guardian" @selected(strcasecmp((string) $member->relationship_to_head, 'guardian') === 0)>Guardian</option>
                                                        <option value="boarder" @selected(strcasecmp((string) $member->relationship_to_head, 'boarder') === 0)>Boarder</option>
                                                        <option value="helper" @selected(strcasecmp((string) $member->relationship_to_head, 'helper') === 0)>Helper</option>
                                                        <option value="other" @selected(strcasecmp((string) $member->relationship_to_head, 'other') === 0)>Other</option>
                                                    </optgroup>
                                                </select>
                                                <p data-role="relationship_helper" class="mt-1 text-[11px] text-gray-500">Available relationships depend on age. For Son/Daughter, age must be lower than head and at least 12 years gap.</p>
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                                            <button type="button" onclick="document.getElementById('editMemberModal-{{ $member->family_member_id }}').classList.add('hidden')" class="ui-focus-ring rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                                            <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            @if (isset($membersPaginator) && $membersPaginator && $membersPaginator->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $membersPaginator->links() }}
                </div>
            @endif
        </div>
    @elseif ($canManageFamily && $members->count() === 0)
        <div class="ui-surface-card p-6 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="mt-3 text-sm text-gray-500">No family members available yet.</p>
            <p class="mt-1 text-xs text-gray-400">Click "Add Member" to register family members under a valid household.</p>
            <button type="button" onclick="document.getElementById('addMemberModal').classList.remove('hidden')"
                    class="ui-focus-ring mt-4 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Family Member
            </button>
        </div>
    @endif

    {{-- Recently removed (restore window) --}}
    @if ($isHead && $recentlyRemovedMembers->count() > 0)
        <div class="ui-surface-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Recently Removed (Restorable)</h3>
                <p class="mt-1 text-xs text-gray-500">You can restore removed members within 7 days.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($recentlyRemovedMembers as $removedMember)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $removedMember->full_name }}</p>
                            <p class="text-xs text-gray-500">Removed {{ optional($removedMember->deleted_at)->diffForHumans() }}</p>
                        </div>
                        <form method="POST" action="{{ route('family.restore', $removedMember->id) }}">
                            @csrf
                            <button type="submit" class="ui-focus-ring rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-medium text-emerald-700 transition hover:bg-emerald-50">
                                Restore
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Non-head info box --}}
    @if ($isHead)
        <div class="ui-surface-card p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Request Head Transfer</h3>
                    <p class="mt-1 text-xs text-gray-500">Choose one of your currently linked members as next head. Request is subject to admin/staff verification.</p>
                </div>
                @if ($pendingHeadTransferRequest)
                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Pending Review</span>
                @endif
            </div>

            @if ($latestHeadTransferRequest)
                @php
                    $status = (string) ($latestHeadTransferRequest->status ?? 'pending');
                    $statusClasses = $status === 'approved'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                        : ($status === 'rejected'
                            ? 'border-rose-200 bg-rose-50 text-rose-800'
                            : 'border-amber-200 bg-amber-50 text-amber-800');
                @endphp
                <div class="mt-3 rounded-lg border px-3 py-2 text-xs {{ $statusClasses }}">
                    <p class="font-medium">Latest request: {{ ucfirst($status) }}</p>
                    <p class="mt-1">
                        Target: <strong>{{ $latestHeadTransferRequest->newHead?->full_name ?? 'Unknown member' }}</strong>
                        ({{ $latestHeadTransferRequest->reasonLabel() }}{{ $latestHeadTransferRequest->details ? ' - ' . $latestHeadTransferRequest->details : '' }})
                    </p>
                    @if ($latestHeadTransferRequest->review_note)
                        <p class="mt-1">Review note: {{ $latestHeadTransferRequest->review_note }}</p>
                    @endif
                    @if ($latestHeadTransferRequest->processed_at)
                        <p class="mt-1">Reviewed at: {{ $latestHeadTransferRequest->processed_at->format('M d, Y h:i A') }}</p>
                    @endif
                </div>
            @endif

            @if ($pendingHeadTransferRequest)
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    Current pending request:
                    <strong>{{ $pendingHeadTransferRequest->newHead?->full_name ?? 'Unknown head' }}</strong>
                    ({{ $pendingHeadTransferRequest->reasonLabel() }}{{ $pendingHeadTransferRequest->details ? ' - ' . $pendingHeadTransferRequest->details : '' }})
                </div>
            @else
                <div class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                    Tap <strong>Request as Head</strong> beside a linked member in the family list to start the transfer request.
                </div>
                @if (! $hasEligibleLinkedMembers)
                    <p class="mt-3 text-xs text-amber-700">No eligible linked members found. Eligible member must be an active linked resident (18+).</p>
                @endif
                @if ($errors->has('requested_head_user_id') || $errors->has('reason') || $errors->has('details'))
                    <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                        {{ $errors->first('requested_head_user_id') ?: ($errors->first('reason') ?: $errors->first('details')) }}
                    </div>
                @endif
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-500">
            <strong>Note:</strong> Only the head of family can manage family members. Contact the barangay office if you need changes to your family linkage.
        </div>
    @endif
</div>

{{-- ===== ADD MEMBER MODAL (head only) ===== --}}
@if ($canManageFamily)
<div id="addMemberModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/50" onclick="document.getElementById('addMemberModal').classList.add('hidden')"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">Add Family Member</h3>
                <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @if ($errors->any() && session('_family_form'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('family.store') }}" class="space-y-4 family-member-form" data-head-age="{{ (int) $headAge }}">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">First Name <span class="text-red-600">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('first_name') border-red-500 @enderror"
                               placeholder="Juan">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Last Name <span class="text-red-600">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('last_name') border-red-500 @enderror"
                               placeholder="Dela Cruz">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                               placeholder="Santos">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Suffix</label>
                        <select name="suffix" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                            <option value="">None</option>
                            @foreach (['Jr.', 'Sr.', 'I', 'II', 'III', 'IV'] as $s)
                                <option value="{{ $s }}" @selected(old('suffix') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Birthdate <span class="text-red-600">*</span></label>
                        <input type="date" name="birthdate" data-role="birthdate" value="{{ old('birthdate') }}" required
                               max="{{ now('Asia/Manila')->format('Y-m-d') }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('birthdate') border-red-500 @enderror">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Age</label>
                        <input type="text" data-role="age" value="{{ old('birthdate') ? \Carbon\Carbon::parse(old('birthdate'))->age : '' }}" readonly class="block w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gender <span class="text-red-600">*</span></label>
                        <select name="gender" data-role="gender" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                            <option value="">Select</option>
                            @foreach (['male' => 'Male', 'female' => 'Female'] as $v => $l)
                                <option value="{{ $v }}" @selected(old('gender') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Contact Number</label>
                        <input
                               type="text"
                               id="contact_number"
                               name="contact_number"
                               data-role="contact_number"
                               value="{{ old('contact_number') }}"
                               maxlength="11"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20 @error('contact_number') border-red-500 @enderror"
                               placeholder="09XXXXXXXXX">
                        <p data-role="contact_helper" class="mt-1 text-[11px] text-gray-500"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Relationship <span class="text-red-600">*</span></label>
                        <select name="relationship_to_head" id="relationship_to_head" data-role="relationship_to_head" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm transition-all duration-200 focus:border-red-500 focus:ring-2 focus:ring-red-500/20">
                            <option value="">Select</option>
                            <optgroup label="Immediate Family">
                                <option value="son" @selected(strtolower((string) old('relationship_to_head')) === 'son')>Son</option>
                                <option value="daughter" @selected(strtolower((string) old('relationship_to_head')) === 'daughter')>Daughter</option>
                                <option value="spouse" @selected(strtolower((string) old('relationship_to_head')) === 'spouse')>Spouse</option>
                                <option value="father" @selected(strtolower((string) old('relationship_to_head')) === 'father')>Father</option>
                                <option value="mother" @selected(strtolower((string) old('relationship_to_head')) === 'mother')>Mother</option>
                            </optgroup>
                            <optgroup label="Siblings">
                                <option value="brother" @selected(strtolower((string) old('relationship_to_head')) === 'brother')>Brother</option>
                                <option value="sister" @selected(strtolower((string) old('relationship_to_head')) === 'sister')>Sister</option>
                            </optgroup>
                            <optgroup label="Extended Family">
                                <option value="grandfather" @selected(strtolower((string) old('relationship_to_head')) === 'grandfather')>Grandfather</option>
                                <option value="grandmother" @selected(strtolower((string) old('relationship_to_head')) === 'grandmother')>Grandmother</option>
                                <option value="uncle" @selected(strtolower((string) old('relationship_to_head')) === 'uncle')>Uncle</option>
                                <option value="aunt" @selected(strtolower((string) old('relationship_to_head')) === 'aunt')>Aunt</option>
                                <option value="cousin" @selected(strtolower((string) old('relationship_to_head')) === 'cousin')>Cousin</option>
                                <option value="nephew" @selected(strtolower((string) old('relationship_to_head')) === 'nephew')>Nephew</option>
                                <option value="niece" @selected(strtolower((string) old('relationship_to_head')) === 'niece')>Niece</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="guardian" @selected(strtolower((string) old('relationship_to_head')) === 'guardian')>Guardian</option>
                                <option value="boarder" @selected(strtolower((string) old('relationship_to_head')) === 'boarder')>Boarder</option>
                                <option value="helper" @selected(strtolower((string) old('relationship_to_head')) === 'helper')>Helper</option>
                                <option value="other" @selected(strtolower((string) old('relationship_to_head')) === 'other')>Other</option>
                            </optgroup>
                        </select>
                        <p data-role="relationship_helper" class="mt-1 text-[11px] text-gray-500">Available relationships depend on age. For Son/Daughter, age must be lower than head and at least 12 years gap.</p>
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs text-gray-500">
                    This member will be registered under your household and will inherit your address and purok.
                </div>
                <label class="inline-flex items-start gap-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs text-blue-800">
                    <input type="checkbox" name="link_existing_if_duplicate" value="1" @checked(old('link_existing_if_duplicate'))
                           class="mt-0.5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>If a matching resident record already exists, link that existing resident instead of creating a duplicate.</span>
                </label>

                <div class="flex justify-end gap-3 pt-3 border-t border-gray-200">
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" class="ui-focus-ring rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Auto-open modal if there are validation errors from the add form --}}
@if ($errors->any() && session('_family_form'))
<script>document.getElementById('addMemberModal').classList.remove('hidden');</script>
@endif
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    function calculateAge(birthdate) {
        const date = new Date(birthdate);
        if (Number.isNaN(date.getTime())) return '';
        const today = new Date();
        let age = today.getFullYear() - date.getFullYear();
        const monthDiff = today.getMonth() - date.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < date.getDate())) {
            age--;
        }
        return Math.max(age, 0);
    }

    function updateRelationshipOptions(age, gender, relationshipSelect, isBirthdateChange = false, helper = null) {
        if (!relationshipSelect) return;

        const options = relationshipSelect.querySelectorAll('option');
        const alwaysAllowed = ['son', 'daughter', 'brother', 'sister', 'cousin'];
        const previousValue = relationshipSelect.value;
        const normalizedGender = (gender || '').toLowerCase();

        options.forEach((option) => {
            if (!option.value) return;

            option.disabled = false;
            option.hidden = false;
            const value = option.value.toLowerCase();

            if (alwaysAllowed.includes(value)) return;

            if (age < 10) {
                option.disabled = true;
                option.hidden = true;
                return;
            }

            if (age >= 10 && age <= 17) {
                const allowedTeen = ['nephew', 'niece', 'guardian'];
                if (!allowedTeen.includes(value)) {
                    option.disabled = true;
                    option.hidden = true;
                }
                return;
            }

            if (age >= 18 && age <= 24) {
                if (['father', 'mother', 'grandfather', 'grandmother'].includes(value)) {
                    option.disabled = true;
                    option.hidden = true;
                }
                if (age < 21 && ['uncle', 'aunt'].includes(value)) {
                    option.disabled = true;
                    option.hidden = true;
                }
                return;
            }

            if (age >= 25 && age <= 39) {
                if (['grandfather', 'grandmother'].includes(value)) {
                    option.disabled = true;
                    option.hidden = true;
                }
                return;
            }
        });

        if (age < 10) {
            if (normalizedGender === 'male') {
                relationshipSelect.value = 'son';
            } else if (normalizedGender === 'female') {
                relationshipSelect.value = 'daughter';
            } else {
                relationshipSelect.value = 'cousin';
            }
        } else if (isBirthdateChange) {
            relationshipSelect.value = '';
        } else {
            const selectedOption = relationshipSelect.querySelector(`option[value="${previousValue}"]`);
            if (!selectedOption || selectedOption.disabled || selectedOption.hidden) {
                relationshipSelect.value = '';
            }
        }

        if (helper) {
            if (age < 10) {
                helper.textContent = 'Only child-safe relationships are available below age 10.';
            } else if (age >= 10 && age <= 17) {
                helper.textContent = 'Minor-safe relationship options are enabled.';
            } else if (age >= 18 && age <= 24) {
                helper.textContent = 'Parent and grandparent roles are restricted for ages 18–24.';
            } else if (age >= 25 && age <= 39) {
                helper.textContent = 'Grandparent roles are restricted below age 40.';
            } else if (age >= 60) {
                helper.textContent = 'All relationships are available. Grandfather/Grandmother are prioritized for seniors.';
            } else {
                helper.textContent = 'All relationship options are available.';
            }
        }
    }

    function validateChildRelationship(memberAge, headAge) {
        if (memberAge >= headAge) {
            return 'Child must be younger than head of family.';
        }

        if ((headAge - memberAge) < 12) {
            return 'Age gap between parent and child must be at least 12 years.';
        }

        return null;
    }

    function showRelationshipError(relationshipSelect, helper, message) {
        if (!relationshipSelect || !helper) return;
        relationshipSelect.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
        helper.textContent = message;
        helper.classList.remove('text-gray-500');
        helper.classList.add('text-red-600');
    }

    function clearRelationshipError(relationshipSelect, helper) {
        if (!relationshipSelect || !helper) return;
        relationshipSelect.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
        helper.classList.remove('text-red-600');
        helper.classList.add('text-gray-500');
    }

    function handleContactField(age, contact, helper) {
        if (!contact || !helper) return;

        if (age < 10) {
            contact.value = '';
            contact.disabled = true;
            contact.required = false;
            contact.classList.add('bg-gray-100', 'cursor-not-allowed');
            helper.textContent = 'Not required for children below 10';
        } else if (age >= 10 && age <= 17) {
            contact.disabled = false;
            contact.required = false;
            contact.classList.remove('bg-gray-100', 'cursor-not-allowed');
            helper.textContent = 'Optional';
        } else {
            contact.disabled = false;
            contact.required = true;
            contact.classList.remove('bg-gray-100', 'cursor-not-allowed');
            helper.textContent = 'Required';
        }
    }

    function bindContactSanitizers(contact) {
        if (!contact) return;

        const sanitize = () => {
            contact.value = contact.value.replace(/[^0-9]/g, '').slice(0, 11);
        };

        contact.addEventListener('input', sanitize);
        contact.addEventListener('paste', function () {
            setTimeout(sanitize, 0);
        });
        sanitize();
    }

    document.querySelectorAll('.family-member-form').forEach((form) => {
        const headAge = Number(form.getAttribute('data-head-age') || 0);
        const birthdateInput = form.querySelector('[data-role="birthdate"]');
        const ageInput = form.querySelector('[data-role="age"]');
        const genderInput = form.querySelector('[data-role="gender"]');
        const relationshipSelect = form.querySelector('[data-role="relationship_to_head"]');
        const relationshipHelper = form.querySelector('[data-role="relationship_helper"]');
        const contactInput = form.querySelector('[data-role="contact_number"]');
        const contactHelper = form.querySelector('[data-role="contact_helper"]');

        bindContactSanitizers(contactInput);

        const refreshAgeState = (isBirthdateChange = false) => {
            if (!birthdateInput || !birthdateInput.value) {
                if (ageInput) ageInput.value = '';
                if (contactInput) {
                    contactInput.disabled = false;
                    contactInput.required = false;
                    contactInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
                }
                if (contactHelper) contactHelper.textContent = '';
                if (relationshipHelper) relationshipHelper.textContent = 'Available relationships depend on age. For Son/Daughter, age must be lower than head and at least 12 years gap.';
                return;
            }

            const age = calculateAge(birthdateInput.value);
            if (ageInput) ageInput.value = age;
            updateRelationshipOptions(age, genderInput ? genderInput.value : '', relationshipSelect, isBirthdateChange, relationshipHelper);
            handleContactField(age, contactInput, contactHelper);
            clearRelationshipError(relationshipSelect, relationshipHelper);
        };

        if (birthdateInput) {
            birthdateInput.addEventListener('change', () => refreshAgeState(true));
        }
        if (genderInput) {
            genderInput.addEventListener('change', () => refreshAgeState(false));
        }
        if (relationshipSelect) {
            relationshipSelect.addEventListener('change', () => {
                clearRelationshipError(relationshipSelect, relationshipHelper);
            });
        }

        form.addEventListener('submit', function (event) {
            if (!birthdateInput || !ageInput || !relationshipSelect || !relationshipHelper || headAge <= 0) {
                return;
            }

            const memberAge = Number(ageInput.value || 0);
            const relationship = String(relationshipSelect.value || '').toLowerCase();
            if (relationship === 'son' || relationship === 'daughter') {
                const error = validateChildRelationship(memberAge, headAge);
                if (error) {
                    event.preventDefault();
                    showRelationshipError(relationshipSelect, relationshipHelper, error);
                }
            }
        });

        refreshAgeState(false);
    });

    document.querySelectorAll('[data-transfer-open]').forEach((button) => {
        button.addEventListener('click', function () {
            const id = button.getAttribute('data-transfer-open');
            const modal = id ? document.getElementById(id) : null;
            if (modal) {
                modal.classList.remove('hidden');
            }
        });
    });

    document.querySelectorAll('[data-transfer-close]').forEach((button) => {
        button.addEventListener('click', function () {
            const id = button.getAttribute('data-transfer-close');
            const modal = id ? document.getElementById(id) : null;
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    });

    document.querySelectorAll('[data-transfer-reason]').forEach((reasonSelect) => {
        const form = reasonSelect.closest('form');
        const detailsWrap = form ? form.querySelector('[data-transfer-details]') : null;
        const detailsField = detailsWrap ? detailsWrap.querySelector('textarea[name="details"]') : null;

        const refresh = () => {
            const isOther = String(reasonSelect.value || '') === 'other';
            if (!detailsWrap || !detailsField) return;
            detailsWrap.classList.toggle('hidden', !isOther);
            detailsField.required = isOther;
        };

        reasonSelect.addEventListener('change', refresh);
        refresh();
    });
});
</script>
