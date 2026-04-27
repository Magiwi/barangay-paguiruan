<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class CmsPageTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_published_page_is_visible_to_guests(): void
    {
        Page::query()->create([
            'slug' => 'hello',
            'title' => 'Hello Page',
            'body' => '# Greeting',
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->get(route('cms.page', 'hello'))
            ->assertOk()
            ->assertSee('Hello Page', false);
    }

    public function test_draft_page_returns_404(): void
    {
        Page::query()->create([
            'slug' => 'draft-only',
            'title' => 'Draft',
            'body' => 'Secret',
            'status' => Page::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $this->get(route('cms.page', 'draft-only'))->assertNotFound();
    }

    public function test_admin_can_create_a_published_page(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'New page',
            'slug' => 'new-page',
            'body' => 'Content here.',
            'status' => Page::STATUS_PUBLISHED,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'slug' => 'new-page',
            'status' => Page::STATUS_PUBLISHED,
        ]);
    }

    public function test_non_admin_cannot_access_admin_pages_list(): void
    {
        $resident = $this->createResidentUser(['role' => User::ROLE_RESIDENT]);

        $this->actingAs($resident)
            ->get(route('admin.pages.index'))
            ->assertForbidden();
    }
}
