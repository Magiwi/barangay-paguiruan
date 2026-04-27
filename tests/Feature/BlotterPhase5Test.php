<?php

namespace Tests\Feature;

use App\Models\Blotter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class BlotterPhase5Test extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    private function seedBlotter(User $uploader, array $overrides = []): Blotter
    {
        return DB::transaction(function () use ($uploader, $overrides) {
            $data = array_merge([
                'complainant_name' => 'Test Complainant',
                'complainant_user_id' => null,
                'incident_date' => now()->toDateString(),
                'file_path' => 'blotters/placeholder.pdf',
                'handwritten_salaysay_path' => 'blotters/handwritten-salaysay/h.jpg',
                'remarks' => null,
            ], $overrides);

            $blotter = new Blotter($data);
            $blotter->blotter_number = Blotter::generateBlotterNumber();
            $blotter->uploaded_by = $uploader->id;
            $blotter->status = Blotter::STATUS_ACTIVE;
            $blotter->save();

            return $blotter;
        });
    }

    public function test_archived_blotter_hearings_page_resolves_without_404(): void
    {
        $admin = $this->createAdminUser();
        $blotter = $this->seedBlotter($admin);
        $id = $blotter->id;

        $blotter->forceFill(['status' => Blotter::STATUS_ARCHIVED])->save();
        $blotter->delete();

        $archived = Blotter::withTrashed()->findOrFail($id);

        $this->actingAs($admin)
            ->get(route('admin.blotters.hearings.index', $archived))
            ->assertOk();
    }

    public function test_resident_cannot_submit_request_for_archived_blotter(): void
    {
        $admin = $this->createAdminUser();
        $resident = $this->createResidentUser();
        $blotter = $this->seedBlotter($admin, ['complainant_user_id' => $resident->id]);
        $id = $blotter->id;

        $blotter->forceFill(['status' => Blotter::STATUS_ARCHIVED])->save();
        $blotter->delete();

        $this->actingAs($resident)
            ->post(route('resident.blotter-requests.store'), [
                'blotter_id' => $id,
                'purpose' => 'Need a certified copy of my blotter record for legal documentation.',
            ])
            ->assertSessionHasErrors(['blotter_id']);
    }

    public function test_admin_blotter_store_persists_optional_video(): void
    {
        $admin = $this->createAdminUser();
        $handwritten = UploadedFile::fake()->image('salaysay.jpg', 600, 800);
        $video = UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4');

        $this->actingAs($admin)
            ->post(route('admin.blotters.store'), [
                'complainant_first_name' => 'Maria',
                'complainant_last_name' => 'Santos',
                'incident_date' => now()->toDateString(),
                'handwritten_salaysay' => $handwritten,
                'video' => $video,
            ])
            ->assertRedirect(route('admin.blotters.index'));

        $blotter = Blotter::where('complainant_name', 'Maria Santos')->first();
        $this->assertNotNull($blotter);
        $this->assertNotEmpty($blotter->video_path);

        $this->actingAs($admin)
            ->get(route('admin.blotters.evidence.preview', ['blotter' => $blotter, 'type' => 'video']))
            ->assertOk();
    }

    public function test_hearing_store_rejected_for_archived_blotter(): void
    {
        $admin = $this->createAdminUser();
        $blotter = $this->seedBlotter($admin);
        $id = $blotter->id;

        $blotter->forceFill(['status' => Blotter::STATUS_ARCHIVED])->save();
        $blotter->delete();

        $archived = Blotter::withTrashed()->findOrFail($id);

        $this->actingAs($admin)
            ->post(route('admin.blotters.hearings.store', $archived), [
                'summon_id' => 1,
                'hearing_date' => now()->addDay()->toDateString(),
                'hearing_time' => '09:00',
                'lupon_user_id' => $admin->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
