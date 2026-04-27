@extends('layouts.admin')

@section('title', $page->title.' - Site page - Admin')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Edit page</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('cms.page', $page->slug) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700" target="_blank" rel="noopener">View public</a>
                <a href="{{ route('admin.pages.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Back to list</a>
            </div>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">URL slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">Public: <span class="font-mono">/p/{{ old('slug', $page->slug) }}</span></p>
            </div>
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Body (Markdown)</label>
                <textarea name="body" id="body" rows="14" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:ring-blue-500">{{ old('body', $page->body) }}</textarea>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $page->status) === 'published')>Published</option>
                </select>
            </div>
            <div class="flex justify-end border-t border-gray-200 pt-4">
                <button type="submit" class="ui-btn ui-btn-primary rounded-lg">Save changes</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="mt-6" onsubmit="return confirm('Delete this page permanently?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Delete this page</button>
        </form>
    </div>
</section>
@endsection
