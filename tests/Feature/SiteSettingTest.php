<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_home_uses_site_settings_when_seeded(): void
    {
        foreach (SiteSetting::DEFAULTS as $key => $value) {
            SiteSetting::query()->create(['key' => $key, 'value' => $value]);
        }

        SiteSetting::query()->where('key', 'welcome_hero_badge')->update(['value' => 'CMS Badge Test']);

        $this->get('/')->assertOk()->assertSee('CMS Badge Test', false);
    }

    public function test_admin_can_update_site_settings(): void
    {
        foreach (SiteSetting::DEFAULTS as $key => $value) {
            SiteSetting::query()->create(['key' => $key, 'value' => $value]);
        }

        $admin = $this->createAdminUser();

        $payload = SiteSetting::DEFAULTS;
        $payload['contact_phone'] = '(0999) 000-0000';

        $response = $this->actingAs($admin)->put(route('admin.site-settings.update'), $payload);

        $response->assertRedirect(route('admin.site-settings.edit'));
        $this->assertSame('(0999) 000-0000', SiteSetting::query()->where('key', 'contact_phone')->value('value'));
    }

    public function test_admin_can_update_pdf_boilerplate_site_settings(): void
    {
        foreach (SiteSetting::DEFAULTS as $key => $value) {
            SiteSetting::query()->create(['key' => $key, 'value' => $value]);
        }

        $admin = $this->createAdminUser();

        $payload = SiteSetting::DEFAULTS;
        $payload['doc_seal_note'] = '*TEST SEAL NOTE*';
        $payload['doc_header_line_1'] = 'Test Republic Line';

        $response = $this->actingAs($admin)->put(route('admin.site-settings.update'), $payload);

        $response->assertRedirect(route('admin.site-settings.edit'));
        $this->assertSame('*TEST SEAL NOTE*', SiteSetting::query()->where('key', 'doc_seal_note')->value('value'));
        $this->assertSame('Test Republic Line', SiteSetting::getValue('doc_header_line_1'));
    }
}
