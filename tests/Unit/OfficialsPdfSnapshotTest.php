<?php

namespace Tests\Unit;

use App\Models\Official;
use App\Models\Position;
use App\Services\BarangayOfficialRosterService;
use App\Support\OfficialsPdfSnapshot;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class OfficialsPdfSnapshotTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_from_pdf_rosters_produces_valid_snapshot(): void
    {
        $this->seed(PositionSeeder::class);

        $chairPos = Position::query()->where('name', 'Barangay Chairman')->firstOrFail();
        $user = $this->createResidentUser(['first_name' => 'Pat', 'middle_name' => '', 'last_name' => 'Chair']);

        Official::create([
            'user_id' => $user->id,
            'position_id' => $chairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);

        $pdf = app(BarangayOfficialRosterService::class)->pdfRosters();
        $snap = OfficialsPdfSnapshot::fromPdfRosters($pdf);

        $this->assertSame(OfficialsPdfSnapshot::VERSION, $snap['v']);
        $this->assertTrue(OfficialsPdfSnapshot::isValidSnapshot($snap));
        $this->assertStringContainsString('Pat', $snap['chairman']['honorific']);
        $this->assertStringContainsString('CHAIR', $snap['signature_name_upper']);
    }

    public function test_for_print_uses_snapshot_when_valid(): void
    {
        $this->seed(PositionSeeder::class);

        $stored = [
            'v' => 1,
            'chairman' => ['honorific' => 'Hon. Frozen Name', 'role' => 'Punong Barangay'],
            'kagawads' => [],
            'sk_chairman' => null,
            'treasurer' => null,
            'secretary' => null,
            'signature_name_upper' => 'HON. FROZEN NAME',
            'signature_role' => 'Punong Barangay',
        ];

        $live = app(BarangayOfficialRosterService::class)->pdfRosters();

        $display = OfficialsPdfSnapshot::forPrint($stored, $live);

        $this->assertSame('Hon. Frozen Name', $display['chairman']['honorific']);
        $this->assertSame('HON. FROZEN NAME', $display['signature_name_upper']);
    }

    public function test_for_print_falls_back_when_snapshot_invalid(): void
    {
        $this->seed(PositionSeeder::class);

        $chairPos = Position::query()->where('name', 'Barangay Chairman')->firstOrFail();
        $user = $this->createResidentUser(['first_name' => 'Live', 'middle_name' => '', 'last_name' => 'Only']);

        Official::create([
            'user_id' => $user->id,
            'position_id' => $chairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);

        $live = app(BarangayOfficialRosterService::class)->pdfRosters();
        $display = OfficialsPdfSnapshot::forPrint(['v' => 0], $live);

        $this->assertStringContainsString('Live', $display['chairman']['honorific'] ?? '');
    }
}
