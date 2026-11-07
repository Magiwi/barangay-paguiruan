<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class AllResidentsListTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_all_residents_hides_pending_accounts(): void
    {
        $admin = $this->createAdminUser();
        $approved = $this->createResidentUser([
            'first_name' => 'Approved',
            'last_name' => 'Neighbor',
            'email' => 'approved.neighbor@example.com',
        ]);
        $pending = $this->createResidentUser([
            'status' => User::STATUS_PENDING,
            'first_name' => 'Waiting',
            'last_name' => 'Applicant',
            'email' => 'waiting.applicant@example.com',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.residents.index'));

        $response->assertOk();
        $response->assertSee($approved->last_name);
        $response->assertDontSee($pending->last_name);

        $this->actingAs($admin)
            ->get(route('admin.residents.index', ['status' => User::STATUS_PENDING]))
            ->assertOk()
            ->assertDontSee($pending->last_name);
    }
}
