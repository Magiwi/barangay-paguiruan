<?php

namespace Tests\Feature;

use App\Models\SmsTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class SmsManagementControllerTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_active_template_cannot_be_edited_without_disabling_first(): void
    {
        $admin = $this->createAdminUser([
            'first_name' => 'SMS',
            'middle_name' => 'A',
            'last_name' => 'Admin',
            'contact_number' => '09171234567',
        ]);
        $template = SmsTemplate::query()->create([
            'key' => 'certificate_released_pickup',
            'title' => 'Certificate Released (Pickup)',
            'message' => 'Original template content.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.sms.index'))
            ->put(route('admin.sms.templates.update', $template), [
                'title' => 'Edited Title',
                'message' => 'Edited body while enabled.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.sms.index'));
        $response->assertSessionHasErrors('template');

        $template->refresh();
        $this->assertSame('Certificate Released (Pickup)', $template->title);
        $this->assertSame('Original template content.', $template->message);
        $this->assertTrue($template->is_active);
    }

    public function test_template_can_be_updated_when_disabled(): void
    {
        $admin = $this->createAdminUser([
            'first_name' => 'SMS',
            'middle_name' => 'A',
            'last_name' => 'Admin',
            'contact_number' => '09171234567',
        ]);
        $template = SmsTemplate::query()->create([
            'key' => 'permit_released_pickup',
            'title' => 'Permit Released (Pickup)',
            'message' => 'Original permit content.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.sms.index'))
            ->put(route('admin.sms.templates.update', $template), [
                'title' => 'Updated Permit Title',
                'message' => 'Updated permit body.',
                'is_active' => '0',
            ]);

        $response->assertRedirect(route('admin.sms.index'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $template->refresh();
        $this->assertSame('Updated Permit Title', $template->title);
        $this->assertSame('Updated permit body.', $template->message);
        $this->assertFalse($template->is_active);
    }
}
