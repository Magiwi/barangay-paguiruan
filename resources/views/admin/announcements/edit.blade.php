@extends($layout ?? 'layouts.admin')

@section('title', 'Edit Announcement - e-Governance')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <a href="{{ route($rp . '.announcements.index') }}" class="ui-link text-sm">Back to announcements</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">Edit Announcement</h1>

            @if ($errors->any())
                <x-ui.alert type="error">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route($rp . '.announcements.update', $announcement) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" required maxlength="255" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 ui-form-focus">
                </div>
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" id="content" rows="6" required class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 ui-form-focus">{{ old('content', $announcement->content) }}</textarea>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Photo (optional)</label>
                    @if ($announcement->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="h-32 w-auto rounded-lg object-cover border border-gray-200">
                            <label class="mt-2 flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remove_image" value="1" {{ old('remove_image') ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="text-sm text-gray-700">Remove current photo</span>
                            </label>
                        </div>
                    @endif
                    <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--brand-100)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-[var(--brand-700)] hover:file:opacity-90">
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG or WEBP. Max 5MB. {{ $announcement->image ? 'Upload a new file to replace.' : '' }}</p>
                </div>
                @if ($labels->isNotEmpty())
                <div>
                    <label for="labels" class="block text-sm font-medium text-gray-700 mb-1">Labels (optional)</label>
                    <select name="labels[]" id="labels" class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 ui-form-focus text-sm">
                        <option value="">Select label</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}" {{ in_array($label->id, old('labels', $selectedLabels)) ? 'selected' : '' }}>
                                {{ $label->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Choose one label to classify this announcement.</p>
                </div>
                @endif
                <div class="rounded-lg border px-4 py-3 {{ $announcement->status === 'approved' ? 'bg-green-50 border-green-200' : ($announcement->status === 'pending' ? 'bg-amber-50 border-amber-200' : ($announcement->status === 'rejected' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200')) }}">
                    <p class="text-sm font-medium {{ $announcement->status === 'approved' ? 'text-green-800' : ($announcement->status === 'pending' ? 'text-amber-800' : ($announcement->status === 'rejected' ? 'text-red-800' : 'text-gray-700')) }}">
                        Current status:
                        <span class="uppercase tracking-wider">{{ $announcement->status }}</span>
                    </p>
                    @if ($announcement->status !== 'approved')
                        <p class="mt-1 text-xs text-gray-500">Status can be changed from the announcements list using Approve/Reject actions.</p>
                    @endif
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="ui-btn ui-btn-primary rounded-lg">
                        Update
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
