<?php

namespace Tests\Feature;

use App\Models\SitePageLayout;
use App\Models\SitePageLayoutRevision;
use App\Models\User;
use App\Services\SitePage\AboutPageDefaults;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class AboutPageBuilderTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_guest_can_view_about_with_default_sections(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('About Barangay', false);
    }

    public function test_resident_can_view_about_with_default_sections(): void
    {
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);

        $this->actingAs($resident)
            ->get(route('about'))
            ->assertOk()
            ->assertSee('About Barangay', false);
    }

    public function test_admin_can_open_about_page_builder(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->get(route('admin.about-page.edit'))
            ->assertOk()
            ->assertSee('About Page Builder', false);
    }

    public function test_resident_cannot_access_about_builder(): void
    {
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);

        $this->actingAs($resident)
            ->get(route('admin.about-page.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_save_draft_and_publish_updates_public_copy(): void
    {
        $admin = $this->createAdminUser();
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);

        $sections = AboutPageDefaults::sections();
        $sections[0]['data']['title_line2'] = 'Paguiruan TEST';

        $this->actingAs($admin)
            ->putJson(route('admin.about-page.draft'), ['sections' => $sections])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->actingAs($resident)
            ->get(route('about'))
            ->assertOk()
            ->assertDontSee('Paguiruan TEST', false);

        $this->actingAs($admin)
            ->post(route('admin.about-page.publish'))
            ->assertRedirect(route('admin.about-page.edit'));

        $this->actingAs($resident)
            ->get(route('about'))
            ->assertOk()
            ->assertSee('Paguiruan TEST', false);

        $layout = SitePageLayout::query()->where('page_key', SitePageLayout::PAGE_ABOUT)->first();
        $this->assertNotNull($layout);
        $this->assertNotNull($layout->published_at);
    }

    public function test_publish_is_rejected_when_no_section_is_visible(): void
    {
        $admin = $this->createAdminUser();

        $sections = AboutPageDefaults::sections();
        foreach ($sections as $i => $_) {
            $sections[$i]['visible'] = false;
        }

        $this->actingAs($admin)
            ->putJson(route('admin.about-page.draft'), ['sections' => $sections])
            ->assertOk();

        $before = SitePageLayout::query()->where('page_key', SitePageLayout::PAGE_ABOUT)->first();
        $this->assertNotNull($before);

        $this->actingAs($admin)
            ->post(route('admin.about-page.publish'))
            ->assertRedirect(route('admin.about-page.edit'))
            ->assertSessionHas('error');

        $after = $before->fresh();
        $this->assertNull($after->published_at);
    }

    public function test_publish_creates_revision_and_audit_log(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin)
            ->post(route('admin.about-page.publish'))
            ->assertRedirect(route('admin.about-page.edit'));

        $this->assertDatabaseHas('site_page_layout_revisions', [
            'page_key' => SitePageLayout::PAGE_ABOUT,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'about_page_published',
        ]);
    }

    public function test_restore_revision_republishes_that_snapshot(): void
    {
        $admin = $this->createAdminUser();
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);

        $sections = AboutPageDefaults::sections();
        $sections[0]['data']['title_line2'] = 'Version One';

        $this->actingAs($admin)
            ->putJson(route('admin.about-page.draft'), ['sections' => $sections])
            ->assertOk();
        $this->actingAs($admin)
            ->post(route('admin.about-page.publish'))
            ->assertRedirect(route('admin.about-page.edit'));

        $rev1 = SitePageLayoutRevision::query()->orderByDesc('id')->first();
        $this->assertNotNull($rev1);

        $sections[0]['data']['title_line2'] = 'Version Two';
        $this->actingAs($admin)
            ->putJson(route('admin.about-page.draft'), ['sections' => $sections])
            ->assertOk();
        $this->actingAs($admin)
            ->post(route('admin.about-page.publish'))
            ->assertRedirect(route('admin.about-page.edit'));

        $this->actingAs($resident)
            ->get(route('about'))
            ->assertOk()
            ->assertSee('Version Two', false);

        $this->actingAs($admin)
            ->post(route('admin.about-page.restore-revision', $rev1))
            ->assertRedirect(route('admin.about-page.edit'));

        $this->actingAs($resident)
            ->get(route('about'))
            ->assertOk()
            ->assertSee('Version One', false)
            ->assertDontSee('Version Two', false);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'about_page_restored',
        ]);
    }

    public function test_location_per_purok_map_url_is_output_for_switcher_script(): void
    {
        $admin = $this->createAdminUser();
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);

        $sections = AboutPageDefaults::sections();
        $customUrl = 'https://maps.example.com/maps?custom=purok-test';
        foreach ($sections as $i => $section) {
            if (($section['type'] ?? '') === 'location') {
                $sections[$i]['data']['purok_options'][1]['map_embed_url'] = $customUrl;
            }
        }

        $this->actingAs($admin)
            ->putJson(route('admin.about-page.draft'), ['sections' => $sections])
            ->assertOk();
        $this->actingAs($admin)
            ->post(route('admin.about-page.publish'))
            ->assertRedirect(route('admin.about-page.edit'));

        $this->actingAs($resident)
            ->get(route('about'))
            ->assertOk()
            ->assertSee('custom=purok-test', false);
    }
}
