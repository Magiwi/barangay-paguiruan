<?php

namespace Tests\Feature;

use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthRegistrationPermanentAddressValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_invalid_email_format(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $response = $this->post('/register', $this->basePayload($purok, [
            'email' => 'not-a-valid-email',
        ]));

        $response->assertSessionHasErrors(['email']);
    }

    public function test_non_permanent_registration_requires_permanent_region_and_address_fields(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $response = $this->post('/register', $this->basePayload($purok, [
            'resident_type' => 'non-permanent',
            'permanent_house_no' => '',
            'permanent_street' => '',
            'permanent_region' => '',
            'permanent_barangay' => '',
            'permanent_city' => '',
            'permanent_province' => '',
        ]));

        $response->assertSessionHasErrors([
            'permanent_house_no',
            'permanent_street',
            'permanent_region',
            'permanent_barangay',
            'permanent_city',
            'permanent_province',
        ]);
    }

    public function test_permanent_registration_clears_permanent_address_fields_before_save(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $email = 'permanent.' . uniqid() . '@example.com';

        $response = $this->post('/register', $this->basePayload($purok, [
            'email' => $email,
            'resident_type' => 'permanent',
            'permanent_house_no' => '77',
            'permanent_street' => 'Outside Street',
            'permanent_region' => 'Region III',
            'permanent_barangay' => 'Some Barangay',
            'permanent_city' => 'Some City',
            'permanent_province' => 'Some Province',
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => User::ROLE_RESIDENT,
            'status' => User::STATUS_PENDING,
            'resident_type' => 'permanent',
            'permanent_house_no' => null,
            'permanent_street' => null,
            'permanent_region' => null,
            'permanent_barangay' => null,
            'permanent_city' => null,
            'permanent_province' => null,
        ]);
    }

    public function test_pwd_yes_requires_pwd_proof_upload(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $response = $this->post('/register', $this->basePayload($purok, [
            'is_pwd' => 'yes',
            'pwd_proof' => null,
            'government_id_type' => null,
            'government_id_proof' => null,
        ]));

        $response->assertSessionHasErrors(['pwd_proof']);
    }

    public function test_senior_yes_requires_senior_proof_upload(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $response = $this->post('/register', $this->basePayload($purok, [
            'is_senior' => 'yes',
            'senior_proof' => null,
            'government_id_type' => null,
            'government_id_proof' => null,
        ]));

        $response->assertSessionHasErrors(['senior_proof']);
    }

    public function test_government_id_fields_are_required_only_when_pwd_and_senior_are_no(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $response = $this->post('/register', $this->basePayload($purok, [
            'is_pwd' => 'no',
            'is_senior' => 'no',
            'government_id_type' => null,
            'government_id_proof' => null,
        ]));

        $response->assertSessionHasErrors(['government_id_type', 'government_id_proof']);
    }

    public function test_government_id_fields_are_not_required_when_pwd_or_senior_is_yes(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();
        $email = 'pwd.' . uniqid() . '@example.com';

        $response = $this->post('/register', $this->basePayload($purok, [
            'email' => $email,
            'is_pwd' => 'yes',
            'pwd_proof' => UploadedFile::fake()->create('pwd-proof.pdf', 100, 'application/pdf'),
            'government_id_type' => null,
            'government_id_proof' => null,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'is_pwd' => true,
            'government_id_type' => null,
            'government_id_path' => null,
        ]);
    }

    /**
     */
    private function createAddress(): Purok
    {
        return Purok::create([
            'name' => 'Purok 1',
            'description' => 'Test purok',
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function basePayload(Purok $purok, array $overrides = []): array
    {
        return array_replace([
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'suffix' => '',
            'house_no' => '101',
            'purok_id' => $purok->id,
            'sitio_subdivision' => '',
            'contact_number' => '9171234567',
            'gender' => 'male',
            'birthdate' => now()->subYears(25)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'yes',
            'resident_type' => 'permanent',
            'is_pwd' => 'no',
            'is_senior' => 'no',
            'government_id_type' => 'national_id',
            'government_id_proof' => UploadedFile::fake()->image('government-id.jpg', 100, 100),
            'email' => 'resident.' . uniqid() . '@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'privacy_consent' => '1',
            'permanent_house_no' => null,
            'permanent_street' => null,
            'permanent_region' => null,
            'permanent_barangay' => null,
            'permanent_city' => null,
            'permanent_province' => null,
        ], $overrides);
    }
}
