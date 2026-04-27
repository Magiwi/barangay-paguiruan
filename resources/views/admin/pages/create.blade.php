@extends('layouts.admin')

@section('title', 'New site page - Admin')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">New site page</h1>
            <a href="{{ route('admin.pages.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Back</a>
        </div>

        @if ($errors->any())
            <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">URL slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                       placeholder="e.g. transparency"
                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and hyphens only. Public URL: <span class="font-mono text-gray-700">/p/<span id="slug-preview">slug</span></span></p>
            </div>
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Body (Markdown)</label>
                <textarea name="body" id="body" rows="14" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:ring-blue-500">{{ old('body') }}</textarea>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status') === 'published')>Published</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 pt-4">
                <a href="{{ route('admin.pages.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="ui-btn ui-btn-primary rounded-lg">Create page</button>
            </div>
        </form>
    </div>
</section>
<script>
document.getElementById('slug')?.addEventListener('input', function () {
    const el = document.getElementById('slug-preview');
    if (el) el.textContent = this.value || 'slug';
});
</script>
@endsection
