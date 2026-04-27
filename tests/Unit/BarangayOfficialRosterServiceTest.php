<?php

namespace Tests\Unit;

use App\Models\Official;
use App\Models\Position;
use App\Services\BarangayOfficialRosterService;
use Database\Seeders\PositionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class BarangayOfficialRosterServiceTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_council_roster_empty_when_no_officials(): void
    {
        $this->seed(PositionSeeder::class);

        $roster = app(BarangayOfficialRosterService::class)->councilRoster();

        $this->assertNull($roster->chairman);
        $this->assertNull($roster->secretary);
        $this->assertTrue($roster->kagawads->isEmpty());
    }

    public function test_council_roster_includes_chairman_and_kagawads(): void
    {
        $this->seed(PositionSeeder::class);

        $chairPos = Position::query()->where('name', 'Barangay Chairman')->firstOrFail();
        $kagPos = Position::query()->where('name', 'Kagawad')->firstOrFail();

        $chairUser = $this->createResidentUser(['first_name' => 'Chair', 'middle_name' => '', 'last_name' => 'Person']);
        $k1 = $this->createResidentUser(['first_name' => 'K', 'middle_name' => '', 'last_name' => 'One']);
        $k2 = $this->createResidentUser(['first_name' => 'K', 'middle_name' => '', 'last_name' => 'Two']);

        Official::create([
            'user_id' => $chairUser->id,
            'position_id' => $chairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);
        Official::create([
            'user_id' => $k1->id,
            'position_id' => $kagPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => 'environment',
            'photo' => null,
        ]);
        Official::create([
            'user_id' => $k2->id,
            'position_id' => $kagPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => 'health',
            'photo' => null,
        ]);

        $roster = app(BarangayOfficialRosterService::class)->councilRoster();

        $this->assertNotNull($roster->chairman);
        $this->assertSame('Chair Person', $roster->chairman->user->full_name);
        $this->assertCount(2, $roster->kagawads);
    }

    public function test_sk_roster_excludes_barangay_chairman(): void
    {
        $this->seed(PositionSeeder::class);

        $chairPos = Position::query()->where('name', 'Barangay Chairman')->firstOrFail();
        $skChairPos = Position::query()->where('name', 'SK Chairman')->firstOrFail();

        $chairUser = $this->createResidentUser();
        $skUser = $this->createResidentUser(['first_name' => 'Sven', 'middle_name' => '', 'last_name' => 'Lead']);

        Official::create([
            'user_id' => $chairUser->id,
            'position_id' => $chairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);
        Official::create([
            'user_id' => $skUser->id,
            'position_id' => $skChairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => 'sports_youth_development',
            'photo' => null,
        ]);

        $council = app(BarangayOfficialRosterService::class)->councilRoster();
        $sk = app(BarangayOfficialRosterService::class)->skRoster();

        $this->assertNotNull($council->chairman);

        $this->assertNotNull($sk->skChairman);
        $this->assertSame('Sven Lead', $sk->skChairman->user->full_name);
        $this->assertTrue($sk->skKagawads->isEmpty());
    }

    public function test_pdf_rosters_returns_council_and_sk_in_one_call(): void
    {
        $this->seed(PositionSeeder::class);

        $chairPos = Position::query()->where('name', 'Barangay Chairman')->firstOrFail();
        $skChairPos = Position::query()->where('name', 'SK Chairman')->firstOrFail();

        $chairUser = $this->createResidentUser(['first_name' => 'Chair', 'middle_name' => '', 'last_name' => 'One']);
        $skUser = $this->createResidentUser(['first_name' => 'Sk', 'middle_name' => '', 'last_name' => 'Two']);

        Official::create([
            'user_id' => $chairUser->id,
            'position_id' => $chairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);
        Official::create([
            'user_id' => $skUser->id,
            'position_id' => $skChairPos->id,
            'term_start' => now()->subMonth(),
            'term_end' => now()->addYear(),
            'is_active' => true,
            'committee' => 'sports_youth_development',
            'photo' => null,
        ]);

        $pdf = app(BarangayOfficialRosterService::class)->pdfRosters();

        $this->assertSame('Chair One', $pdf->council->chairman?->user->full_name);
        $this->assertSame('Sk Two', $pdf->sk->skChairman?->user->full_name);
    }

    public function test_expired_term_excluded_from_roster(): void
    {
        $this->seed(PositionSeeder::class);

        $chairPos = Position::query()->where('name', 'Barangay Chairman')->firstOrFail();
        $user = $this->createResidentUser();

        Official::create([
            'user_id' => $user->id,
            'position_id' => $chairPos->id,
            'term_start' => now()->subYears(3),
            'term_end' => now()->subYear(),
            'is_active' => true,
            'committee' => null,
            'photo' => null,
        ]);

        $roster = app(BarangayOfficialRosterService::class)->councilRoster();

        $this->assertNull($roster->chairman);
    }
}
