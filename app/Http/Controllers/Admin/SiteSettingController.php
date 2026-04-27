<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $values = [];
        foreach (array_keys(SiteSetting::DEFAULTS) as $key) {
            $values[$key] = SiteSetting::getValue($key);
        }

        return view('admin.site-settings.edit', ['settings' => $values]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (array_keys(SiteSetting::DEFAULTS) as $key) {
            $rules[$key] = ['nullable', 'string', 'max:65000'];
        }

        $validated = $request->validate($rules);

        $pairs = [];
        foreach (array_keys(SiteSetting::DEFAULTS) as $key) {
            $pairs[$key] = $validated[$key] ?? '';
        }

        SiteSetting::upsertMany($pairs);

        AuditService::log('site_settings_updated', null, 'Updated public site content (home, contact, and PDF boilerplate)');

        return redirect()->route('admin.site-settings.edit')
            ->with('success', 'Site content saved.');
    }
}
