<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $html = Str::markdown($page->body ?? '');

        return view('public.page', [
            'page' => $page,
            'html' => $html,
        ]);
    }
}
