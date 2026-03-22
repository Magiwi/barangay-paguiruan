<?php

namespace Tests\Feature;

use App\Models\CertificateRequest;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateReviewWaveBTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_certificate_without_preapproval_review(): void
    {
        $admin = $this->createResident([
            'role' => User::ROLE_ADMIN,
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);

        $resident = $this->createResident([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);

        $certificate = CertificateRequest::create([
            'user_id' => $resident->id,
            'certificate_type' => 'Barangay Clearance',
            'purpose' => 'Job application',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.certificates.update', $certificate), [
                'status' => 'approved',
                'remarks' => 'Looks good.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Certificate request approved.');
        $this->assertSame('approved', $certificate->fresh()->status);
    }

    public function test_admin_review_updates_review_metadata_and_allows_approval(): void
    {
        $admin = $this->createResident([
            'role' => User::ROLE_ADMIN,
            'first_name' => 'Admin',
            'last_name' => 'Reviewer',
        ]);

        $resident = $this->createResident([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $certificate = CertificateRequest::create([
            'user_id' => $resident->id,
            'certificate_type' => 'Certificate of Indigency',
            'purpose' => 'Medical Assistance',
            'extra_fields' => ['monthly_income' => 'No Income'],
            'status' => 'pending',
        ]);

        $reviewResponse = $this->actingAs($admin)
            ->put(route('admin.certificates.review.update', $certificate), [
                'certificate_name_override' => 'Maria P. Santos',
                'certificate_address_override' => 'Purok 1, Barangay Paguiruan',
                'certificate_issued_on' => now()->toDateString(),
                'purpose' => 'Financial Assistance',
                'monthly_income' => 'Below ₱5,000',
            ]);

        $reviewResponse->assertRedirect();
        $reviewResponse->assertSessionHas('success', 'Certificate details updated. You may now approve or reject.');

        $certificate = $certificate->fresh();
        $this->assertNotNull($certificate->reviewed_at);
        $this->assertSame($admin->id, $certificate->reviewed_by);
        $this->assertSame('Below ₱5,000', data_get($certificate->extra_fields, 'monthly_income'));
        $this->assertSame('Financial Assistance', $certificate->purpose);

        $approveResponse = $this->actingAs($admin)
            ->post(route('admin.certificates.update', $certificate), [
                'status' => 'approved',
                'remarks' => 'Approved after review.',
            ]);

        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success', 'Certificate request approved.');

        $this->assertSame('approved', $certificate->fresh()->status);
    }

    private function createResident(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => 'Purok 1']);

        $defaults = [
            'first_name' => 'Juan',
            'middle_name' => 'D',
            'last_name' => 'Cruz',
            'suffix' => null,
            'house_no' => '101',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Main St',
            'contact_number' => '+639171234567',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'email' => 'user' . uniqid() . '@example.com',
            'password' => 'password123',
            'head_of_family_id' => null,
            'family_link_status' => null,
            'relationship_to_head' => null,
            'household_id' => null,
        ];

        $data = array_merge($defaults, array_intersect_key($overrides, $defaults));
        $user = User::create($data);

        $user->forceFill([
            'role' => $overrides['role'] ?? User::ROLE_RESIDENT,
            'status' => $overrides['status'] ?? User::STATUS_APPROVED,
            'is_suspended' => $overrides['is_suspended'] ?? false,
        ])->save();

        return $user->fresh();
    }
}
