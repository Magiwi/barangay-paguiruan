<?php

namespace Tests\Unit;

use App\Models\Official;
use App\Models\Position;
use App\Models\User;
use App\Support\OfficialPrint;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class OfficialPrintTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_honorific_name_uses_full_name(): void
    {
        $user = User::make([
            'first_name' => 'Juan',
            'middle_name' => 'C.',
            'last_name' => 'Cruz',
            'suffix' => null,
        ]);

        $this->assertSame('Hon. Juan C. Cruz', OfficialPrint::honorificName($user));
    }

    public function test_honorific_name_empty_falls_back(): void
    {
        $user = User::make([
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'suffix' => null,
        ]);

        $this->assertSame('Hon. ________', OfficialPrint::honorificName($user));
    }

    public function test_position_print_title_maps_chairman_to_punong_barangay(): void
    {
        $this->assertSame('Punong Barangay', OfficialPrint::positionPrintTitle('Barangay Chairman'));
        $this->assertSame('Barangay Secretary', OfficialPrint::positionPrintTitle('Barangay Secretary'));
    }

    public function test_executive_short_role_strips_barangay_prefix(): void
    {
        $this->assertSame('Secretary', OfficialPrint::executiveShortRole('Barangay Secretary'));
        $this->assertSame('Kagawad', OfficialPrint::executiveShortRole('Kagawad'));
    }

    public function test_honorific_signature_upper(): void
    {
        $user = User::make([
            'first_name' => 'Jose',
            'middle_name' => 'C.',
            'last_name' => 'Basa',
            'suffix' => null,
        ]);

        $this->assertSame('HON. JOSE C. BASA', OfficialPrint::honorificNameSignatureUpper($user));
    }

    public function test_kagawad_role_line_falls_back_when_no_committee(): void
    {
        $this->seed(PositionSeeder::class);

        $position = Position::query()->where('name', 'Kagawad')->firstOrFail();
        $user = $this->createResidentUser();

        $official = Official::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);
        $official->load('position');

        $this->assertSame('Kagawad', OfficialPrint::kagawadRoleLine($official));
    }

    public function test_committee_line_resolves_kagawad_committee(): void
    {
        $this->seed(PositionSeeder::class);

        $position = Position::query()->where('name', 'Kagawad')->firstOrFail();
        $user = $this->createResidentUser();

        $official = Official::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => 'environment',
            'photo' => null,
        ]);
        $official->load('position');

        $this->assertSame('Committee on Environment', OfficialPrint::committeeLine($official));
    }
}
