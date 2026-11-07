<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Purok;
use App\Models\StaffPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class AssistedRegistrationTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_public_register_still_requires_government_id_when_pwd_and_senior_are_no(): void
    {
        Storage::fake('public');
        $purok = $this->createAddress();

        $this->get('/register')
            ->assertOk()
            ->assertSee('Government ID is required', false)
            ->assertSee('Account Information', false);

        $response = $this->post('/register', $this->basePayload($purok, [
            'is_pwd' => 'no',
            'is_senior' => 'no',
            'government_id_type' => null,
            'government_id_proof' => null,
        ]));

        $response->assertSessionHasErrors(['government_id_type', 'government_id_proof']);
    }

    public function test_assisted_registration_without_file_requires_in_person_checkbox(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();
        $purok = $this->createAddress();

        $email = 'walkin.missing.' . uniqid() . '@example.com';

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.create'))
            ->assertOk()
            ->assertSee('Registration Management', false)
            ->assertSee('Register Resident', false)
            ->assertSee('ID verified in person', false)
            ->assertSee('Account activation', false)
            ->assertDontSee('Account Information', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('Government ID is required', false);

        $response = $this->actingAs($admin)->post(route('admin.pending-registrations.store'), $this->basePayload($purok, [
            'email' => $email,
            'government_id_type' => 'national_id',
            'government_id_proof' => null,
            'id_verified_in_person' => null,
        ]));

        $response->assertSessionHasErrors(['id_verified_in_person']);
        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);
    }

    public function test_assisted_registration_issues_a_one_time_code_and_the_resident_sets_login(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser(['first_name' => 'Desk', 'last_name' => 'Officer']);
        $purok = $this->createAddress();
        $password = 'WalkInSecret99';

        $response = $this->actingAs($admin)->post(route('admin.pending-registrations.store'), $this->basePayload($purok, [
            'last_name' => 'Walkin',
            'email' => 'ignored.walkin@example.com',
            'password' => $password,
            'password_confirmation' => $password,
            'government_id_type' => 'national_id',
            'government_id_proof' => null,
            'id_verified_in_person' => '1',
        ]));

        $response->assertRedirect(route('admin.pending-registrations.created'));
        $response->assertSessionHasNoErrors();
        $code = $response->getSession()->get('assisted_credentials.code');
        $this->assertIsString($code);
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{4}-[A-Z2-9]{4}$/', $code);

        $created = User::where('last_name', 'Walkin')->first();
        $this->assertNotNull($created);
        $this->assertNull($created->email);
        $this->assertTrue($created->hasUnclaimedAccount());
        $this->assertSame(User::ROLE_RESIDENT, $created->role);
        $this->assertSame(User::STATUS_APPROVED, $created->status);
        $this->assertSame($admin->id, $created->registered_by);
        $this->assertTrue($created->id_verified_in_person);
        $this->assertNull($created->government_id_path);
        $this->assertNull(User::where('email', 'ignored.walkin@example.com')->first());

        $audit = AuditLog::query()->where('action', 'assisted_registration')->latest('id')->first();
        $this->assertNotNull($audit);
        $this->assertStringNotContainsString($password, (string) $audit->description);
        $this->assertStringNotContainsString($code, (string) $audit->description);

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.created'))
            ->assertOk()
            ->assertSee($code)
            ->assertDontSee($password);

        auth()->logout();
        $this->app['auth']->forgetGuards();

        $this->get('/login')->assertOk()->assertSee('Activate your account', false);

        $this->post(route('account.claim.store'), ['code' => strtolower($code)])
            ->assertRedirect(route('account.claim.setup'));

        $this->post(route('account.claim.complete'), [
            'email' => 'juan.claimed@example.com',
            'password' => 'ResidentPass1',
            'password_confirmation' => 'ResidentPass1',
        ])->assertRedirect(route('resident.government-id.edit'));

        $this->assertAuthenticatedAs($created);
        $created->refresh();
        $this->assertSame('juan.claimed@example.com', $created->email);
        $this->assertFalse($created->hasUnclaimedAccount());

        auth()->logout();
        $this->app['auth']->forgetGuards();

        $this->post(route('account.claim.store'), ['code' => $code])
            ->assertSessionHasErrors('code');
    }

    public function test_reissued_claim_code_replaces_the_old_one(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();
        $purok = $this->createAddress();

        $response = $this->actingAs($admin)->post(route('admin.pending-registrations.store'), $this->basePayload($purok, [
            'last_name' => 'Reissue',
            'government_id_type' => 'national_id',
            'government_id_proof' => null,
            'id_verified_in_person' => '1',
        ]));

        $oldCode = $response->getSession()->get('assisted_credentials.code');
        $created = User::where('last_name', 'Reissue')->firstOrFail();

        $reissue = $this->actingAs($admin)->post(route('admin.pending-registrations.claim-code', $created));
        $reissue->assertRedirect(route('admin.pending-registrations.created'));
        $newCode = $reissue->getSession()->get('assisted_credentials.code');
        $this->assertNotSame($oldCode, $newCode);

        $audit = AuditLog::query()->where('action', 'claim_code_reissued')->latest('id')->first();
        $this->assertNotNull($audit);
        $this->assertStringNotContainsString((string) $newCode, (string) $audit->description);

        auth()->logout();
        $this->app['auth']->forgetGuards();

        $this->post(route('account.claim.store'), ['code' => $oldCode])
            ->assertSessionHasErrors('code');

        $this->post(route('account.claim.store'), ['code' => $newCode])
            ->assertRedirect(route('account.claim.setup'));
    }

    public function test_assisted_pwd_registration_still_requires_pwd_proof(): void
    {
        Storage::fake('public');
        $admin = $this->createAdminUser();
        $purok = $this->createAddress();

        $response = $this->actingAs($admin)->post(route('admin.pending-registrations.store'), $this->basePayload($purok, [
            'is_pwd' => 'yes',
            'pwd_proof' => null,
            'government_id_type' => null,
            'government_id_proof' => null,
        ]));

        $response->assertSessionHasErrors(['pwd_proof']);
    }

    public function test_staff_without_registration_access_cannot_register_a_resident(): void
    {
        $staff = $this->createResidentUser([
            'role' => User::ROLE_STAFF,
        ]);
        StaffPermission::create([
            'user_id' => $staff->id,
            'can_manage_registrations' => false,
            'can_manage_blotter' => false,
            'can_manage_announcements' => false,
            'can_manage_complaints' => false,
            'can_manage_reports' => false,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.pending-registrations.store'), [])
            ->assertForbidden();

        $resident = $this->createResidentUser();
        $resident->forceFill(['claim_code_hash' => 'pending-code'])->save();

        $this->actingAs($staff)
            ->post(route('staff.pending-registrations.claim-code', $resident))
            ->assertForbidden();
    }

    public function test_verified_in_person_resident_must_upload_id_before_the_dashboard(): void
    {
        Storage::fake('public');
        $resident = $this->createResidentUser();
        $resident->forceFill([
            'is_pwd' => false,
            'is_senior' => false,
            'id_verified_in_person' => true,
            'government_id_type' => 'national_id',
            'government_id_path' => null,
        ])->save();

        $this->actingAs($resident)
            ->get(route('resident.dashboard'))
            ->assertRedirect(route('resident.government-id.edit'));

        $this->actingAs($resident)
            ->post(route('resident.government-id.update'), [
                'government_id_type' => 'national_id',
                'government_id_proof' => UploadedFile::fake()->image('government-id.jpg', 100, 100),
            ])
            ->assertRedirect(route('resident.dashboard'));

        $resident->refresh();
        $this->assertNotNull($resident->government_id_path);
        $this->assertFalse($resident->mustUploadGovernmentId());

        $this->actingAs($resident)
            ->get(route('resident.dashboard'))
            ->assertOk();
    }

    public function test_walk_in_residents_have_their_own_list_and_name_search(): void
    {
        $admin = $this->createAdminUser();
        $online = $this->createResidentUser([
            'first_name' => 'Online',
            'last_name' => 'Public',
        ]);
        $walkIn = $this->createResidentUser([
            'first_name' => 'Counter',
            'last_name' => 'Walkin',
            'email' => 'walkin.list.' . uniqid() . '@example.com',
        ]);
        $walkIn->forceFill([
            'registered_by' => $admin->id,
            'email' => null,
        ])->save();
        $other = $this->createResidentUser([
            'first_name' => 'Other',
            'last_name' => 'Desk',
        ]);
        $other->forceFill([
            'registered_by' => $admin->id,
            'claim_code_hash' => 'still-open',
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.index', ['status' => 'registered']))
            ->assertOk()
            ->assertSee('Walkin')
            ->assertSee('Desk')
            ->assertDontSee('Public')
            ->assertSee('Search name', false);

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.index', ['status' => 'registered', 'q' => 'Counter']))
            ->assertOk()
            ->assertSee('Walkin')
            ->assertDontSee('Desk');

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.index', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Public')
            ->assertDontSee('Walkin');

        $this->assertNull($online->registered_by);

        $owing = $this->createResidentUser([
            'first_name' => 'Still',
            'last_name' => 'Owing',
            'email' => 'still.owing@example.com',
        ]);
        $owing->forceFill([
            'registered_by' => $admin->id,
            'claim_code_hash' => null,
            'is_pwd' => false,
            'is_senior' => false,
            'id_verified_in_person' => true,
            'government_id_path' => null,
        ])->save();

        $finished = $this->createResidentUser([
            'first_name' => 'Already',
            'last_name' => 'Finished',
            'email' => 'already.finished@example.com',
        ]);
        $finished->forceFill([
            'registered_by' => $admin->id,
            'claim_code_hash' => null,
            'is_pwd' => false,
            'is_senior' => false,
            'id_verified_in_person' => true,
            'government_id_path' => 'id-proofs/government/finished.jpg',
        ])->save();

        $this->actingAs($admin)
            ->get(route('admin.pending-registrations.index', ['status' => 'registered']))
            ->assertOk()
            ->assertSee('Owing')
            ->assertDontSee('Finished');

        $this->actingAs($admin)
            ->get(route('admin.residents.index'))
            ->assertOk()
            ->assertSee('Finished');
    }

    private function createAddress(): Purok
    {
        return Purok::create([
            'name' => 'Purok 1',
            'description' => 'Test purok',
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
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
        ], $overrides);
    }
}
