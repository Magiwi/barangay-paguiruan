<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class SuperAdminResidentDeleteTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_super_admin_can_delete_a_resident_and_keep_other_residents(): void
    {
        $superAdmin = $this->createResidentUser([
            'role' => User::ROLE_SUPER_ADMIN,
            'email' => 'super@example.com',
            'first_name' => 'Super',
            'last_name' => 'Admin',
        ]);
        $resident = $this->createResidentUser([
            'email' => 'walkin@example.com',
            'first_name' => 'Walk',
            'last_name' => 'In',
        ]);
        $other = $this->createResidentUser([
            'email' => 'other@example.com',
        ]);

        $response = $this->actingAs($superAdmin)->post(route('admin.residents.destroy', $resident), [
            'confirm_email' => 'walkin@example.com',
            'reason' => 'Trail account',
        ]);

        $response->assertRedirect(route('admin.residents.index'));
        $this->assertDatabaseMissing('users', ['id' => $resident->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'resident_deleted',
        ]);
        $this->assertTrue(
            AuditLog::query()
                ->where('action', 'resident_deleted')
                ->where('description', 'like', '%walkin@example.com%')
                ->exists()
        );
    }

    public function test_regular_admin_cannot_delete_a_resident(): void
    {
        $admin = $this->createAdminUser(['email' => 'admin@example.com']);
        $resident = $this->createResidentUser(['email' => 'keep@example.com']);

        $response = $this->actingAs($admin)->post(route('admin.residents.destroy', $resident), [
            'confirm_email' => 'keep@example.com',
            'reason' => 'Should not work',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $resident->id]);
    }

    public function test_super_admin_cannot_delete_admin_or_staff(): void
    {
        $superAdmin = $this->createResidentUser([
            'role' => User::ROLE_SUPER_ADMIN,
            'email' => 'super@example.com',
        ]);
        $admin = $this->createAdminUser(['email' => 'admin@example.com']);
        $staff = $this->createResidentUser([
            'role' => User::ROLE_STAFF,
            'email' => 'staff@example.com',
        ]);

        $this->actingAs($superAdmin)->post(route('admin.residents.destroy', $admin), [
            'confirm_email' => 'admin@example.com',
            'reason' => 'Nope',
        ])->assertForbidden();

        $this->actingAs($superAdmin)->post(route('admin.residents.destroy', $staff), [
            'confirm_email' => 'staff@example.com',
            'reason' => 'Nope',
        ])->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_wrong_confirmation_email_does_not_delete_the_resident(): void
    {
        $superAdmin = $this->createResidentUser([
            'role' => User::ROLE_SUPER_ADMIN,
            'email' => 'super@example.com',
        ]);
        $resident = $this->createResidentUser(['email' => 'keep@example.com']);

        $response = $this->actingAs($superAdmin)->from(route('admin.residents.show', $resident))
            ->post(route('admin.residents.destroy', $resident), [
                'confirm_email' => 'wrong@example.com',
                'reason' => 'Typo',
            ]);

        $response->assertRedirect(route('admin.residents.show', $resident));
        $response->assertSessionHasErrors('confirm_email');
        $this->assertDatabaseHas('users', ['id' => $resident->id]);
    }
}
