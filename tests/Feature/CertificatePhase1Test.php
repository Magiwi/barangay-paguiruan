<?php

namespace Tests\Feature;

use App\Models\CertificateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class CertificatePhase1Test extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_admin_reject_certificate_requires_remarks(): void
    {
        $admin = $this->createAdminUser();
        $resident = $this->createResidentUser();

        $certificate = CertificateRequest::create([
            'user_id' => $resident->id,
            'certificate_type' => 'Barangay Clearance',
            'purpose' => 'Job application',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.certificates.update', $certificate), [
                'status' => 'rejected',
                'remarks' => '',
            ])
            ->assertSessionHasErrors(['remarks']);

        $this->assertSame('pending', $certificate->fresh()->status);
    }

    public function test_admin_reject_certificate_with_remarks_succeeds(): void
    {
        $admin = $this->createAdminUser();
        $resident = $this->createResidentUser();

        $certificate = CertificateRequest::create([
            'user_id' => $resident->id,
            'certificate_type' => 'Barangay Clearance',
            'purpose' => 'Job application',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.certificates.update', $certificate), [
                'status' => 'rejected',
                'remarks' => 'Incomplete supporting documents per barangay policy.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Certificate request rejected.');

        $certificate->refresh();
        $this->assertSame('rejected', $certificate->status);
        $this->assertStringContainsString('Incomplete', (string) $certificate->remarks);
    }

    public function test_resident_residency_request_requires_valid_id_upload(): void
    {
        $resident = $this->createResidentUser();

        $this->actingAs($resident)
            ->post(route('resident.certificates.store'), [
                'certificate_type' => 'Residency Certificate',
                'purpose' => 'Job application',
                'residency_start_year' => 2020,
            ])
            ->assertSessionHasErrors(['valid_id']);
    }

    public function test_resident_residency_request_accepts_valid_id_upload(): void
    {
        $resident = $this->createResidentUser();
        $file = UploadedFile::fake()->image('government_id.jpg', 800, 600);

        $this->actingAs($resident)
            ->post(route('resident.certificates.store'), [
                'certificate_type' => 'Residency Certificate',
                'purpose' => 'Job application',
                'residency_start_year' => 2020,
                'valid_id' => $file,
            ])
            ->assertRedirect(route('resident.certificates.index'))
            ->assertSessionHas('success');

        $req = CertificateRequest::query()->where('user_id', $resident->id)->first();
        $this->assertNotNull($req);
        $this->assertNotEmpty(data_get($req->extra_fields, 'valid_id_path'));
    }
}
