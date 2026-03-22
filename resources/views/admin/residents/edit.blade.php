@extends('layouts.admin')

@section('title', 'Edit Resident - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Edit Resident</h1>
            <a href="{{ route('admin.residents.show', $user) }}" class="text-sm font-medium text-gray-600 hover:text-gray-800">
                Back to profile
            </a>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error">
                {{ $errors->first() }}
            </x-ui.alert>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-6 space-y-6">
            <form method="POST" action="{{ route('admin.residents.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Personal Information</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Middle name</label>
                            <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                            <input type="text" name="suffix" value="{{ old('suffix', $user->suffix) }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Address</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">House number</label>
                            <input type="text" name="house_no" value="{{ old('house_no', $user->house_no) }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purok</label>
                            <select name="purok_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Select purok</option>
                                @foreach ($puroks as $purok)
                                    <option value="{{ $purok->id }}" @selected(old('purok_id', $user->purok_id) == $purok->id)>{{ $purok->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Demographics</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" required
                                   pattern="^(\+63|0)?9[0-9]{9}$" inputmode="numeric"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <p class="mt-1 text-xs text-gray-500">Use 09171234567 or +639171234567 format.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                            <p class="px-3 py-2 text-sm text-gray-700 bg-gray-50 rounded-lg border border-gray-200">{{ $user->birthdate ? $user->birthdate->age : $user->age }} <span class="text-xs text-gray-400">(auto-calculated from birthdate)</span></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select name="gender" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">Select</option>
                                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('gender', $user->gender) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                            <input type="date" name="birthdate" value="{{ old('birthdate', optional($user->birthdate)->toDateString()) }}" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Civil status</label>
                            <select name="civil_status" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @foreach (['single','married','widowed','divorced','separated'] as $status)
                                    <option value="{{ $status }}" @selected(old('civil_status', $user->civil_status) === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Resident type</label>
                            <select name="resident_type" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @foreach (['permanent' => 'Permanent', 'non-permanent' => 'Non-permanent'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('resident_type', $user->resident_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="permanent-address-fields" class="mt-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="mb-3 text-xs font-medium uppercase tracking-wide text-gray-500">Permanent Address (Philippines)</p>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">House number</label>
                                <input type="text" name="permanent_house_no" id="permanent_house_no"
                                       value="{{ old('permanent_house_no', $user->permanent_house_no) }}"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @error('permanent_house_no')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                                <input type="text" name="permanent_street" id="permanent_street"
                                       value="{{ old('permanent_street', $user->permanent_street) }}"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                @error('permanent_street')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                <select name="permanent_region" id="permanent_region" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="{{ old('permanent_region', $user->permanent_region) }}">
                                        {{ old('permanent_region', $user->permanent_region) ?: 'Select region' }}
                                    </option>
                                </select>
                                @error('permanent_region')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                <select name="permanent_province" id="permanent_province" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="{{ old('permanent_province', $user->permanent_province) }}">
                                        {{ old('permanent_province', $user->permanent_province) ?: 'Select province' }}
                                    </option>
                                </select>
                                @error('permanent_province')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City / Municipality</label>
                                <select name="permanent_city" id="permanent_city" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="{{ old('permanent_city', $user->permanent_city) }}">
                                        {{ old('permanent_city', $user->permanent_city) ?: 'Select city / municipality' }}
                                    </option>
                                </select>
                                @error('permanent_city')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                                <select name="permanent_barangay" id="permanent_barangay" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="{{ old('permanent_barangay', $user->permanent_barangay) }}">
                                        {{ old('permanent_barangay', $user->permanent_barangay) ?: 'Select barangay' }}
                                    </option>
                                </select>
                                @error('permanent_barangay')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">Household</h2>
                    <div class="space-y-3 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_head" value="1" {{ old('is_head', optional($user->householdAsHead)->exists()) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span>Set as head of household</span>
                        </label>
                        <p class="text-xs text-gray-500">
                            If set as head, a household record will be created or updated using this resident and their purok.
                        </p>
                        <div id="household-member-fields">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assign to existing household (optional)</label>
                            <select name="household_id" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                <option value="">— None —</option>
                                @foreach ($householdHeads as $head)
                                    <option value="{{ optional($head->householdAsHead)->id }}"
                                        @selected(old('household_id', $user->household_id) === optional($head->householdAsHead)->id)>
                                        @php
                                            $headPurokLabel = trim((string) (optional($head->purokRelation)->name ?? $head->purok));
                                            $headNormalizedPurok = preg_match('/^purok\b/i', $headPurokLabel) ? $headPurokLabel : ('Purok ' . $headPurokLabel);
                                        @endphp
                                        {{ $head->last_name }}, {{ $head->first_name }} ({{ $headNormalizedPurok }})
                                    </option>
                                @endforeach
                            </select>
                            @error('household_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Relationship to head</label>
                                <select name="relationship_to_head" id="relationship_to_head" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Select relationship</option>
                                    @foreach ([
                                        'spouse' => 'Spouse',
                                        'partner' => 'Partner',
                                        'father' => 'Father',
                                        'mother' => 'Mother',
                                        'son' => 'Son',
                                        'daughter' => 'Daughter',
                                        'brother' => 'Brother',
                                        'sister' => 'Sister',
                                        'grandfather' => 'Grandfather',
                                        'grandmother' => 'Grandmother',
                                        'uncle' => 'Uncle',
                                        'aunt' => 'Aunt',
                                        'cousin' => 'Cousin',
                                        'nephew' => 'Nephew',
                                        'niece' => 'Niece',
                                        'guardian' => 'Guardian',
                                        'boarder' => 'Boarder',
                                        'helper' => 'Helper',
                                        'other' => 'Other',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('relationship_to_head', $user->relationship_to_head) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p id="relationship-age-hint" class="mt-1 text-xs text-gray-500"></p>
                                @error('relationship_to_head')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Family Linking Note --}}
                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs text-gray-500">
                    <strong>Family Linking:</strong> To change the head of family assignment, go to the
                    <a href="{{ route('admin.residents.show', $user) }}" class="text-blue-600 underline">resident profile page</a>
                    and use the Family Linking section.
                </div>

                {{-- Staff Module Permissions --}}
                @if ($user->role === 'staff')
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 mb-1">Module Access Permissions</h2>
                        <p class="text-xs text-gray-500 mb-4">Control which modules this staff member can manage.</p>

                        @php $perm = $user->staffPermission; @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Registration Management --}}
                            <label class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 transition">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Registration Access</span>
                                    <p class="text-xs text-gray-500">Approve, reject & manage registrations</p>
                                </div>
                                <div class="relative ml-3 shrink-0">
                                    <input type="hidden" name="can_manage_registrations" value="0">
                                    <input type="checkbox" name="can_manage_registrations" value="1"
                                        class="sr-only peer"
                                        {{ old('can_manage_registrations', $perm?->can_manage_registrations) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </div>
                            </label>

                            {{-- e-Blotter --}}
                            <label class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 transition">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">e-Blotter Access</span>
                                    <p class="text-xs text-gray-500">Manage blotter records & requests</p>
                                </div>
                                <div class="relative ml-3 shrink-0">
                                    <input type="hidden" name="can_manage_blotter" value="0">
                                    <input type="checkbox" name="can_manage_blotter" value="1"
                                        class="sr-only peer"
                                        {{ old('can_manage_blotter', $perm?->can_manage_blotter) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </div>
                            </label>

                            {{-- Announcements --}}
                            <label class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 transition">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Announcement Access</span>
                                    <p class="text-xs text-gray-500">Create & manage announcements</p>
                                </div>
                                <div class="relative ml-3 shrink-0">
                                    <input type="hidden" name="can_manage_announcements" value="0">
                                    <input type="checkbox" name="can_manage_announcements" value="1"
                                        class="sr-only peer"
                                        {{ old('can_manage_announcements', $perm?->can_manage_announcements) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </div>
                            </label>

                            {{-- Complaints --}}
                            <label class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 transition">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Complaints Access</span>
                                    <p class="text-xs text-gray-500">Handle issue reports & complaints</p>
                                </div>
                                <div class="relative ml-3 shrink-0">
                                    <input type="hidden" name="can_manage_complaints" value="0">
                                    <input type="checkbox" name="can_manage_complaints" value="1"
                                        class="sr-only peer"
                                        {{ old('can_manage_complaints', $perm?->can_manage_complaints) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </div>
                            </label>

                            {{-- Reports --}}
                            <label class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 cursor-pointer hover:bg-gray-100 transition">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Reports Access</span>
                                    <p class="text-xs text-gray-500">View reports & analytics data</p>
                                </div>
                                <div class="relative ml-3 shrink-0">
                                    <input type="hidden" name="can_manage_reports" value="0">
                                    <input type="checkbox" name="can_manage_reports" value="1"
                                        class="sr-only peer"
                                        {{ old('can_manage_reports', $perm?->can_manage_reports) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.residents.show', $user) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contactInput = document.querySelector('input[name="contact_number"]');
    if (contactInput) {
        contactInput.addEventListener('input', function () {
            // Allow only digits and a single leading plus sign.
            let value = this.value.replace(/[^0-9+]/g, '');
            if (value.includes('+')) {
                value = (value.startsWith('+') ? '+' : '') + value.replace(/\+/g, '');
            }
            this.value = value;
        });
    }

    const isHeadCheckbox = document.querySelector('input[name="is_head"]');
    const householdMemberFields = document.getElementById('household-member-fields');
    const householdSelect = document.querySelector('select[name="household_id"]');
    const relationshipSelect = document.getElementById('relationship_to_head');
    const birthdateInput = document.querySelector('input[name="birthdate"]');
    const relationshipAgeHint = document.getElementById('relationship-age-hint');
    const residentTypeSelect = document.querySelector('select[name="resident_type"]');
    const permanentAddressFields = document.getElementById('permanent-address-fields');
    const permanentInputs = [
        document.getElementById('permanent_house_no'),
        document.getElementById('permanent_street'),
        document.getElementById('permanent_region'),
        document.getElementById('permanent_province'),
        document.getElementById('permanent_city'),
        document.getElementById('permanent_barangay'),
    ];

    const allRelationshipOptions = [
        'spouse', 'partner', 'father', 'mother', 'son', 'daughter', 'brother', 'sister',
        'grandfather', 'grandmother', 'uncle', 'aunt', 'cousin', 'nephew', 'niece',
        'guardian', 'boarder', 'helper', 'other'
    ];
    const minorRelationshipOptions = ['son', 'daughter', 'brother', 'sister', 'nephew', 'niece', 'cousin', 'other'];

    function getAge(dateString) {
        if (!dateString) return null;
        const birthDate = new Date(dateString);
        if (Number.isNaN(birthDate.getTime())) return null;
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    function applyRelationshipAgeOptions() {
        if (!relationshipSelect) return;

        const age = getAge(birthdateInput ? birthdateInput.value : null);
        const allowed = age !== null && age < 18 ? minorRelationshipOptions : allRelationshipOptions;

        Array.from(relationshipSelect.options).forEach((option) => {
            if (!option.value) return;
            const enabled = allowed.includes(option.value);
            option.hidden = !enabled;
            option.disabled = !enabled;
        });

        if (relationshipSelect.value && !allowed.includes(relationshipSelect.value)) {
            relationshipSelect.value = '';
        }

        if (relationshipAgeHint) {
            relationshipAgeHint.textContent = age !== null && age < 18
                ? 'Minor profile detected: only age-appropriate household relationships are available.'
                : 'Relationship options are filtered by resident age.';
        }
    }

    function toggleHouseholdMemberFields() {
        if (!isHeadCheckbox || !householdMemberFields) return;

        const isHead = isHeadCheckbox.checked;
        householdMemberFields.classList.toggle('hidden', isHead);

        if (householdSelect) householdSelect.disabled = isHead;
        if (relationshipSelect) relationshipSelect.disabled = isHead;

        if (isHead) {
            if (householdSelect) householdSelect.value = '';
            if (relationshipSelect) relationshipSelect.value = '';
        } else {
            applyRelationshipAgeOptions();
        }
    }

    function setSelectOptions(selectEl, items, selectedText, placeholder) {
        if (!selectEl) return;
        const target = (selectedText || '').trim().toLowerCase();
        selectEl.innerHTML = '';
        const base = document.createElement('option');
        base.value = '';
        base.textContent = placeholder;
        selectEl.appendChild(base);

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.name;
            option.textContent = item.label || item.name;
            if (target !== '' && item.name.trim().toLowerCase() === target) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
    }

    function getPsgcCacheKey(url) {
        return `psgc-cache-v1::${url}`;
    }

    function readCachedPsgcData(url) {
        try {
            const raw = localStorage.getItem(getPsgcCacheKey(url));
            if (!raw) return null;
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed?.data) ? parsed.data : null;
        } catch (e) {
            return null;
        }
    }

    function writeCachedPsgcData(url, data) {
        try {
            localStorage.setItem(getPsgcCacheKey(url), JSON.stringify({
                cached_at: Date.now(),
                data,
            }));
        } catch (e) {
            // Ignore localStorage quota/privacy-mode errors.
        }
    }

    async function fetchJson(url) {
        try {
            const response = await fetch(url, { cache: 'no-store' });
            if (!response.ok) {
                throw new Error('Failed to load address data.');
            }
            const data = await response.json();
            writeCachedPsgcData(url, data);
            return data;
        } catch (error) {
            const cached = readCachedPsgcData(url);
            if (cached) {
                return cached;
            }
            throw error;
        }
    }

    async function loadRegions() {
        const regionSelect = document.getElementById('permanent_region');
        const oldRegion = @json(old('permanent_region', $user->permanent_region));
        const regions = await fetchJson('https://psgc.gitlab.io/api/regions/');
        const sorted = regions.sort((a, b) => a.name.localeCompare(b.name));
        setSelectOptions(regionSelect, sorted, oldRegion, 'Select region');
        return sorted;
    }

    async function loadProvincesForRegion(region, selectedProvince) {
        const provinceSelect = document.getElementById('permanent_province');
        const citySelect = document.getElementById('permanent_city');
        const barangaySelect = document.getElementById('permanent_barangay');

        if (!region || !region.code) {
            setSelectOptions(provinceSelect, [], '', 'Select province');
            setSelectOptions(citySelect, [], '', 'Select city / municipality');
            setSelectOptions(barangaySelect, [], '', 'Select barangay');
            return [];
        }

        const provinces = await fetchJson(`https://psgc.gitlab.io/api/regions/${region.code}/provinces/`);
        const sorted = provinces.sort((a, b) => a.name.localeCompare(b.name));
        if (sorted.length > 0) {
            setSelectOptions(provinceSelect, sorted, selectedProvince, 'Select province');
            return sorted;
        }

        const directProvince = [{
            code: region.code,
            name: region.name,
            label: `${region.name} (No province)`,
            source: 'region',
        }];
        setSelectOptions(provinceSelect, directProvince, selectedProvince || region.name, 'Select province');
        return directProvince;
    }

    async function loadCitiesForProvince(province, selectedCity) {
        const citySelect = document.getElementById('permanent_city');
        const barangaySelect = document.getElementById('permanent_barangay');
        if (!province || !province.code) {
            setSelectOptions(citySelect, [], '', 'Select city / municipality');
            setSelectOptions(barangaySelect, [], '', 'Select barangay');
            return [];
        }

        const endpoint = province.source === 'region'
            ? `https://psgc.gitlab.io/api/regions/${province.code}/cities-municipalities/`
            : `https://psgc.gitlab.io/api/provinces/${province.code}/cities-municipalities/`;

        const cities = await fetchJson(endpoint);
        const sorted = cities.sort((a, b) => a.name.localeCompare(b.name));
        setSelectOptions(citySelect, sorted, selectedCity, 'Select city / municipality');
        return sorted;
    }

    async function loadBarangays(cityCode, selectedBarangay) {
        const barangaySelect = document.getElementById('permanent_barangay');
        if (!cityCode) {
            setSelectOptions(barangaySelect, [], '', 'Select barangay');
            return;
        }

        const barangays = await fetchJson(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`);
        const sorted = barangays.sort((a, b) => a.name.localeCompare(b.name));
        setSelectOptions(barangaySelect, sorted, selectedBarangay, 'Select barangay');
    }

    function togglePermanentAddressFields() {
        const isNonPermanent = residentTypeSelect && residentTypeSelect.value === 'non-permanent';
        if (permanentAddressFields) {
            permanentAddressFields.classList.toggle('hidden', !isNonPermanent);
        }
        permanentInputs.forEach((el) => {
            if (!el) return;
            el.required = isNonPermanent;
            if (!isNonPermanent) {
                el.value = '';
            }
        });
    }

    async function initPermanentAddressDropdowns() {
        const regionSelect = document.getElementById('permanent_region');
        const provinceSelect = document.getElementById('permanent_province');
        const citySelect = document.getElementById('permanent_city');
        const oldRegion = @json(old('permanent_region', $user->permanent_region));
        const oldProvince = @json(old('permanent_province', $user->permanent_province));
        const oldCity = @json(old('permanent_city', $user->permanent_city));
        const oldBarangay = @json(old('permanent_barangay', $user->permanent_barangay));
        let regions = [];
        let provinces = [];
        let cities = [];

        regions = await loadRegions();
        const selectedRegion = regions.find((item) => item.name.toLowerCase() === String(oldRegion || '').toLowerCase());
        provinces = await loadProvincesForRegion(selectedRegion || null, oldProvince);
        const selectedProvince = provinces.find((item) => item.name.toLowerCase() === String(oldProvince || '').toLowerCase()) || provinces[0] || null;
        cities = await loadCitiesForProvince(selectedProvince, oldCity);
        const selectedCity = cities.find((item) => item.name.toLowerCase() === String(oldCity || '').toLowerCase());
        await loadBarangays(selectedCity ? selectedCity.code : null, oldBarangay);

        regionSelect?.addEventListener('change', async function () {
            const selected = regions.find((item) => item.name === this.value) || null;
            provinces = await loadProvincesForRegion(selected, '');
            cities = [];
            setSelectOptions(document.getElementById('permanent_city'), [], '', 'Select city / municipality');
            setSelectOptions(document.getElementById('permanent_barangay'), [], '', 'Select barangay');
        });

        provinceSelect?.addEventListener('change', async function () {
            const selected = provinces.find((item) => item.name === this.value);
            cities = await loadCitiesForProvince(selected || null, '');
            await loadBarangays(null, '');
        });

        citySelect?.addEventListener('change', async function () {
            const selectedNow = cities.find((item) => item.name === this.value);
            if (!selectedNow) {
                await loadBarangays(null, '');
                return;
            }
            await loadBarangays(selectedNow.code, '');
        });
    }

    isHeadCheckbox?.addEventListener('change', toggleHouseholdMemberFields);
    birthdateInput?.addEventListener('change', applyRelationshipAgeOptions);
    residentTypeSelect?.addEventListener('change', togglePermanentAddressFields);
    applyRelationshipAgeOptions();
    toggleHouseholdMemberFields();
    togglePermanentAddressFields();
    initPermanentAddressDropdowns().catch(() => {
        // Keep page usable even when PSGC API is unavailable.
    });
});
</script>
@endsection

