@extends('layouts.resident')

@section('title', 'Submit Complaint - e-Governance System')

@section('content')
<section class="py-8">
    <div class="max-w-7xl mx-auto px-4 md:px-6">
        <a href="{{ route('resident.issues.index') }}" class="ui-focus-ring mb-6 inline-flex items-center gap-1 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
            <span>&larr;</span> Back to my complaints
        </a>

        <div class="ui-surface-card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h1 class="font-semibold tracking-tight text-gray-800 text-xl">Report a Complaint</h1>
            </div>

            @if ($errors->any())
                <div class="mx-6 mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('resident.issues.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf

                {{-- Subject --}}
                <div>
                    <label for="subject" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Subject <span class="text-red-600">*</span></label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required maxlength="255"
                           placeholder="Brief subject of your concern"
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subject') border-red-500 @enderror">
                </div>

                {{-- Category --}}
                <div>
                    <label for="category" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Category <span class="text-red-600">*</span></label>
                    <select name="category" id="category" required
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror">
                        <option value="">Select a category</option>
                        @foreach (['Infrastructure', 'Noise', 'Sanitation', 'Safety', 'Flooding', 'Stray Animals', 'Illegal Activity', 'Other'] as $cat)
                            <option value="{{ $cat }}" @selected(old('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Other category: Please specify --}}
                <div id="otherDetailsWrap" class="hidden">
                    <label for="other_details" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Please specify your issue <span class="text-red-600">*</span></label>
                    <textarea name="other_details" id="other_details" rows="3" maxlength="2000"
                              placeholder="Describe your issue in detail..."
                              class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('other_details') border-red-500 @enderror">{{ old('other_details') }}</textarea>
                    @error('other_details')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <script>
                    (function() {
                        const cat = document.getElementById('category');
                        const wrap = document.getElementById('otherDetailsWrap');
                        const inp = document.getElementById('other_details');
                        function toggle() {
                            if (cat.value === 'Other') {
                                wrap.classList.remove('hidden');
                                inp.setAttribute('required', 'required');
                            } else {
                                wrap.classList.add('hidden');
                                inp.removeAttribute('required');
                                inp.value = '';
                            }
                        }
                        cat.addEventListener('change', toggle);
                        toggle();
                    })();
                </script>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Description <span class="text-red-600">*</span></label>
                    <textarea name="description" id="description" rows="5" required
                              placeholder="Describe the issue or concern in detail..."
                              class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                </div>

                {{-- Location --}}
                <div>
                    <label for="purok_id" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Purok <span class="text-red-600">*</span></label>
                    <select name="purok_id" id="purok_id" required
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('purok_id') border-red-500 @enderror">
                        <option value="">Select purok</option>
                        @foreach (($puroks ?? collect()) as $purok)
                            <option value="{{ $purok->id }}" @selected((string) old('purok_id', auth()->user()->purok_id) === (string) $purok->id)>{{ $purok->name }}</option>
                        @endforeach
                    </select>
                    @error('purok_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Location Note <span class="text-red-600">*</span></label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}" required maxlength="255"
                           placeholder="e.g. Tapat ng covered court / malapit sa barangay hall"
                           class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Ilagay kung saang banda sa napiling purok.</p>
                </div>

                {{-- Attachment --}}
                <div>
                    <label for="attachment" class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Attachment (optional)</label>
                    <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.pdf"
                           class="block w-full text-sm text-gray-600 rounded-lg border border-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('attachment') border-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, or PDF. Max 5MB.</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="ui-focus-ring rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                        Submit Complaint
                    </button>
                    <a href="{{ route('resident.issues.index') }}" class="ui-focus-ring rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
