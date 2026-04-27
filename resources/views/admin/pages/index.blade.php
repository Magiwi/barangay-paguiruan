@extends('layouts.admin')

@section('title', 'Site pages - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Site pages (CMS)</h1>
                <p class="mt-1 text-sm text-gray-600">Public content at <code class="rounded bg-gray-100 px-1 py-0.5 text-xs">/p/your-slug</code>. Body uses Markdown.</p>
            </div>
            <a href="{{ route('admin.pages.create') }}" class="ui-btn ui-btn-primary inline-flex rounded-lg">
                New page
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($pages as $page)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $page->title }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <a href="{{ route('cms.page', $page->slug) }}" class="text-blue-600 hover:underline" target="_blank" rel="noopener">{{ $page->slug }}</a>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($page->isPublished())
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Published</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Draft</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="font-medium text-blue-600 hover:text-blue-700">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">No pages yet. Create one or run <code class="rounded bg-gray-100 px-1">php artisan db:seed</code> for the sample page.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pages->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">{{ $pages->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
