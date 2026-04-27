@extends('layouts.auth')

@section('title', 'Register - e-Governance System')

@section('content')
<div class="w-full max-w-2xl">

    {{-- Logo & Branding --}}
    <div class="flex flex-col items-center mb-8">
        <img src="{{ asset('images/logo1.png') }}"
             alt="Barangay Paguiruan"
             class="h-20 md:h-24 w-auto object-contain mb-3">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Barangay Paguiruan, Floridablanca</h1>
        <p class="mt-1 text-sm text-gray-500">Resident Registration</p>
    </div>

    <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <p class="font-medium">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Section 1: Personal Information --}}
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-2">1</span>
                    Personal Information
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-600">*</span></label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                               class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('first_name') border-red-500 @else border-gray-300 @enderror">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle name</label>
                        <input type="text" name="middle_name" id="middle_name" value="{{ old('middle_name') }}"
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last name <span class="text-red-600">*</span></label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                               class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('last_name') border-red-500 @else border-gray-300 @enderror">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1">Suffix</label>
                        <select name="suffix" id="suffix"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus">
                            <option value="">None</option>
                            <option value="Jr." @selected(old('suffix') === 'Jr.')>Jr.</option>
                            <option value="Sr." @selected(old('suffix') === 'Sr.')>Sr.</option>
                            <option value="I" @selected(old('suffix') === 'I')>I</option>
                            <option value="II" @selected(old('suffix') === 'II')>II</option>
                            <option value="III" @selected(old('suffix') === 'III')>III</option>
                            <option value="IV" @selected(old('suffix') === 'IV')>IV</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- Section 2: Address Information --}}
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-2">2</span>
                    Address Information
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="house_no" class="block text-sm font-medium text-gray-700 mb-1">House number <span class="text-red-600">*</span></label>
                        <input type="text" name="house_no" id="house_no" value="{{ old('house_no') }}" required
                               class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('house_no') border-red-500 @else border-gray-300 @enderror">
                        @error('house_no')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="purok_id" class="block text-sm font-medium text-gray-700 mb-1">Purok <span class="text-red-600">*</span></label>
                        <select name="purok_id" id="purok_id" required
                                class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('purok_id') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Select</option>
                            @foreach ($puroks ?? [] as $purok)
                                <option value="{{ $purok->id }}" @selected(old('purok_id') == $purok->id)>{{ $purok->name }}</option>
                            @endforeach
                        </select>
                        @error('purok_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="sitio_subdivision" class="block text-sm font-medium text-gray-700 mb-1">Sitio / Subdivision <span class="text-gray-400">(optional)</span></label>
                        <input type="text" name="sitio_subdivision" id="sitio_subdivision" value="{{ old('sitio_subdivision') }}"
                               class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('sitio_subdivision') border-red-500 @else border-gray-300 @enderror">
                        @error('sitio_subdivision')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Section 3: Contact & Demographics --}}
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-2">3</span>
                    Contact & Demographics
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Contact Number with +63 prefix --}}
                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact number <span class="text-red-600">*</span></label>
                        <div class="flex">
                            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-600">+63</span>
                            <input type="tel" name="contact_number" id="contact_number" value="{{ old('contact_number') }}" required
                                   maxlength="10" placeholder="9171234567"
                                   class="block w-full rounded-r-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('contact_number') border-red-500 @else border-gray-300 @enderror">
                        </div>
                        @error('contact_number')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-600">*</span></label>
                        <select name="gender" id="gender" required
                                class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('gender') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Select</option>
                            <option value="male" @selected(old('gender') === 'male')>Male</option>
                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                            <option value="other" @selected(old('gender') === 'other')>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Birthdate (mobile-friendly dropdowns) --}}
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate <span class="text-red-600">*</span></label>
                        <input type="hidden" name="birthdate" id="birthdate" value="{{ old('birthdate') }}" required>
                        <div class="dob-grid">
                            <div>
                                <label for="birth_month" class="sr-only">Birth month</label>
                                <select name="birth_month" id="birth_month" class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('birthdate') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Month</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                            <div>
                                <label for="birth_day" class="sr-only">Birth day</label>
                                <select name="birth_day" id="birth_day" class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('birthdate') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Day</option>
                                </select>
                            </div>
                            <div>
                                <label for="birth_year" class="sr-only">Birth year</label>
                                <input
                                    type="number"
                                    name="birth_year"
                                    id="birth_year"
                                    min="1850"
                                    max="{{ now('Asia/Manila')->format('Y') }}"
                                    step="1"
                                    inputmode="numeric"
                                    placeholder="Year (1850-{{ now('Asia/Manila')->format('Y') }})"
                                    class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('birthdate') border-red-500 @else border-gray-300 @enderror">
                            </div>
                        </div>
                        <p id="age_preview" class="mt-2 text-sm font-medium text-gray-600">Age: —</p>
                        <p id="birthdate_live_error" class="mt-1 hidden text-xs text-red-600"></p>
                        @error('birthdate')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Civil Status --}}
                    <div>
                        <label for="civil_status" class="block text-sm font-medium text-gray-700 mb-1">Civil status <span class="text-red-600">*</span></label>
                        <select name="civil_status" id="civil_status" required
                                class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('civil_status') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Select</option>
                            <option value="single" @selected(old('civil_status') === 'single')>Single</option>
                            <option value="married" @selected(old('civil_status') === 'married')>Married</option>
                            <option value="widowed" @selected(old('civil_status') === 'widowed')>Widowed</option>
                            <option value="divorced" @selected(old('civil_status') === 'divorced')>Divorced</option>
                            <option value="separated" @selected(old('civil_status') === 'separated')>Separated</option>
                        </select>
                        @error('civil_status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Head of Family --}}
                    <div>
                        <label for="head_of_family" class="block text-sm font-medium text-gray-700 mb-1">Head of family <span class="text-red-600">*</span></label>
                        <select name="head_of_family" id="head_of_family" required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus">
                            <option value="">Select</option>
                            <option value="yes" @selected(old('head_of_family') === 'yes')>Yes</option>
                            <option value="no" @selected(old('head_of_family') === 'no')>No</option>
                        </select>
                    </div>

                    {{-- Head of Family conditional fields --}}
                    <div id="head-of-family-fields" class="sm:col-span-2 hidden mt-2 pl-4 border-l-2 border-gray-200">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Head of family name</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="head_first_name" class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-600">*</span></label>
                                <input type="text" name="head_first_name" id="head_first_name" value="{{ old('head_first_name') }}"
                                       class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('head_first_name') border-red-500 @else border-gray-300 @enderror">
                                @error('head_first_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="head_middle_name" class="block text-sm font-medium text-gray-700 mb-1">Middle name</label>
                                <input type="text" name="head_middle_name" id="head_middle_name" value="{{ old('head_middle_name') }}"
                                       class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus">
                            </div>
                            <div>
                                <label for="head_last_name" class="block text-sm font-medium text-gray-700 mb-1">Last name <span class="text-red-600">*</span></label>
                                <input type="text" name="head_last_name" id="head_last_name" value="{{ old('head_last_name') }}"
                                       class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('head_last_name') border-red-500 @else border-gray-300 @enderror">
                                @error('head_last_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="household_connection_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Household connection type <span class="text-red-600">*</span>
                                </label>
                                <select name="household_connection_type" id="household_connection_type"
                                        class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('household_connection_type') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Select</option>
                                    <option value="family_member" @selected(old('household_connection_type') === 'family_member')>Family Member</option>
                                    <option value="boarder" @selected(old('household_connection_type') === 'boarder')>Boarder</option>
                                    <option value="helper" @selected(old('household_connection_type') === 'helper')>Helper</option>
                                    <option value="guardian_dependent" @selected(old('household_connection_type') === 'guardian_dependent')>Guardian/Dependent</option>
                                    <option value="other" @selected(old('household_connection_type') === 'other')>Other</option>
                                </select>
                                @error('household_connection_type')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="relationship-to-head-wrap" class="hidden">
                                <label for="relationship_to_head" class="block text-sm font-medium text-gray-700 mb-1">
                                    Relationship to head <span class="text-red-600">*</span>
                                </label>
                                <select name="relationship_to_head" id="relationship_to_head"
                                        class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('relationship_to_head') border-red-500 @else border-gray-300 @enderror">
                                    <option value="">Select</option>
                                    <option value="son" @selected(old('relationship_to_head') === 'son')>Son</option>
                                    <option value="daughter" @selected(old('relationship_to_head') === 'daughter')>Daughter</option>
                                    <option value="spouse" @selected(old('relationship_to_head') === 'spouse')>Spouse</option>
                                    <option value="father" @selected(old('relationship_to_head') === 'father')>Father</option>
                                    <option value="mother" @selected(old('relationship_to_head') === 'mother')>Mother</option>
                                    <option value="brother" @selected(old('relationship_to_head') === 'brother')>Brother</option>
                                    <option value="sister" @selected(old('relationship_to_head') === 'sister')>Sister</option>
                                    <option value="grandfather" @selected(old('relationship_to_head') === 'grandfather')>Grandfather</option>
                                    <option value="grandmother" @selected(old('relationship_to_head') === 'grandmother')>Grandmother</option>
                                    <option value="uncle" @selected(old('relationship_to_head') === 'uncle')>Uncle</option>
                                    <option value="aunt" @selected(old('relationship_to_head') === 'aunt')>Aunt</option>
                                    <option value="cousin" @selected(old('relationship_to_head') === 'cousin')>Cousin</option>
                                    <option value="nephew" @selected(old('relationship_to_head') === 'nephew')>Nephew</option>
                                    <option value="niece" @selected(old('relationship_to_head') === 'niece')>Niece</option>
                                    <option value="guardian" @selected(old('relationship_to_head') === 'guardian')>Guardian</option>
                                    <option value="boarder" @selected(old('relationship_to_head') === 'boarder')>Boarder</option>
                                    <option value="helper" @selected(old('relationship_to_head') === 'helper')>Helper</option>
                                    <option value="other" @selected(old('relationship_to_head') === 'other')>Other</option>
                                </select>
                                @error('relationship_to_head')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div id="connection-note-wrap" class="hidden mt-4">
                            <label for="connection_note" class="block text-sm font-medium text-gray-700 mb-1">
                                Connection details <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="connection_note" id="connection_note" value="{{ old('connection_note') }}"
                                   placeholder="Provide details for Other connection type"
                                   class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('connection_note') border-red-500 @else border-gray-300 @enderror">
                            @error('connection_note')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Resident Type --}}
                    <div>
                        <label for="resident_type" class="block text-sm font-medium text-gray-700 mb-1">Resident type <span class="text-red-600">*</span></label>
                        <select name="resident_type" id="resident_type" required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus">
                            <option value="">Select</option>
                            <option value="permanent" @selected(old('resident_type') === 'permanent')>Permanent</option>
                            <option value="non-permanent" @selected(old('resident_type') === 'non-permanent')>Non-permanent</option>
                        </select>
                    </div>
                </div>

                {{-- Permanent Address conditional fields --}}
                <div id="permanent-address-fields" class="hidden mt-6 pl-4 border-l-2 border-gray-200">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Permanent Address</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="permanent_house_no" class="block text-sm font-medium text-gray-700 mb-1">House number <span class="text-red-600">*</span></label>
                            <input type="text" name="permanent_house_no" id="permanent_house_no" value="{{ old('permanent_house_no') }}"
                                   class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('permanent_house_no') border-red-500 @else border-gray-300 @enderror">
                            @error('permanent_house_no')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="permanent_street" class="block text-sm font-medium text-gray-700 mb-1">Street <span class="text-red-600">*</span></label>
                            <input type="text" name="permanent_street" id="permanent_street" value="{{ old('permanent_street') }}"
                                   class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('permanent_street') border-red-500 @else border-gray-300 @enderror">
                            @error('permanent_street')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="permanent_region" class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-600">*</span></label>
                            <select name="permanent_region" id="permanent_region"
                                    class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('permanent_region') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Select region</option>
                            </select>
                            @error('permanent_region')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="permanent_barangay" class="block text-sm font-medium text-gray-700 mb-1">Barangay <span class="text-red-600">*</span></label>
                            <select name="permanent_barangay" id="permanent_barangay"
                                    class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('permanent_barangay') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Select barangay</option>
                            </select>
                            @error('permanent_barangay')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="permanent_city" class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-600">*</span></label>
                            <select name="permanent_city" id="permanent_city"
                                    class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('permanent_city') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Select city / municipality</option>
                            </select>
                            @error('permanent_city')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="permanent_province" class="block text-sm font-medium text-gray-700 mb-1">Province <span class="text-red-600">*</span></label>
                            <select name="permanent_province" id="permanent_province"
                                    class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('permanent_province') border-red-500 @else border-gray-300 @enderror">
                                <option value="">Select province</option>
                            </select>
                            @error('permanent_province')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- Section 4: Resident Classification --}}
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-2">4</span>
                    Resident Classification
                </h2>
                <p class="text-sm text-gray-600 mb-4">Please indicate if you belong to any of the following categories. If set to Yes, uploading the matching proof is required.</p>

                <div class="space-y-6">
                    {{-- PWD --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Person With Disability (PWD)</label>
                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_pwd" value="yes" {{ old('is_pwd') === 'yes' ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-[var(--brand-700)] focus:ring-[var(--brand-100)]">
                                <span class="ml-2 text-sm text-gray-700">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_pwd" value="no" {{ old('is_pwd', 'no') === 'no' ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-[var(--brand-700)] focus:ring-[var(--brand-100)]">
                                <span class="ml-2 text-sm text-gray-700">No</span>
                            </label>
                        </div>
                        <div id="pwd-proof-field" class="mt-3 hidden pl-4 border-l-2 border-gray-200">
                            <label for="pwd_proof" class="block text-sm font-medium text-gray-700 mb-1">
                                PWD ID / Supporting Document <span class="text-red-600">*</span>
                            </label>
                            <input type="file" name="pwd_proof" id="pwd_proof" accept=".jpg,.jpeg,.png,.pdf" data-auto-compress="true"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[var(--brand-100)] file:text-[var(--brand-800)] hover:file:bg-emerald-200/70">
                            <p class="mt-1 text-xs text-gray-500">Accepted: JPG, PNG, PDF (max 2MB)</p>
                            @error('pwd_proof')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Senior Citizen --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Senior Citizen</label>
                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_senior" value="yes" {{ old('is_senior') === 'yes' ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-[var(--brand-700)] focus:ring-[var(--brand-100)]">
                                <span class="ml-2 text-sm text-gray-700">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="is_senior" value="no" {{ old('is_senior', 'no') === 'no' ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-[var(--brand-700)] focus:ring-[var(--brand-100)]">
                                <span class="ml-2 text-sm text-gray-700">No</span>
                            </label>
                        </div>
                        <div id="senior-proof-field" class="mt-3 hidden pl-4 border-l-2 border-gray-200">
                            <label for="senior_proof" class="block text-sm font-medium text-gray-700 mb-1">
                                Senior Citizen ID / Supporting Document <span class="text-red-600">*</span>
                            </label>
                            <input type="file" name="senior_proof" id="senior_proof" accept=".jpg,.jpeg,.png,.pdf" data-auto-compress="true"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[var(--brand-100)] file:text-[var(--brand-800)] hover:file:bg-emerald-200/70">
                            <p class="mt-1 text-xs text-gray-500">Accepted: JPG, PNG, PDF (max 2MB)</p>
                            @error('senior_proof')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Government ID (required) --}}
                    <div id="government-id-section" class="rounded-lg border border-[var(--brand-100)] bg-[var(--brand-100)]/70 p-4">
                        <label for="government_id_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Government ID Type <span class="text-red-600">*</span>
                        </label>
                        <select name="government_id_type" id="government_id_type" required
                                class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 shadow-sm transition ui-form-focus @error('government_id_type') border-red-500 @else border-gray-300 @enderror">
                            <option value="">Select ID type</option>
                            <option value="national_id" @selected(old('government_id_type') === 'national_id')>National ID</option>
                            <option value="passport" @selected(old('government_id_type') === 'passport')>Passport</option>
                            <option value="drivers_license" @selected(old('government_id_type') === 'drivers_license')>Driver's License</option>
                            <option value="umid" @selected(old('government_id_type') === 'umid')>UMID</option>
                            <option value="philhealth" @selected(old('government_id_type') === 'philhealth')>PhilHealth ID</option>
                            <option value="postal_id" @selected(old('government_id_type') === 'postal_id')>Postal ID</option>
                            <option value="voters_id" @selected(old('government_id_type') === 'voters_id')>Voter's ID</option>
                        </select>
                        @error('government_id_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <label for="government_id_proof" class="mt-4 block text-sm font-medium text-gray-700 mb-1">
                            Government ID Upload <span class="text-red-600">*</span>
                        </label>
                        <input type="file"
                               name="government_id_proof"
                               id="government_id_proof"
                               required
                               accept=".jpg,.jpeg,.png"
                               capture="environment"
                               data-auto-compress="true"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[var(--brand-100)] file:text-[var(--brand-800)] hover:file:bg-emerald-200/80 @error('government_id_proof') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Capture from camera (mobile) or upload JPG/PNG image. Max file size: 2MB.</p>
                        @error('government_id_proof')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="classification-proof-note" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-xs text-emerald-800">
                        Government ID fields are not required when PWD or Senior Citizen is set to Yes.
                    </div>
                </div>
            </section>

            {{-- Section 5: Account Information --}}
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold mr-2">5</span>
                    Account Information
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email"
                               class="block w-full rounded-lg border px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('email') border-red-500 @else border-gray-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required autocomplete="new-password" minlength="8"
                                   class="block w-full rounded-lg border px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus @error('password') border-red-500 @else border-gray-300 @enderror">
                            <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" aria-label="Show password" aria-pressed="false">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm password <span class="text-red-600">*</span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" minlength="8"
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-10 text-gray-900 placeholder-gray-400 shadow-sm transition ui-form-focus">
                            <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" aria-label="Show confirm password" aria-pressed="false">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Data Privacy Consent --}}
            <section class="pb-6">
                <div class="rounded-lg border border-[var(--brand-100)] bg-[var(--brand-100)]/80 p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="privacy_consent" value="1" {{ old('privacy_consent') ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[var(--brand-700)] focus:ring-[var(--brand-100)]">
                        <span class="text-sm text-gray-700">
                            I agree to the
                            <button type="button"
                                    onclick="openPrivacyModal()"
                                    class="ui-link cursor-pointer border-0 bg-transparent p-0 text-left underline font-medium">
                                Data Privacy Act
                            </button>
                            and consent to the collection and processing of my personal data for barangay services.
                            <span class="text-red-600">*</span>
                        </span>
                    </label>
                    @error('privacy_consent')
                        <p class="mt-2 text-xs text-red-600 pl-7">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('login') }}" class="ui-link order-2 text-sm sm:order-1">
                    Back to Login
                </a>
                <button type="submit"
                        class="ui-btn ui-btn-primary ui-btn-lg order-1 w-full rounded-lg py-3 px-6 shadow-sm sm:order-2 sm:w-auto">
                    Register
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Data Privacy Modal --}}
<div id="privacyModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-gray-900/60" onclick="closePrivacyModal()"></div>
    <div class="relative mx-auto mt-8 w-[92%] max-w-3xl rounded-2xl bg-white shadow-2xl ring-1 ring-gray-200 sm:mt-12">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h3 class="text-base font-semibold text-gray-900">Data Privacy Act Consent</h3>
            <button type="button" onclick="closePrivacyModal()" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="max-h-[65vh] overflow-y-auto px-6 py-4 text-sm leading-relaxed text-gray-600 space-y-3">
            <p>
                In compliance with Republic Act No. 10173 (Data Privacy Act of 2012), Barangay Paguiruan, Floridablanca collects personal information for resident registration, identity verification, and delivery of barangay services.
            </p>
            <p>
                The information you provide may include your full name, address, contact details, birthdate, civil status, and uploaded supporting documents. This information is used solely for legitimate barangay governance, record-keeping, and service transactions.
            </p>
            <p>
                Your data will be accessed only by authorized personnel (admin/staff) and protected through reasonable organizational and technical safeguards. Data will not be disclosed to unauthorized parties, except when required by law or by lawful government request.
            </p>
            <p>
                You may request correction of inaccurate data and may contact the barangay office for privacy-related concerns. By proceeding with registration, you confirm that the information you provide is true and that you voluntarily consent to the processing of your personal data for official barangay purposes.
            </p>
        </div>
        <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-6 py-4">
            <button type="button" onclick="closePrivacyModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Close
            </button>
            <button type="button" onclick="acceptPrivacyAndClose()" class="ui-btn ui-btn-primary rounded-lg px-4 py-2 text-sm">
                I Understand
            </button>
        </div>
    </div>
</div>

<style>
.dob-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
}
@media (max-width: 640px) {
    .dob-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
(function () {
    // === Conditional field toggles ===
    var headOfFamilySelect = document.getElementById('head_of_family');
    var headOfFamilyFields = document.getElementById('head-of-family-fields');
    var headFirstName = document.getElementById('head_first_name');
    var headLastName = document.getElementById('head_last_name');
    var householdConnectionType = document.getElementById('household_connection_type');
    var relationshipWrap = document.getElementById('relationship-to-head-wrap');
    var relationshipSelect = document.getElementById('relationship_to_head');
    var connectionNoteWrap = document.getElementById('connection-note-wrap');
    var connectionNoteInput = document.getElementById('connection_note');

    var residentTypeSelect = document.getElementById('resident_type');
    var permanentAddressFields = document.getElementById('permanent-address-fields');
    var permanentInputs = ['permanent_house_no', 'permanent_street', 'permanent_region', 'permanent_barangay', 'permanent_city', 'permanent_province'].map(function (id) { return document.getElementById(id); });

    var pwdRadios = document.querySelectorAll('input[name="is_pwd"]');
    var pwdProofField = document.getElementById('pwd-proof-field');
    var pwdProofInput = document.getElementById('pwd_proof');

    var seniorRadios = document.querySelectorAll('input[name="is_senior"]');
    var seniorProofField = document.getElementById('senior-proof-field');
    var seniorProofInput = document.getElementById('senior_proof');
    var governmentIdSection = document.getElementById('government-id-section');
    var classificationProofNote = document.getElementById('classification-proof-note');
    var governmentIdType = document.getElementById('government_id_type');
    var governmentIdProof = document.getElementById('government_id_proof');
    var autoCompressInputs = document.querySelectorAll('input[type="file"][data-auto-compress="true"]');

    function dataUrlToFile(dataUrl, originalName) {
        var parts = dataUrl.split(',');
        var mimeMatch = parts[0].match(/:(.*?);/);
        var mime = mimeMatch ? mimeMatch[1] : 'image/jpeg';
        var binary = atob(parts[1]);
        var length = binary.length;
        var bytes = new Uint8Array(length);

        while (length--) {
            bytes[length] = binary.charCodeAt(length);
        }

        var baseName = (originalName || 'upload').replace(/\.[^.]+$/, '');

        return new File([bytes], baseName + '.jpg', {
            type: mime,
            lastModified: Date.now(),
        });
    }

    function setCompressedFile(input, file) {
        var transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
    }

    function compressImageFile(file, options) {
        options = options || {};

        return new Promise(function (resolve, reject) {
            if (!file.type || file.type.indexOf('image/') !== 0) {
                resolve(file);
                return;
            }

            var reader = new FileReader();

            reader.onload = function (event) {
                var img = new Image();

                img.onload = function () {
                    var maxDimension = options.maxDimension || 1600;
                    var quality = options.quality || 0.82;
                    var width = img.width;
                    var height = img.height;

                    if (width > height && width > maxDimension) {
                        height = Math.round(height * (maxDimension / width));
                        width = maxDimension;
                    } else if (height >= width && height > maxDimension) {
                        width = Math.round(width * (maxDimension / height));
                        height = maxDimension;
                    }

                    var canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    var context = canvas.getContext('2d');
                    if (!context) {
                        resolve(file);
                        return;
                    }

                    context.drawImage(img, 0, 0, width, height);

                    resolve(dataUrlToFile(canvas.toDataURL('image/jpeg', quality), file.name));
                };

                img.onerror = function () {
                    reject(new Error('Image load failed.'));
                };

                img.src = event.target.result;
            };

            reader.onerror = function () {
                reject(new Error('File read failed.'));
            };

            reader.readAsDataURL(file);
        });
    }

    function attachAutoCompression() {
        autoCompressInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) return;

                if (file.type === 'application/pdf' || file.size <= (1800 * 1024)) {
                    return;
                }

                compressImageFile(file).then(function (compressedFile) {
                    if (compressedFile.size < file.size) {
                        setCompressedFile(input, compressedFile);
                    }
                }).catch(function () {
                    // Keep the original file if compression is not available.
                });
            });
        });
    }

    function toggleHeadOfFamilyFields() {
        var show = headOfFamilySelect.value === 'no';
        headOfFamilyFields.classList.toggle('hidden', !show);
        headFirstName.required = show;
        headLastName.required = show;
        if (householdConnectionType) {
            householdConnectionType.required = show;
        }
        if (!show) {
            if (householdConnectionType) householdConnectionType.value = '';
            if (relationshipSelect) relationshipSelect.value = '';
            if (connectionNoteInput) connectionNoteInput.value = '';
        }
        toggleHouseholdConnectionFields();
    }

    function toggleHouseholdConnectionFields() {
        var showHeadNo = headOfFamilySelect && headOfFamilySelect.value === 'no';
        var connectionType = householdConnectionType ? householdConnectionType.value : '';
        var showRelationship = showHeadNo && connectionType === 'family_member';
        var showConnectionNote = showHeadNo && connectionType === 'other';

        if (relationshipWrap) {
            relationshipWrap.classList.toggle('hidden', !showRelationship);
        }
        if (relationshipSelect) {
            relationshipSelect.required = showRelationship;
            if (!showRelationship) relationshipSelect.value = '';
        }
        if (connectionNoteWrap) {
            connectionNoteWrap.classList.toggle('hidden', !showConnectionNote);
        }
        if (connectionNoteInput) {
            connectionNoteInput.required = showConnectionNote;
            if (!showConnectionNote) connectionNoteInput.value = '';
        }
    }

    function togglePermanentAddressFields() {
        var show = residentTypeSelect.value === 'non-permanent';
        permanentAddressFields.classList.toggle('hidden', !show);
        permanentInputs.forEach(function (el) { if (el) el.required = show; });
    }

    var permanentAddressFallbackEnabled = false;
    var NCR_REGION_CODE = '130000000';
    var NCR_FALLBACK_CITIES = [
        'City of Manila',
        'Quezon City',
        'Caloocan City',
        'Las Pinas City',
        'Makati City',
        'Malabon City',
        'Mandaluyong City',
        'Marikina City',
        'Muntinlupa City',
        'Navotas City',
        'Paranaque City',
        'Pasay City',
        'Pasig City',
        'San Juan City',
        'Taguig City',
        'Valenzuela City',
        'Pateros',
    ];

    function sanitizeSelectValue(raw) {
        var value = String(raw || '').trim();
        if (!value) return '';
        var lowered = value.toLowerCase();
        if (lowered.indexOf('select ') === 0) return '';
        if (lowered === 'loading...') return '';
        if (lowered === 'manual input required') return '';
        if (lowered === 'enter city manually') return '';
        return value;
    }

    function createSimpleItems(values) {
        return values.map(function (value) {
            return { value: value, name: value, label: value };
        });
    }

    function setSelectLoadingState(selectEl) {
        if (!selectEl || selectEl.tagName !== 'SELECT') return;
        selectEl.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Loading...';
        opt.selected = true;
        selectEl.appendChild(opt);
        selectEl.disabled = true;
    }

    function setSelectManualRequiredState(selectEl) {
        if (!selectEl || selectEl.tagName !== 'SELECT') return;
        selectEl.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Manual input required';
        opt.selected = true;
        selectEl.appendChild(opt);
        selectEl.disabled = true;
    }

    function enableManualPermanentAddressFallback(reason) {
        if (permanentAddressFallbackEnabled) return;
        permanentAddressFallbackEnabled = true;
        console.warn('[Register][Permanent Address] Switched to manual mode:', reason || 'unknown');

        var replacements = [
            { id: 'permanent_region', placeholder: 'Enter region manually' },
            { id: 'permanent_province', placeholder: 'Enter province manually' },
            { id: 'permanent_city', placeholder: 'Enter city manually' },
            { id: 'permanent_barangay', placeholder: 'Enter barangay manually' },
        ];

        replacements.forEach(function (entry) {
            var select = document.getElementById(entry.id);
            if (!select || select.tagName !== 'SELECT') return;

            setSelectManualRequiredState(select);

            var selectedOption = select.options && select.options.length > 0 ? select.options[select.selectedIndex] : null;
            var fromValue = sanitizeSelectValue(select.value);
            var fromText = sanitizeSelectValue(selectedOption ? selectedOption.textContent : '');
            var initialValue = fromValue || fromText;

            var input = document.createElement('input');
            input.type = 'text';
            input.name = select.name;
            input.id = select.id;
            input.value = initialValue;
            input.placeholder = entry.placeholder;
            input.className = select.className;
            input.required = !!select.required;

            select.replaceWith(input);
        });

        permanentInputs = ['permanent_house_no', 'permanent_street', 'permanent_region', 'permanent_barangay', 'permanent_city', 'permanent_province']
            .map(function (id) { return document.getElementById(id); });
        togglePermanentAddressFields();
    }

    function setSelectOptions(selectEl, items, selectedText, placeholder) {
        if (!selectEl || selectEl.tagName !== 'SELECT') return;
        var target = (selectedText || '').trim().toLowerCase();
        selectEl.innerHTML = '';
        var baseOption = document.createElement('option');
        baseOption.value = '';
        baseOption.textContent = placeholder;
        selectEl.appendChild(baseOption);

        items.forEach(function (item) {
            var option = document.createElement('option');
            option.value = item.value || item.name;
            option.textContent = item.label || item.name || item.value;
            if (target !== '' && String(option.value).trim().toLowerCase() === target) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });

        selectEl.disabled = false;
    }

      function getPsgcCacheKey(url) {
        return 'psgc-cache-v1::' + url;
    }

    function readCachedPsgcData(url) {
        try {
            var raw = localStorage.getItem(getPsgcCacheKey(url));
            if (!raw) return null;
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed && parsed.data) ? parsed.data : null;
        } catch (e) {
            return null;
        }
    }

    function writeCachedPsgcData(url, data) {
        try {
            localStorage.setItem(getPsgcCacheKey(url), JSON.stringify({
                cached_at: Date.now(),
                data: data
            }));
        } catch (e) {
            // Ignore quota/privacy mode write failures.
        }
    }

    function fetchJson(url, timeoutMs) {
        var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var timeoutId = null;
        if (controller) {
            timeoutId = window.setTimeout(function () {
                controller.abort();
            }, timeoutMs || 10000);
        }

        return fetch(url, {
            cache: 'no-store',
            signal: controller ? controller.signal : undefined,
        })
            .then(function (response) {
                if (timeoutId) window.clearTimeout(timeoutId);
                if (!response.ok) throw new Error('Failed to load address data.');
                return response.json();
            })
            .then(function (data) {
                writeCachedPsgcData(url, data);
                return data;
            })
            .catch(function (error) {
                if (timeoutId) window.clearTimeout(timeoutId);
                var cached = readCachedPsgcData(url);
                if (cached) return cached;
                throw error;
            });
    }

    function loadRegions() {
        var regionSelect = document.getElementById('permanent_region');
        var oldRegion = @json(old('permanent_region'));

        setSelectLoadingState(regionSelect);

        return fetchJson('https://psgc.gitlab.io/api/regions/', 12000).then(function (regions) {
            regions.sort(function (a, b) { return a.name.localeCompare(b.name); });
            var mapped = regions.map(function (region) {
                return { name: region.name, value: region.name, label: region.name, code: region.code };
            });
            setSelectOptions(regionSelect, mapped, oldRegion, 'Select region');
            return regions;
        });
    }

    function loadProvincesForRegion(region, selectedProvince) {
        var provinceSelect = document.getElementById('permanent_province');
        var citySelect = document.getElementById('permanent_city');
        var barangaySelect = document.getElementById('permanent_barangay');

        setSelectLoadingState(provinceSelect);
        setSelectOptions(citySelect, [], '', 'Select city / municipality');
        setSelectOptions(barangaySelect, [], '', 'Select barangay');

        if (!region || !region.code) {
            setSelectOptions(provinceSelect, [], '', 'Select province');
            return Promise.resolve([]);
        }

        if (region.code === NCR_REGION_CODE) {
            var ncrProvince = [{
                code: region.code,
                name: 'NCR',
                value: 'NCR',
                label: 'NCR',
                source: 'region',
            }];
            setSelectOptions(provinceSelect, ncrProvince, selectedProvince || 'NCR', 'Select province');
            provinceSelect.value = 'NCR';
            provinceSelect.disabled = false;
            return Promise.resolve(ncrProvince);
        }

        provinceSelect.disabled = false;

        return fetchJson('https://psgc.gitlab.io/api/regions/' + region.code + '/provinces/', 12000).then(function (provinces) {
            provinces.sort(function (a, b) { return a.name.localeCompare(b.name); });
            if (provinces.length > 0) {
                var mapped = provinces.map(function (province) {
                    return { name: province.name, value: province.name, label: province.name, code: province.code };
                });
                setSelectOptions(provinceSelect, mapped, selectedProvince, 'Select province');
                return mapped;
            }

            throw new Error('No province data available.');
        });
    }

    function loadCitiesForProvince(province, selectedCity) {
        var citySelect = document.getElementById('permanent_city');
        var barangaySelect = document.getElementById('permanent_barangay');
        setSelectLoadingState(citySelect);
        setSelectOptions(barangaySelect, [], '', 'Select barangay');

        if (!province || !province.code) {
            setSelectOptions(citySelect, [], '', 'Select city / municipality');
            return Promise.resolve([]);
        }

        var endpoint = province.source === 'region'
            ? 'https://psgc.gitlab.io/api/regions/' + province.code + '/cities-municipalities/'
            : 'https://psgc.gitlab.io/api/provinces/' + province.code + '/cities-municipalities/';

        return fetchJson(endpoint, 12000).then(function (cities) {
            cities.sort(function (a, b) { return a.name.localeCompare(b.name); });

            if (!cities.length) {
                if (province.source === 'region') {
                    var ncrFallback = createSimpleItems(NCR_FALLBACK_CITIES);
                    setSelectOptions(citySelect, ncrFallback, selectedCity, 'Select city / municipality');
                    return ncrFallback;
                }

                citySelect.innerHTML = '<option value="">Enter city manually</option>';
                throw new Error('City list is empty.');
            }

            var mapped = cities.map(function (city) {
                return { name: city.name, value: city.name, label: city.name, code: city.code };
            });
            setSelectOptions(citySelect, mapped, selectedCity, 'Select city / municipality');
            return mapped;
        }).catch(function (error) {
            if (province.source === 'region') {
                var ncrFallback = createSimpleItems(NCR_FALLBACK_CITIES);
                setSelectOptions(citySelect, ncrFallback, selectedCity, 'Select city / municipality');
                return ncrFallback;
            }
            throw error;
        });
    }

    function loadBarangays(cityCode, selectedBarangay) {
        var barangaySelect = document.getElementById('permanent_barangay');
        setSelectLoadingState(barangaySelect);

        if (!cityCode) {
            setSelectOptions(barangaySelect, [], '', 'Select barangay');
            return Promise.resolve();
        }

        return fetchJson('https://psgc.gitlab.io/api/cities-municipalities/' + cityCode + '/barangays/', 12000).then(function (barangays) {
            barangays.sort(function (a, b) { return a.name.localeCompare(b.name); });

            if (!barangays.length) {
                throw new Error('Barangay list is empty.');
            }

            var mapped = barangays.map(function (barangay) {
                return { name: barangay.name, value: barangay.name, label: barangay.name, code: barangay.code };
            });
            setSelectOptions(barangaySelect, mapped, selectedBarangay, 'Select barangay');
        });
    }

    function initPermanentAddressDropdowns() {
        var regionSelect = document.getElementById('permanent_region');
        var provinceSelect = document.getElementById('permanent_province');
        var citySelect = document.getElementById('permanent_city');
        var barangaySelect = document.getElementById('permanent_barangay');
        var oldRegion = @json(old('permanent_region'));
        var oldProvince = @json(old('permanent_province'));
        var oldCity = @json(old('permanent_city'));
        var oldBarangay = @json(old('permanent_barangay'));
        var regionsCache = [];
        var provincesCache = [];
        var citiesCache = [];

        if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
            return Promise.resolve();
        }

        return loadRegions()
            .then(function (regions) {
                regionsCache = regions;
                var selectedRegion = regions.find(function (item) {
                    return item.name.toLowerCase() === String(oldRegion || '').toLowerCase();
                });

                return loadProvincesForRegion(selectedRegion || null, oldProvince).then(function (provinces) {
                    return {
                        selectedRegion: selectedRegion || null,
                        provinces: provinces,
                    };
                });
            })
            .then(function (payload) {
                provincesCache = payload.provinces || [];
                var selectedProvince = provincesCache.find(function (item) {
                    return item.name.toLowerCase() === String(oldProvince || '').toLowerCase();
                }) || provincesCache[0] || null;

                return loadCitiesForProvince(selectedProvince, oldCity);
            })
            .then(function (cities) {
                citiesCache = cities;
                var selectedCity = citiesCache.find(function (item) {
                    return item.name.toLowerCase() === String(oldCity || '').toLowerCase();
                });

                if (!selectedCity || !selectedCity.code) {
                    return Promise.resolve();
                }

                return loadBarangays(selectedCity ? selectedCity.code : null, oldBarangay);
            })
            .then(function () {
                if (regionSelect) {
                    regionSelect.addEventListener('change', function () {
                        if (permanentAddressFallbackEnabled) return;
                        var selectedRegion = regionsCache.find(function (item) { return item.name === regionSelect.value; });
                        loadProvincesForRegion(selectedRegion || null, '').then(function (provinces) {
                            provincesCache = provinces;
                            citiesCache = [];

                            if (selectedRegion && selectedRegion.code === NCR_REGION_CODE) {
                                var ncrProvince = provinces[0] || null;
                                return loadCitiesForProvince(ncrProvince, '').then(function (ncrCities) {
                                    citiesCache = ncrCities;
                                    setSelectOptions(document.getElementById('permanent_barangay'), [], '', 'Select barangay');
                                });
                            }

                            setSelectOptions(citySelect, [], '', 'Select city / municipality');
                            setSelectOptions(barangaySelect, [], '', 'Select barangay');
                            return Promise.resolve();
                        }).catch(function (error) {
                            enableManualPermanentAddressFallback(error && error.message ? error.message : error);
                        });
                    });
                }

                if (provinceSelect) {
                    provinceSelect.addEventListener('change', function () {
                        if (permanentAddressFallbackEnabled || provinceSelect.disabled) return;
                        var selectedProvince = provincesCache.find(function (item) { return item.name === provinceSelect.value; }) || null;
                        loadCitiesForProvince(selectedProvince, '').then(function (cities) {
                            citiesCache = cities;
                            setSelectOptions(barangaySelect, [], '', 'Select barangay');
                        }).catch(function (error) {
                            enableManualPermanentAddressFallback(error && error.message ? error.message : error);
                        });
                    });
                }

                if (citySelect) {
                    citySelect.addEventListener('change', function () {
                        if (permanentAddressFallbackEnabled) return;
                        var selectedCity = citiesCache.find(function (item) { return item.name === citySelect.value; }) || null;
                        if (!selectedCity) {
                            loadBarangays(null, '');
                            return;
                        }

                        if (!selectedCity.code) {
                            enableManualPermanentAddressFallback('City selected from fallback list without PSGC code.');
                            return;
                        }

                        loadBarangays(selectedCity.code, '').catch(function (error) {
                            enableManualPermanentAddressFallback(error && error.message ? error.message : error);
                        });
                    });
                }
            })
            .catch(function (error) {
                enableManualPermanentAddressFallback(error && error.message ? error.message : error);
                throw error;
            });
    }

    function togglePwdProofField() {
        var checkedRadio = document.querySelector('input[name="is_pwd"]:checked');
        var show = checkedRadio && checkedRadio.value === 'yes';
        pwdProofField.classList.toggle('hidden', !show);
        if (pwdProofInput) pwdProofInput.required = show;
    }

    function toggleSeniorProofField() {
        var checkedRadio = document.querySelector('input[name="is_senior"]:checked');
        var show = checkedRadio && checkedRadio.value === 'yes';
        seniorProofField.classList.toggle('hidden', !show);
        if (seniorProofInput) seniorProofInput.required = show;
    }

    function toggleGovernmentIdFields() {
        var checkedPwd = document.querySelector('input[name="is_pwd"]:checked');
        var checkedSenior = document.querySelector('input[name="is_senior"]:checked');
        var isPwdYes = checkedPwd && checkedPwd.value === 'yes';
        var isSeniorYes = checkedSenior && checkedSenior.value === 'yes';
        var shouldRequireGovernmentId = !isPwdYes && !isSeniorYes;

        if (governmentIdSection) {
            governmentIdSection.classList.toggle('hidden', !shouldRequireGovernmentId);
        }
        if (classificationProofNote) {
            classificationProofNote.classList.toggle('hidden', shouldRequireGovernmentId);
        }
        if (governmentIdType) {
            governmentIdType.required = shouldRequireGovernmentId;
            if (!shouldRequireGovernmentId) {
                governmentIdType.value = '';
            }
        }
        if (governmentIdProof) {
            governmentIdProof.required = shouldRequireGovernmentId;
            if (!shouldRequireGovernmentId) {
                governmentIdProof.value = '';
            }
        }
    }

    if (headOfFamilySelect) { headOfFamilySelect.addEventListener('change', toggleHeadOfFamilyFields); toggleHeadOfFamilyFields(); }
    if (householdConnectionType) { householdConnectionType.addEventListener('change', toggleHouseholdConnectionFields); toggleHouseholdConnectionFields(); }
    if (residentTypeSelect) { residentTypeSelect.addEventListener('change', togglePermanentAddressFields); togglePermanentAddressFields(); }
    initPermanentAddressDropdowns().catch(function () {
        // Keep registration usable if PSGC endpoint is unavailable.
        enableManualPermanentAddressFallback('Permanent address init failed.');
        togglePermanentAddressFields();
    });
    pwdRadios.forEach(function (radio) {
        radio.addEventListener('change', togglePwdProofField);
        radio.addEventListener('change', toggleGovernmentIdFields);
    });
    togglePwdProofField();
    seniorRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleSeniorProofField);
        radio.addEventListener('change', toggleGovernmentIdFields);
    });
    toggleSeniorProofField();
    toggleGovernmentIdFields();
    attachAutoCompression();
    // === Birthdate dropdown UX (mobile-friendly) ===
    var birthdateInput = document.getElementById('birthdate');
    var birthMonthSelect = document.getElementById('birth_month');
    var birthDaySelect = document.getElementById('birth_day');
    var birthYearSelect = document.getElementById('birth_year');
    var agePreview = document.getElementById('age_preview');
    var birthdateLiveError = document.getElementById('birthdate_live_error');

    function getAgeFromParts(year, month, day) {
        var today = new Date();
        var birth = new Date(year, month - 1, day);
        var age = today.getFullYear() - birth.getFullYear();
        var m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) { age--; }
        return age;
    }

    function getDaysInMonth(year, month) {
        return new Date(year, month, 0).getDate();
    }

    function setBirthdateLiveError(message) {
        if (!birthdateLiveError) return;
        birthdateLiveError.textContent = message || '';
        birthdateLiveError.classList.toggle('hidden', !message);
    }

    function updateDayOptions(selectedDay) {
        if (!birthDaySelect || !birthMonthSelect || !birthYearSelect) return;
        var month = parseInt(birthMonthSelect.value, 10);
        var year = parseInt(birthYearSelect.value, 10);
        var maxDays = (month && year) ? getDaysInMonth(year, month) : 31;

        birthDaySelect.innerHTML = '<option value="">Day</option>';
        for (var day = 1; day <= maxDays; day++) {
            var option = document.createElement('option');
            option.value = String(day);
            option.textContent = String(day);
            if (String(day) === String(selectedDay || '')) {
                option.selected = true;
            }
            birthDaySelect.appendChild(option);
        }
    }

    function syncBirthdateInput() {
        if (!birthdateInput || !birthMonthSelect || !birthDaySelect || !birthYearSelect) return;
        var month = parseInt(birthMonthSelect.value, 10);
        var day = parseInt(birthDaySelect.value, 10);
        var year = parseInt(birthYearSelect.value, 10);
        var currentYear = new Date().getFullYear();

        if (!month || !day || !year) {
            birthdateInput.value = '';
            if (agePreview) agePreview.textContent = 'Age: —';
            setBirthdateLiveError('');
            return;
        }

        if (year < 1850 || year > currentYear) {
            birthdateInput.value = '';
            if (agePreview) agePreview.textContent = 'Age: —';
            setBirthdateLiveError('Year must be between 1850 and ' + currentYear + '.');
            return;
        }

        var birth = new Date(year, month - 1, day);
        if (birth.getFullYear() !== year || birth.getMonth() !== (month - 1) || birth.getDate() !== day) {
            birthdateInput.value = '';
            if (agePreview) agePreview.textContent = 'Age: —';
            setBirthdateLiveError('Please select a valid birthdate.');
            return;
        }

        var today = new Date();
        today.setHours(0, 0, 0, 0);
        if (birth >= today) {
            birthdateInput.value = '';
            if (agePreview) agePreview.textContent = 'Age: —';
            setBirthdateLiveError('Birthdate cannot be today or in the future.');
            return;
        }

        var age = getAgeFromParts(year, month, day);
        var monthPadded = String(month).padStart(2, '0');
        var dayPadded = String(day).padStart(2, '0');
        birthdateInput.value = year + '-' + monthPadded + '-' + dayPadded;
        if (agePreview) agePreview.textContent = 'Age: ' + age + ' years old';

        if (age < 18) {
            setBirthdateLiveError('You must be at least 18 years old to register.');
            return;
        }

        setBirthdateLiveError('');
    }

    function initializeBirthdateDropdowns() {
        if (!birthdateInput || !birthMonthSelect || !birthDaySelect || !birthYearSelect) return;
        var oldValue = String(birthdateInput.value || '');
        var match = oldValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (match) {
            var oldYear = String(parseInt(match[1], 10));
            var oldMonth = String(parseInt(match[2], 10));
            var oldDay = String(parseInt(match[3], 10));

            birthYearSelect.value = oldYear;
            birthMonthSelect.value = oldMonth;
            updateDayOptions(oldDay);
            birthDaySelect.value = oldDay;
        } else {
            updateDayOptions('');
        }

        birthMonthSelect.addEventListener('change', function () {
            updateDayOptions(birthDaySelect.value);
            syncBirthdateInput();
        });
        birthYearSelect.addEventListener('change', function () {
            updateDayOptions(birthDaySelect.value);
            syncBirthdateInput();
        });
        birthYearSelect.addEventListener('input', function () {
            updateDayOptions(birthDaySelect.value);
            syncBirthdateInput();
        });
        birthDaySelect.addEventListener('change', syncBirthdateInput);

        syncBirthdateInput();
    }
    initializeBirthdateDropdowns();

    // === Contact number: digits only ===
    var contactInput = document.getElementById('contact_number');
    if (contactInput) {
        contactInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
        });
    }

    // === Real-time blur validation ===
    function validateField(input, rules) {
        var value = input.value.trim();
        for (var i = 0; i < rules.length; i++) {
            var rule = rules[i];
            if (rule.type === 'required' && !value) return rule.msg;
            if (rule.type === 'letters' && value && !/^[A-Za-zÀ-ÿ\s\-']+$/.test(value)) return rule.msg;
            if (rule.type === 'min' && value && value.length < rule.len) return rule.msg;
            if (rule.type === 'regex' && value && !rule.pattern.test(value)) return rule.msg;
            if (rule.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return rule.msg;
        }
        return null;
    }

    function showFieldError(input, msg) {
        clearFieldError(input);
        input.classList.remove('border-gray-300');
        input.classList.add('border-red-500');
        var p = document.createElement('p');
        p.className = 'mt-1 text-xs text-red-600 js-field-error';
        p.textContent = msg;
        var container = input.closest('.flex') || input;
        container.parentNode.insertBefore(p, container.nextSibling);
    }

    function clearFieldError(input) {
        input.classList.remove('border-red-500');
        input.classList.add('border-gray-300');
        var container = input.closest('.flex') || input;
        var existing = container.parentNode.querySelector('.js-field-error');
        if (existing) existing.remove();
    }

    var blurFields = [
        { id: 'first_name', rules: [
            { type: 'required', msg: 'First name is required.' },
            { type: 'letters',  msg: 'First name must contain letters only.' },
            { type: 'min', len: 2, msg: 'First name must be at least 2 characters.' }
        ]},
        { id: 'last_name', rules: [
            { type: 'required', msg: 'Last name is required.' },
            { type: 'letters',  msg: 'Last name must contain letters only.' },
            { type: 'min', len: 2, msg: 'Last name must be at least 2 characters.' }
        ]},
        { id: 'house_no',       rules: [{ type: 'required', msg: 'House number is required.' }] },
        { id: 'contact_number', rules: [
            { type: 'required', msg: 'Contact number is required.' },
            { type: 'regex', pattern: /^9[0-9]{9}$/, msg: 'Enter valid Philippine mobile number.' }
        ]},
        { id: 'gender',         rules: [{ type: 'required', msg: 'Please select a gender.' }] },
        { id: 'civil_status',   rules: [{ type: 'required', msg: 'Please select a civil status.' }] },
        { id: 'email',          rules: [
            { type: 'required', msg: 'Email address is required.' },
            { type: 'email', msg: 'Please enter a valid email address.' }
        ]},
        { id: 'password',       rules: [
            { type: 'required', msg: 'Password is required.' },
            { type: 'min', len: 8, msg: 'Password must be at least 8 characters.' }
        ]},
    ];

    blurFields.forEach(function (cfg) {
        var el = document.getElementById(cfg.id);
        if (!el) return;
        var event = (el.tagName === 'SELECT') ? 'change' : 'blur';
        el.addEventListener(event, function () {
            var err = validateField(el, cfg.rules);
            err ? showFieldError(el, err) : clearFieldError(el);
        });
    });
})();

function openPrivacyModal() {
    var modal = document.getElementById('privacyModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');
}

function closePrivacyModal() {
    var modal = document.getElementById('privacyModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
}

function acceptPrivacyAndClose() {
    var consent = document.querySelector('input[name="privacy_consent"]');
    if (consent) consent.checked = true;
    closePrivacyModal();
}

// === Show/hide password toggle ===
function togglePassword(fieldId, btn) {
    var input = document.getElementById(fieldId);
    var showLabel = fieldId === 'password_confirmation' ? 'Show confirm password' : 'Show password';
    var hideLabel = fieldId === 'password_confirmation' ? 'Hide confirm password' : 'Hide password';
    if (input.type === 'password') {
        input.type = 'text';
        btn.setAttribute('aria-label', hideLabel);
        btn.setAttribute('aria-pressed', 'true');
        btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>';
    } else {
        input.type = 'password';
        btn.setAttribute('aria-label', showLabel);
        btn.setAttribute('aria-pressed', 'false');
        btn.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
    }
}
</script>
@endsection
