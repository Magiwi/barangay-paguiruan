<?php

namespace Tests\Feature;

use App\Models\CertificateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class CertificateReviewWaveBTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_admin_can_approve_pending_certificate_without_preapproval_review(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);

        $resident = $this->createResidentUser([
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
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'first_name' => 'Admin',
            'last_name' => 'Reviewer',
        ]);

        $resident = $this->createResidentUser([
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

}
