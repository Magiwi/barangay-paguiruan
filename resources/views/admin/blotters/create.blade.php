@extends($layout ?? 'layouts.admin')

@section('title', 'Upload Blotter Entry - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">

        {{-- Back link --}}
        <a href="{{ route($rp . '.blotters.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors mb-6">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back to e-Blotter Records
        </a>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <p class="text-sm font-medium text-red-800">Please correct the following errors:</p>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1 ml-7">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Card --}}
        <form method="POST" action="{{ route($rp . '.blotters.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            @csrf

            {{-- Card Header --}}
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-800">Submit Blotter</h1>
                        <p class="mt-1 text-sm text-gray-500">Provide the incident details and parties involved.</p>
                    </div>
                    <button type="button" onclick="window.location='{{ route($rp . '.blotters.index') }}'" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition" aria-label="Close">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-8">

                {{-- Section 1: Complainant Information --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <span class="h-4 w-1 rounded-full bg-blue-500"></span>
                        <h3 class="text-sm font-semibold text-blue-600 uppercase tracking-wide">Complainant Information (Nagreklamo)</h3>
                    </div>

                    {{-- Complainant name with autocomplete --}}
                    <div class="relative" id="autocomplete-wrapper">
                        <label for="complainant_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Complainant Name <span class="text-gray-400 font-normal">(optional if filling First/Last Name below)</span>
                        </label>
                        <input type="text" name="complainant_name" id="complainant_name"
                               value="{{ old('complainant_name') }}"
                               autocomplete="off"
                               placeholder="Start typing a resident's name..."
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('complainant_name') border-red-400 ring-red-100 @enderror">
                        <input type="hidden" name="complainant_user_id" id="complainant_user_id">
                        <div id="suggestions" class="absolute left-0 right-0 z-50 mt-1 max-h-60 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-lg hidden"></div>
                        <p class="mt-1.5 text-xs text-gray-500">Use this for registered residents; otherwise fill out First Name and Last Name below.</p>
                        @error('complainant_name')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="complainant_first_name" class="block text-sm font-medium text-gray-700 mb-1.5">First Name <span class="text-gray-400 font-normal">(required if no Complainant Name above)</span></label>
                            <input type="text" name="complainant_first_name" id="complainant_first_name" value="{{ old('complainant_first_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="John">
                        </div>
                        <div>
                            <label for="complainant_middle_name" class="block text-sm font-medium text-gray-700 mb-1.5">Middle Name</label>
                            <input type="text" name="complainant_middle_name" id="complainant_middle_name" value="{{ old('complainant_middle_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Santos">
                        </div>
                        <div>
                            <label for="complainant_last_name" class="block text-sm font-medium text-gray-700 mb-1.5">Last Name <span class="text-gray-400 font-normal">(required if no Complainant Name above)</span></label>
                            <input type="text" name="complainant_last_name" id="complainant_last_name" value="{{ old('complainant_last_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Doe">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                        <div class="md:col-span-2">
                            <label for="complainant_age" class="block text-sm font-medium text-gray-700 mb-1.5">Age</label>
                            <input type="number" name="complainant_age" id="complainant_age" value="{{ old('complainant_age') }}" min="0" max="130" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="--">
                        </div>
                        <div class="md:col-span-5">
                            <label for="complainant_contact" class="block text-sm font-medium text-gray-700 mb-1.5">Contact Number</label>
                            <input type="text" name="complainant_contact" id="complainant_contact" value="{{ old('complainant_contact') }}" maxlength="11" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="0917-000-0000">
                        </div>
                        <div class="md:col-span-5">
                            <label for="complainant_address" class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                            <input type="text" name="complainant_address" id="complainant_address" value="{{ old('complainant_address') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Street / Brgy Address">
                        </div>
                    </div>
                </div>

                {{-- Section 2: Respondent Details --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <span class="h-4 w-1 rounded-full bg-rose-500"></span>
                        <h3 class="text-sm font-semibold text-rose-500 uppercase tracking-wide">Respondent Details (Nireklamo)</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="respondent_first_name" class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
                            <input type="text" name="respondent_first_name" id="respondent_first_name" value="{{ old('respondent_first_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Respondent's name">
                        </div>
                        <div>
                            <label for="respondent_middle_name" class="block text-sm font-medium text-gray-700 mb-1.5">Middle Name</label>
                            <input type="text" name="respondent_middle_name" id="respondent_middle_name" value="{{ old('respondent_middle_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="...">
                        </div>
                        <div>
                            <label for="respondent_last_name" class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
                            <input type="text" name="respondent_last_name" id="respondent_last_name" value="{{ old('respondent_last_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="...">
                        </div>
                    </div>

                    <div>
                        <label for="respondent_residence" class="block text-sm font-medium text-gray-700 mb-1.5">Last Known Residence</label>
                        <input type="text" name="respondent_residence" id="respondent_residence" value="{{ old('respondent_residence') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Neighborhood or specific location">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 p-5 space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                            <span class="h-4 w-1 rounded-full bg-blue-500"></span>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Witness</h3>
                        </div>
                        <div>
                            <label for="witness_name" class="block text-sm font-medium text-gray-700 mb-1.5">Witness Name</label>
                            <input type="text" name="witness_name" id="witness_name" value="{{ old('witness_name') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Full Name">
                        </div>
                        <div>
                            <label for="witness_contact" class="block text-sm font-medium text-gray-700 mb-1.5">Witness Contact Number</label>
                            <input type="text" name="witness_contact" id="witness_contact" value="{{ old('witness_contact') }}" maxlength="11" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Phone Number">
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-5 space-y-4">
                        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                            <span class="h-4 w-1 rounded-full bg-blue-500"></span>
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Procedure</h3>
                        </div>
                        <div>
                            <label for="scheduled_hearing_date" class="block text-sm font-medium text-gray-700 mb-1.5">Scheduled Hearing Date</label>
                            <input type="date" name="scheduled_hearing_date" id="scheduled_hearing_date" value="{{ old('scheduled_hearing_date') }}" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                        </div>
                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-1.5">Attach Evidence/Proof <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="file" name="file" id="file" accept=".pdf,image/*" capture="environment" class="block w-full rounded-lg border border-gray-300 text-sm text-gray-600 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 transition @error('file') border-red-400 @enderror">
                            <p class="mt-1.5 text-xs text-gray-500">JPG, JPEG, PNG, WEBP, or PDF (max 10MB)</p>
                            @error('file')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div id="evidence_preview_wrap" class="mt-3 hidden rounded-lg border border-blue-200 bg-blue-50 p-3">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-blue-700">Evidence Preview</p>
                                <img id="evidence_preview_img" src="" alt="Evidence preview" class="hidden max-h-56 rounded-lg border border-blue-200 bg-white object-contain">
                                <p id="evidence_preview_meta" class="text-xs text-blue-800"></p>
                            </div>
                        </div>
                        <div>
                            <label for="video" class="block text-sm font-medium text-gray-700 mb-1.5">Attach video evidence <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="file" name="video" id="video" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov" class="block w-full rounded-lg border border-gray-300 text-sm text-gray-600 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-violet-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-violet-700 hover:file:bg-violet-100 transition @error('video') border-red-400 @enderror">
                            <p class="mt-1.5 text-xs text-gray-500">MP4, WEBM, or MOV (max 50MB). Separate from the image/PDF proof above.</p>
                            @error('video')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section 4: Incident Details --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <span class="h-4 w-1 rounded-full bg-blue-500"></span>
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Incident Details</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="incident_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Incident Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="incident_date" id="incident_date"
                                   value="{{ old('incident_date') }}"
                                   required
                                   max="{{ date('Y-m-d') }}"
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('incident_date') border-red-400 ring-red-100 @enderror">
                            @error('incident_date')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="handwritten_salaysay" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Sinumpaang Salaysay <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="file"
                                name="handwritten_salaysay"
                                id="handwritten_salaysay"
                                required
                                accept="image/*"
                                capture="environment"
                                class="block w-full rounded-lg border border-gray-300 text-sm text-gray-600 shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100 transition @error('handwritten_salaysay') border-red-400 @enderror">
                            <p class="mt-1.5 text-xs text-gray-500">Capture from camera or upload image (JPG, JPEG, PNG, WEBP up to 10MB).</p>
                            @error('handwritten_salaysay')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div id="handwritten_preview_wrap" class="mt-3 hidden rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-emerald-700">Sinumpaang Salaysay Preview</p>
                                <img id="handwritten_preview_img" src="" alt="Handwritten salaysay preview" class="max-h-56 rounded-lg border border-emerald-200 bg-white object-contain">
                                <p id="handwritten_preview_meta" class="mt-2 text-xs text-emerald-800"></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Remarks <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea name="remarks" id="remarks" rows="3"
                                  placeholder="Any additional notes about this blotter entry..."
                                  class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('remarks') border-red-400 ring-red-100 @enderror">{{ old('remarks') }}</textarea>
                        @error('remarks')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                <a href="{{ route($rp . '.blotters.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg rounded-lg shadow-sm">
                    Submit Blotter
                </button>
            </div>
        </form>
    </div>
</section>

<script>
(function () {
    const input = document.getElementById('complainant_name');
    const hiddenId = document.getElementById('complainant_user_id');
    const box = document.getElementById('suggestions');
    const complainantFirstName = document.getElementById('complainant_first_name');
    const complainantMiddleName = document.getElementById('complainant_middle_name');
    const complainantLastName = document.getElementById('complainant_last_name');
    const complainantAge = document.getElementById('complainant_age');
    const complainantContact = document.getElementById('complainant_contact');
    const complainantAddress = document.getElementById('complainant_address');
    let debounceTimer = null;
    let activeIndex = -1;

    function hide() {
        box.classList.add('hidden');
        box.innerHTML = '';
        activeIndex = -1;
    }

    function highlight(text, query) {
        if (!query) return text;
        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<mark class="bg-blue-100 text-blue-800 rounded px-0.5">$1</mark>');
    }

    function render(items, query) {
        if (!items.length) { hide(); return; }
        activeIndex = -1;
        function esc(value) {
            return String(value ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
        box.innerHTML = items.map(function (r, i) {
            return '<div class="suggestion-item flex items-center justify-between gap-3 px-4 py-2.5 cursor-pointer text-sm transition-colors hover:bg-gray-50"'
                 + ' data-index="' + i + '"'
                 + ' data-name="' + esc(r.full_name) + '"'
                 + ' data-id="' + esc(r.id) + '"'
                 + ' data-first-name="' + esc(r.first_name) + '"'
                 + ' data-middle-name="' + esc(r.middle_name) + '"'
                 + ' data-last-name="' + esc(r.last_name) + '"'
                 + ' data-age="' + esc(r.age) + '"'
                 + ' data-contact-number="' + esc(r.contact_number) + '"'
                 + ' data-address="' + esc(r.address) + '">'
                 + '<div class="min-w-0"><p class="font-medium text-gray-900 truncate">' + highlight(r.full_name, query) + '</p></div>'
                 + '<span class="shrink-0 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">' + r.purok_name + '</span>'
                 + '</div>';
        }).join('');
        box.classList.remove('hidden');
    }

    function select(selected) {
        input.value = selected.name || '';
        hiddenId.value = selected.id || '';

        if (complainantFirstName) complainantFirstName.value = selected.firstName || '';
        if (complainantMiddleName) complainantMiddleName.value = selected.middleName || '';
        if (complainantLastName) complainantLastName.value = selected.lastName || '';
        if (complainantAge) complainantAge.value = selected.age || '';
        if (complainantContact) complainantContact.value = String(selected.contactNumber || '').replace(/[^0-9]/g, '').slice(0, 11);
        if (complainantAddress) complainantAddress.value = selected.address || '';

        hide();
    }

    input.addEventListener('input', function () {
        hiddenId.value = '';
        var q = this.value.trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) { hide(); return; }
        debounceTimer = setTimeout(function () {
            fetch('/api/residents/search?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) { render(data, q); })
            .catch(function () { hide(); });
        }, 250);
    });

    box.addEventListener('mousedown', function (e) {
        var item = e.target.closest('.suggestion-item');
        if (item) {
            e.preventDefault();
            select({
                id: item.dataset.id,
                name: item.dataset.name,
                firstName: item.dataset.firstName,
                middleName: item.dataset.middleName,
                lastName: item.dataset.lastName,
                age: item.dataset.age,
                contactNumber: item.dataset.contactNumber,
                address: item.dataset.address
            });
        }
    });

    input.addEventListener('keydown', function (e) {
        var items = box.querySelectorAll('.suggestion-item');
        if (!items.length || box.classList.contains('hidden')) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); activeIndex = (activeIndex + 1) % items.length; }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIndex = (activeIndex - 1 + items.length) % items.length; }
        else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            var active = items[activeIndex];
            select({
                id: active.dataset.id,
                name: active.dataset.name,
                firstName: active.dataset.firstName,
                middleName: active.dataset.middleName,
                lastName: active.dataset.lastName,
                age: active.dataset.age,
                contactNumber: active.dataset.contactNumber,
                address: active.dataset.address
            });
            return;
        }
        else if (e.key === 'Escape') { hide(); return; }
        else { return; }
        items.forEach(function (el, i) { el.classList.toggle('bg-blue-50', i === activeIndex); el.classList.toggle('bg-white', i !== activeIndex); });
        items[activeIndex].scrollIntoView({ block: 'nearest' });
    });

    input.addEventListener('blur', function () { setTimeout(hide, 150); });

    ['complainant_contact', 'witness_contact'].forEach(function (id) {
        var field = document.getElementById(id);
        if (!field) return;
        var sanitize = function () {
            field.value = field.value.replace(/[^0-9]/g, '').slice(0, 11);
        };
        field.addEventListener('input', sanitize);
        field.addEventListener('paste', function () { setTimeout(sanitize, 0); });
    });

    function formatFileSize(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var idx = Math.floor(Math.log(bytes) / Math.log(1024));
        var value = bytes / Math.pow(1024, idx);
        return value.toFixed(idx === 0 ? 0 : 1) + ' ' + units[idx];
    }

    function bindFilePreview(inputId, wrapId, imgId, metaId) {
        var inputEl = document.getElementById(inputId);
        var wrapEl = document.getElementById(wrapId);
        var imgEl = document.getElementById(imgId);
        var metaEl = document.getElementById(metaId);
        if (!inputEl || !wrapEl || !imgEl || !metaEl) return;

        inputEl.addEventListener('change', function () {
            var file = inputEl.files && inputEl.files.length ? inputEl.files[0] : null;
            if (!file) {
                wrapEl.classList.add('hidden');
                imgEl.classList.add('hidden');
                imgEl.src = '';
                metaEl.textContent = '';
                return;
            }

            wrapEl.classList.remove('hidden');
            var fileType = String(file.type || '').toLowerCase();
            var label = file.name + ' (' + formatFileSize(file.size || 0) + ')';

            if (fileType.indexOf('image/') === 0) {
                var reader = new FileReader();
                reader.onload = function (ev) {
                    imgEl.src = (ev.target && ev.target.result) ? ev.target.result : '';
                    imgEl.classList.remove('hidden');
                    metaEl.textContent = label;
                };
                reader.readAsDataURL(file);
                return;
            }

            imgEl.classList.add('hidden');
            imgEl.src = '';
            metaEl.textContent = label + ' — preview not available for this file type.';
        });
    }

    bindFilePreview('handwritten_salaysay', 'handwritten_preview_wrap', 'handwritten_preview_img', 'handwritten_preview_meta');
    bindFilePreview('file', 'evidence_preview_wrap', 'evidence_preview_img', 'evidence_preview_meta');
})();
</script>
@endsection
