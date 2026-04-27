@extends('layouts.resident')

@section('title', 'Request Blotter Record - e-Governance System')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">

        {{-- Back link --}}
        <a href="{{ route('resident.blotter-requests.index') }}" class="ui-focus-ring mb-6 inline-flex items-center gap-1.5 rounded-md text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Back to My Blotter Requests
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
        <form method="POST" action="{{ route('resident.blotter-requests.store') }}" class="ui-surface-card overflow-hidden">
            @csrf

            {{-- Card Header --}}
            <div class="border-b border-gray-200 bg-gray-50/60 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="ui-icon-chip bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight text-gray-800">Request Blotter Record</h1>
                        <p class="mt-0.5 text-sm text-gray-500">Submit a request to obtain a copy of an official blotter record.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">

                {{-- Section 1: Request Details --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Request Details</h3>
                    </div>

                    <div>
                        <label for="blotter_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Blotter Record <span class="text-red-500">*</span>
                        </label>
                        <select name="blotter_id" id="blotter_id" required
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('blotter_id') border-red-400 ring-red-100 @enderror">
                            <option value="">Select your blotter record</option>
                            @foreach ($blotters as $blotter)
                                <option value="{{ $blotter->id }}" @selected(old('blotter_id') == $blotter->id)>
                                    {{ $blotter->complainant_name }}
                                    @if ($blotter->incident_date)
                                        — {{ \Illuminate\Support\Carbon::parse($blotter->incident_date)->format('M d, Y') }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-gray-500">Only active blotter entries where you are listed as the linked complainant appear here.</p>
                        @if ($blotters->isEmpty())
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900 space-y-2">
                                <p class="font-medium">Walang makitang naka-link na blotter sa account mo.</p>
                                <p class="text-amber-800">Kung nakapag-file ka sa barangay pero hindi pa naka-link ang record mo, bisitahin ang barangay hall o tumawag sa opisina para i-update ang iyong resident profile at blotter entry. Pagkatapos noon, lalabas ang case dito at makakapag-request ka ng kopya.</p>
                            </div>
                        @endif
                        @error('blotter_id')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Section 2: Purpose --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Purpose</h3>
                    </div>

                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Purpose of Request <span class="text-red-500">*</span>
                        </label>
                        <textarea name="purpose" id="purpose" rows="4" required minlength="10"
                                  placeholder="Explain the reason for requesting this blotter record (at least 10 characters)..."
                                  class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 @error('purpose') border-red-400 ring-red-100 @enderror">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Section 3: Requestor Information (auto-filled) --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Requestor Information</h3>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->first_name }} {{ auth()->user()->middle_name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->purokRelation?->name ?? 'No purok assigned' }} &middot; {{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">This request will be submitted under your verified account.</p>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                <a href="{{ route('resident.blotter-requests.index') }}" class="ui-focus-ring rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
