<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()->orderBy('title')->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request);

        if ($validated['status'] === Page::STATUS_PUBLISHED) {
            $validated['published_at'] = now();
        } else {
            $validated['published_at'] = null;
        }

        $page = Page::create($validated);

        AuditService::log('cms_page_created', $page, "Created site page: {$page->title} ({$page->slug})");

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $this->validatePage($request, $page->id);

        if ($validated['status'] === Page::STATUS_PUBLISHED) {
            $validated['published_at'] = $page->published_at ?? now();
        } else {
            $validated['published_at'] = null;
        }

        $page->update($validated);

        AuditService::log('cms_page_updated', $page, "Updated site page: {$page->title} ({$page->slug})");

        return redirect()->route('admin.pages.edit', $page)
            ->with('success', 'Page saved.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $slug = $page->slug;
        $title = $page->title;
        $page->delete();

        AuditService::log('cms_page_deleted', null, "Deleted site page: {$title} ({$slug})");

        return redirect()->route('admin.pages.index')
            ->with('success', 'Page deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request, ?int $ignoreId = null): array
    {
        $uniqueSlug = Rule::unique('pages', 'slug');
        if ($ignoreId !== null) {
            $uniqueSlug = $uniqueSlug->ignore($ignoreId);
        }

        return $request->validate([
            'slug' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $uniqueSlug,
            ],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in([Page::STATUS_DRAFT, Page::STATUS_PUBLISHED])],
        ]);
    }
}
