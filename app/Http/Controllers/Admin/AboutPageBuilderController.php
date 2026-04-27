<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Controller;
use App\Models\SitePageLayout;
use App\Models\SitePageLayoutRevision;
use App\Services\AuditService;
use App\Services\SitePage\AboutPageContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AboutPageBuilderController extends Controller
{
    public function __construct(
        private AboutPageContentService $contentService
    ) {}

    public function edit(): View
    {
        $layout = $this->contentService->getOrCreateAboutLayout();
        $draftSections = $this->contentService->getDraftSectionsForBuilder();

        $revisions = SitePageLayoutRevision::query()
            ->where('page_key', SitePageLayout::PAGE_ABOUT)
            ->with('user:id,first_name,last_name,email')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('admin.about-page.edit', [
            'layout' => $layout,
            'draftSections' => $draftSections,
            'revisions' => $revisions,
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.id' => ['required', 'string', 'max:64'],
            'sections.*.type' => ['required', 'string'],
            'sections.*.visible' => ['boolean'],
            'sections.*.data' => ['nullable', 'array'],
        ]);

        /** @var array<int, mixed> $raw */
        $raw = $request->input('sections', []);

        $normalized = $this->contentService->normalizeAndValidate($raw, mergeDefaults: true);

        $layout = $this->contentService->getOrCreateAboutLayout();
        $layout->draft_sections = $normalized;
        $layout->save();

        AuditService::log('about_page_draft_saved', $layout, 'Saved About page draft (not published).');

        return response()->json(['ok' => true, 'sections' => $normalized]);
    }

    public function publish(): RedirectResponse
    {
        $layout = $this->contentService->getOrCreateAboutLayout();
        $draft = $layout->draft_sections;
        if (! is_array($draft) || $draft === []) {
            $draft = $this->contentService->getDraftSectionsForBuilder();
        }

        $normalized = $this->contentService->normalizeAndValidate($draft, mergeDefaults: true);

        if (! $this->contentService->hasAtLeastOneVisibleSection($normalized)) {
            return redirect()->route('admin.about-page.edit')
                ->with('error', 'At least one section must be visible before you can publish.');
        }

        $layout->published_sections = $normalized;
        $layout->published_at = now();
        $layout->save();

        SitePageLayoutRevision::query()->create([
            'page_key' => SitePageLayout::PAGE_ABOUT,
            'sections' => $normalized,
            'user_id' => auth()->id(),
        ]);

        AuditService::log('about_page_published', $layout, 'Published About page layout to residents.');

        return redirect()->route('admin.about-page.edit')
            ->with('success', 'About page changes are now live for residents.');
    }

    public function restoreRevision(SitePageLayoutRevision $revision): RedirectResponse
    {
        if ($revision->page_key !== SitePageLayout::PAGE_ABOUT) {
            abort(404);
        }

        /** @var array<int, mixed> $raw */
        $raw = $revision->sections ?? [];
        if (! is_array($raw)) {
            $raw = [];
        }

        $normalized = $this->contentService->normalizeAndValidate($raw, mergeDefaults: true);

        if (! $this->contentService->hasAtLeastOneVisibleSection($normalized)) {
            return redirect()->route('admin.about-page.edit')
                ->with('error', 'That revision cannot be restored because no section would be visible.');
        }

        $layout = $this->contentService->getOrCreateAboutLayout();
        $layout->published_sections = $normalized;
        $layout->draft_sections = $normalized;
        $layout->published_at = now();
        $layout->save();

        SitePageLayoutRevision::query()->create([
            'page_key' => SitePageLayout::PAGE_ABOUT,
            'sections' => $normalized,
            'user_id' => auth()->id(),
        ]);

        AuditService::log('about_page_restored', $layout, "Restored About page from revision #{$revision->id}.");

        return redirect()->route('admin.about-page.edit')
            ->with('success', 'That version is now live again (draft updated to match).');
    }

    public function preview(Request $request): Response
    {
        $request->validate([
            'sections' => ['nullable', 'array'],
        ]);

        /** @var array<int, mixed> $raw */
        $raw = $request->input('sections', []);
        if (! is_array($raw)) {
            $raw = [];
        }

        $normalized = $this->contentService->normalizeAndValidate($raw, mergeDefaults: true);
        $aboutController = app(AboutController::class);
        $html = view('about.show', $aboutController->viewPayload($normalized))->render();

        return response($html, 200)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('file')->store('site-pages/about', 'public');
        $token = 'storage:'.$path;

        return response()->json([
            'path' => $token,
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
