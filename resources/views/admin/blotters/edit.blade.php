@extends($layout ?? 'layouts.admin')

@section('title', 'Edit Blotter - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Edit Blotter Record</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $blotter->blotter_number }} &middot; Updates are tracked in revision history.
                </p>
            </div>
            <a href="{{ route($rp . '.blotters.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to Records
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route($rp . '.blotters.update', $blotter) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="complainant_name" class="mb-1.5 block text-sm font-medium text-gray-700">Complainant Name <span class="text-red-600">*</span></label>
                        <input type="text" id="complainant_name" name="complainant_name" value="{{ old('complainant_name', $blotter->complainant_name) }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" required>
                        @error('complainant_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="incident_date" class="mb-1.5 block text-sm font-medium text-gray-700">Incident Date <span class="text-red-600">*</span></label>
                            <input type="date" id="incident_date" name="incident_date" value="{{ old('incident_date', optional($blotter->incident_date)->toDateString()) }}"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" required>
                            @error('incident_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="complainant_user_id" class="mb-1.5 block text-sm font-medium text-gray-700">Linked Resident (optional)</label>
                            <input type="number" id="complainant_user_id" name="complainant_user_id" min="1"
                                   value="{{ old('complainant_user_id', $blotter->complainant_user_id) }}"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                   placeholder="Resident user ID">
                            @error('complainant_user_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="remarks" class="mb-1.5 block text-sm font-medium text-gray-700">Remarks</label>
                        <textarea id="remarks" name="remarks" rows="5"
                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                  placeholder="Add or update blotter notes...">{{ old('remarks', $blotter->remarks) }}</textarea>
                        @error('remarks') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="text-sm font-semibold text-gray-800">Evidence Files</p>
                        <p class="mt-1 text-xs text-gray-500">You may replace files. Handwritten salaysay must always remain available.</p>

                        <div class="mt-4 space-y-3">
                            <div>
                                <label for="handwritten_salaysay" class="mb-1.5 block text-sm font-medium text-gray-700">Replace Handwritten Salaysay</label>
                                <input type="file" id="handwritten_salaysay" name="handwritten_salaysay" accept="image/*"
                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                @if ($blotter->handwritten_salaysay_path)
                                    <p class="mt-1 text-xs text-gray-500">Current file: {{ basename($blotter->handwritten_salaysay_path) }}</p>
                                @endif
                                @error('handwritten_salaysay') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="file" class="mb-1.5 block text-sm font-medium text-gray-700">Replace Optional Evidence / Proof</label>
                                <input type="file" id="file" name="file" accept=".pdf,image/*"
                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
                                @if ($blotter->file_path)
                                    <p class="mt-1 text-xs text-gray-500">Current file: {{ basename($blotter->file_path) }}</p>
                                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-gray-700">
                                        <input type="checkbox" name="remove_file" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        Remove current optional evidence file
                                    </label>
                                @endif
                                @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="correction_note" class="mb-1.5 block text-sm font-medium text-gray-700">Correction Note <span class="text-red-600">*</span></label>
                        <textarea id="correction_note" name="correction_note" rows="3" required
                                  class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                  placeholder="Explain why this correction is needed...">{{ old('correction_note') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">This note is saved in revision history for audit tracking.</p>
                        @error('correction_note') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route($rp . '.blotters.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <aside class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Revision History</h2>
                @if ($revisions->isEmpty())
                    <p class="mt-3 text-sm text-gray-500">No revisions yet for this blotter record.</p>
                @else
                    <div class="mt-4 space-y-4">
                        @foreach ($revisions as $revision)
                            <article class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ ucfirst($revision->action) }}
                                    <span class="text-xs font-normal text-gray-500">- {{ $revision->created_at->format('M d, Y h:i A') }}</span>
                                </p>
                                <p class="mt-1 text-xs text-gray-600">
                                    By {{ $revision->changedBy?->first_name }} {{ $revision->changedBy?->last_name }}
                                </p>
                                @if ($revision->change_note)
                                    <p class="mt-2 text-xs text-gray-700">{{ $revision->change_note }}</p>
                                @endif
                                @if (!empty($revision->changed_fields))
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($revision->changed_fields as $field)
                                            <span class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700">{{ $field }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection

