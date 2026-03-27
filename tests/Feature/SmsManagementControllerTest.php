<?php

namespace Tests\Feature;

use App\Models\Purok;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_template_cannot_be_edited_without_disabling_first(): void
    {
        $admin = $this->createAdmin();
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
        $admin = $this->createAdmin();
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

    private function createAdmin(): User
    {
        $purok = Purok::firstOrCreate(['name' => 'Purok 1']);

        $user = User::query()->create([
            'first_name' => 'SMS',
            'middle_name' => 'A',
            'last_name' => 'Admin',
            'suffix' => null,
            'house_no' => '1',
            'street_name' => 'Main St',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'contact_number' => '09171234567',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'email' => 'sms-admin-' . uniqid() . '@example.com',
            'password' => 'password123',
            'head_of_family_id' => null,
            'family_link_status' => null,
            'relationship_to_head' => null,
            'household_id' => null,
        ]);

        $user->forceFill([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ])->save();

        return $user->fresh();
    }
}
