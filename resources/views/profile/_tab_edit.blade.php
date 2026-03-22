{{-- Edit Profile Tab --}}
<div class="max-w-2xl">
    <div class="ui-surface-card p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Edit Profile</h2>
            <a href="{{ route('profile.show', ['tab' => 'info']) }}" class="ui-focus-ring rounded-md text-xs font-medium text-gray-500 hover:text-gray-700">Cancel</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Personal --}}
            <fieldset>
                <legend class="text-xs uppercase tracking-wider text-gray-500 mb-3">Personal Information</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">First name <span class="text-red-600">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Middle name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Last name <span class="text-red-600">*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Suffix</label>
                        <select name="suffix" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">None</option>
                            @foreach (['Jr.', 'Sr.', 'I', 'II', 'III', 'IV'] as $s)
                                <option value="{{ $s }}" @selected(old('suffix', $user->suffix) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            {{-- Demographics --}}
            <fieldset>
                <legend class="text-xs uppercase tracking-wider text-gray-500 mb-3">Demographics</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Gender <span class="text-red-600">*</span></label>
                        <select name="gender" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $v => $l)
                                <option value="{{ $v }}" @selected(old('gender', $user->gender) === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Birthdate <span class="text-red-600">*</span></label>
                        <input type="date" name="birthdate" value="{{ old('birthdate', optional($user->birthdate)->toDateString()) }}" required
                               max="{{ now()->subDay()->format('Y-m-d') }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('birthdate') border-red-500 @enderror">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Civil status <span class="text-red-600">*</span></label>
                        <select name="civil_status" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach (['single', 'married', 'widowed', 'divorced', 'separated'] as $cs)
                                <option value="{{ $cs }}" @selected(old('civil_status', $user->civil_status) === $cs)>{{ ucfirst($cs) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Contact number <span class="text-red-600">*</span></label>
                        <input type="tel" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('contact_number') border-red-500 @enderror">
                    </div>
                </div>
            </fieldset>

            {{-- Address --}}
            <fieldset>
                <legend class="text-xs uppercase tracking-wider text-gray-500 mb-3">Address</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">House number <span class="text-red-600">*</span></label>
                        <input type="text" name="house_no" value="{{ old('house_no', $user->house_no) }}" required
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Purok <span class="text-red-600">*</span></label>
                        <select name="purok_id" required class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            @foreach ($puroks ?? [] as $purok)
                                <option value="{{ $purok->id }}" @selected(old('purok_id', $user->purok_id) == $purok->id)>{{ $purok->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </fieldset>

            {{-- Non-editable notice --}}
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-xs text-gray-500">
                <strong>Note:</strong> Head of family status, family linkage, and account role cannot be changed here. Use the <strong>Verification & Claims</strong> tab for PWD/Senior submissions.
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('profile.show', ['tab' => 'info']) }}" class="ui-focus-ring rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">Save Changes</button>
            </div>
        </form>
    </div>
</div>
