@extends($layout ?? 'layouts.admin')

@section('title', 'Create Announcement - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <a href="{{ route($rp . '.announcements.index') }}" class="ui-link text-sm">Back to announcements</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">Create Announcement</h1>

            @if ($errors->any())
                <x-ui.alert type="error">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route($rp . '.announcements.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 ui-form-focus">
                </div>
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" id="content" rows="6" required class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 ui-form-focus">{{ old('content') }}</textarea>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Photo (optional)</label>
                    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--brand-100)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[var(--brand-700)] hover:file:opacity-90">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG or WEBP. Max 5MB.</p>
                </div>
                @if ($labels->isNotEmpty())
                <div>
                    <label for="labels" class="block text-sm font-medium text-gray-700 mb-1">Labels (optional)</label>
                    <select name="labels[]" id="labels" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 ui-form-focus text-sm">
                        <option value="">Select label</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}" {{ in_array($label->id, old('labels', [])) ? 'selected' : '' }}>
                                {{ $label->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Choose one label to classify this announcement.</p>
                </div>
                @endif
                <div class="rounded-lg border border-[var(--brand-400)] bg-[var(--brand-100)] px-4 py-3">
                    @if (auth()->user()->role === 'admin')
                        <p class="text-sm text-[var(--brand-800)]">
                            <span class="font-semibold">Auto-approved:</span> As admin, this announcement will be published immediately upon creation.
                        </p>
                    @else
                        <p class="text-sm text-[var(--brand-800)]">
                            <span class="font-semibold">Requires approval:</span> This announcement will be submitted for admin review before publishing.
                        </p>
                    @endif
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="ui-btn ui-btn-primary rounded-lg">
                        Create
                    </button>
                    <a href="{{ route($rp . '.announcements.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
