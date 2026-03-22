{{-- Profile Information Tab (read-only) --}}
@php
    $isHeadHousehold = $user->isHeadOfFamily() && $user->head_of_family_id === null;
    $linkedHead = $isHeadHousehold ? $user : $user->headOfFamilyUser;
    $householdMembersCount = $householdMembersTotal ?? ($isHeadHousehold
        ? ($user->familyMemberRecords?->count() ?? 0)
        : ($user->headOfFamilyUser?->familyMemberRecords?->count() ?? 0));
@endphp
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">

        {{-- Personal Information --}}
        <div class="ui-surface-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Personal Information</h2>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Full name</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Gender</dt>
                    <dd class="mt-0.5 text-gray-800">{{ ucfirst($user->gender) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Birthdate</dt>
                    <dd class="mt-0.5 text-gray-800">{{ optional($user->birthdate)->format('F d, Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Age</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->age ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Civil status</dt>
                    <dd class="mt-0.5 text-gray-800">{{ ucfirst($user->civil_status) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Contact number</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->contact_number }}</dd>
                </div>
            </dl>
        </div>

        {{-- Read-only notice --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-3 text-sm text-amber-800">
            <svg class="h-5 w-5 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            </svg>
            <span>Your profile information can only be updated by the Barangay Office. If any details are incorrect, please visit or contact us directly.</span>
        </div>

        {{-- Address --}}
        <div class="ui-surface-card p-6">
            <h2 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-4">Address</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Address</dt>
                    <dd class="mt-0.5 text-gray-800">
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
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Purok</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $normalizedPurok ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Resident type</dt>
                    <dd class="mt-0.5 text-gray-800">{{ ucfirst($user->resident_type) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Head of family</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->isHeadOfFamily() ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Household Link Status</dt>
                    <dd class="mt-0.5">
                        @if ($isHeadHousehold)
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800">Head Household</span>
                        @elseif ($user->head_of_family_id && $linkedHead)
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">Linked</span>
                        @elseif ($user->family_link_status === 'pending_link')
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">Pending Link</span>
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">Unlinked</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Linked Head</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $linkedHead?->full_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Household Members</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $householdMembersCount }}</dd>
                </div>
                @if (! $isHeadHousehold)
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-gray-500">Household connection type</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $user->householdConnectionTypeLabel() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-gray-500">Connection note</dt>
                        <dd class="mt-0.5 text-gray-800">{{ $user->connection_note ?: '—' }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Sidebar: Account --}}
    <div class="space-y-4">
        <div class="ui-surface-card p-6">
            <h2 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-4">Account</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Email</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Role</dt>
                    <dd class="mt-0.5">
                        @php
                            $roleColors = ['resident' => 'bg-blue-100 text-blue-800', 'staff' => 'bg-amber-100 text-amber-800', 'admin' => 'bg-slate-100 text-slate-800'];
                        @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-slate-100 text-slate-800' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Status</dt>
                    <dd class="mt-0.5">
                        @if ($user->status === 'approved')
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-800">Approved</span>
                        @elseif ($user->status === 'pending')
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-800">{{ ucfirst($user->status) }}</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Registered</dt>
                    <dd class="mt-0.5 text-gray-800">{{ $user->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Classification --}}
        <div class="ui-surface-card p-6">
            <h2 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-4">Classification</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-xs uppercase tracking-wider text-gray-500">PWD</dt>
                    <dd>
                        @if ($user->is_pwd)
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst(str_replace('_', ' ', $user->pwd_status ?: 'not_submitted')) }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">No</span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-xs uppercase tracking-wider text-gray-500">Senior Citizen</dt>
                    <dd>
                        @if ($user->is_senior)
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-800">
                                {{ ucfirst(str_replace('_', ' ', $user->senior_status ?: 'not_submitted')) }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">No</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
