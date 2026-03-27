<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdHeadAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_top_ten_head_suggestions_filtered_by_purok(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);
        $purokOne = Purok::firstOrCreate(['name' => 'Purok Auto 1']);
        $purokTwo = Purok::firstOrCreate(['name' => 'Purok Auto 2']);

        for ($i = 1; $i <= 12; $i++) {
            $this->createUser([
                'first_name' => 'Ju' . $i,
                'last_name' => 'Abarico',
                'head_of_family' => 'yes',
                'purok_id' => $purokOne->id,
            ]);
        }

        $this->createUser([
            'first_name' => 'JuOther',
            'last_name' => 'Abarico',
            'head_of_family' => 'yes',
            'purok_id' => $purokTwo->id,
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.reports.households.head-suggestions', [
            'q' => 'Abar',
            'purok' => $purokOne->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonMissing(['purok' => $purokTwo->name]);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'purok', 'house_id'],
            ],
        ]);
    }

    public function test_households_table_filters_by_selected_head(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);

        $targetHead = $this->createUser([
            'first_name' => 'Maria',
            'last_name' => 'Dela Cruz',
            'head_of_family' => 'yes',
        ]);

        $otherHead = $this->createUser([
            'first_name' => 'Ana',
            'last_name' => 'Santos',
            'head_of_family' => 'yes',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.households', [
            'head_id' => $targetHead->id,
            'head_q' => 'Dela Cruz',
        ]));

        $response->assertOk();
        $response->assertViewHas('households', function ($households) use ($targetHead, $otherHead) {
            $ids = collect($households->items())->pluck('id')->all();

            return in_array($targetHead->id, $ids, true)
                && ! in_array($otherHead->id, $ids, true)
                && $households->total() === 1;
        });
    }

    public function test_no_household_found_message_appears_for_head_search_without_matches(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);

        $this->createUser([
            'first_name' => 'Pedro',
            'last_name' => 'Velasco',
            'head_of_family' => 'yes',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.households', [
            'head_q' => 'NameThatDoesNotExist',
        ]));

        $response->assertOk();
        $response->assertSee('No household found.');
    }

    public function test_typed_head_name_without_selection_still_filters_results(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);

        $match = $this->createUser([
            'first_name' => 'Justinkim',
            'last_name' => 'Abarico',
            'head_of_family' => 'yes',
        ]);

        $other = $this->createUser([
            'first_name' => 'Christopher',
            'last_name' => 'Angoya',
            'head_of_family' => 'yes',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.households', [
            'head_q' => 'Abar',
        ]));

        $response->assertOk();
        $response->assertViewHas('households', function ($households) use ($match, $other) {
            $ids = collect($households->items())->pluck('id')->all();

            return in_array($match->id, $ids, true)
                && ! in_array($other->id, $ids, true);
        });
    }

    public function test_clearing_head_search_resets_results_based_on_remaining_filters(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);
        $purokA = Purok::firstOrCreate(['name' => 'Purok Reset A']);
        $purokB = Purok::firstOrCreate(['name' => 'Purok Reset B']);

        $headA1 = $this->createUser([
            'first_name' => 'Mario',
            'last_name' => 'Rivera',
            'head_of_family' => 'yes',
            'purok_id' => $purokA->id,
        ]);
        $headA2 = $this->createUser([
            'first_name' => 'Ana',
            'last_name' => 'Rivera',
            'head_of_family' => 'yes',
            'purok_id' => $purokA->id,
        ]);
        $headB = $this->createUser([
            'first_name' => 'Lito',
            'last_name' => 'Santos',
            'head_of_family' => 'yes',
            'purok_id' => $purokB->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.households', [
            'purok' => $purokA->id,
            'head_q' => '',
            'head_id' => '',
        ]));

        $response->assertOk();
        $response->assertViewHas('households', function ($households) use ($headA1, $headA2, $headB) {
            $ids = collect($households->items())->pluck('id')->all();

            return in_array($headA1->id, $ids, true)
                && in_array($headA2->id, $ids, true)
                && ! in_array($headB->id, $ids, true);
        });
    }

    public function test_members_sort_direction_orders_results_correctly(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);

        $headLow = $this->createUser([
            'first_name' => 'Low',
            'last_name' => 'Members',
            'head_of_family' => 'yes',
        ]);
        $headHigh = $this->createUser([
            'first_name' => 'High',
            'last_name' => 'Members',
            'head_of_family' => 'yes',
        ]);

        $householdLow = Household::create([
            'head_id' => $headLow->id,
            'purok' => $headLow->purok,
        ]);
        $householdHigh = Household::create([
            'head_id' => $headHigh->id,
            'purok' => $headHigh->purok,
        ]);

        FamilyMember::create([
            'head_user_id' => $headLow->id,
            'household_id' => $householdLow->id,
            'purok_id' => $headLow->purok_id,
            'first_name' => 'Only',
            'last_name' => 'One',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            FamilyMember::create([
                'head_user_id' => $headHigh->id,
                'household_id' => $householdHigh->id,
                'purok_id' => $headHigh->purok_id,
                'first_name' => 'Member' . $i,
                'last_name' => 'Group',
            ]);
        }

        $ascResponse = $this->actingAs($admin)->get(route('admin.reports.households', [
            'sort' => 'members',
            'direction' => 'asc',
        ]));
        $descResponse = $this->actingAs($admin)->get(route('admin.reports.households', [
            'sort' => 'members',
            'direction' => 'desc',
        ]));

        $ascResponse->assertOk();
        $descResponse->assertOk();

        $ascResponse->assertViewHas('households', function ($households) use ($headLow, $headHigh) {
            $items = collect($households->items());
            $orderedIds = $items->pluck('id')->all();

            return array_search($headLow->id, $orderedIds, true) < array_search($headHigh->id, $orderedIds, true);
        });

        $descResponse->assertViewHas('households', function ($households) use ($headLow, $headHigh) {
            $items = collect($households->items());
            $orderedIds = $items->pluck('id')->all();

            return array_search($headHigh->id, $orderedIds, true) < array_search($headLow->id, $orderedIds, true);
        });
    }

    public function test_households_filters_by_resident_type_and_status(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);

        $matching = $this->createUser([
            'first_name' => 'Match',
            'last_name' => 'Household',
            'head_of_family' => 'yes',
            'resident_type' => 'non-permanent',
            'is_suspended' => true,
        ]);

        $this->createUser([
            'first_name' => 'Different',
            'last_name' => 'Type',
            'head_of_family' => 'yes',
            'resident_type' => 'permanent',
            'is_suspended' => true,
        ]);

        $this->createUser([
            'first_name' => 'Different',
            'last_name' => 'Status',
            'head_of_family' => 'yes',
            'resident_type' => 'non-permanent',
            'is_suspended' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.households', [
            'resident_type' => 'non-permanent',
            'household_status' => 'suspended',
        ]));

        $response->assertOk();
        $response->assertViewHas('households', function ($households) use ($matching) {
            $ids = collect($households->items())->pluck('id')->all();

            return count($ids) === 1 && in_array($matching->id, $ids, true);
        });
    }

    public function test_household_exports_create_audit_logs(): void
    {
        $admin = $this->createUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ]);

        $this->createUser([
            'first_name' => 'Audit',
            'last_name' => 'Target',
            'head_of_family' => 'yes',
        ]);

        $this->actingAs($admin)->get(route('admin.reports.households.export', [
            'head_q' => 'Audit',
        ]))->assertOk();

        $this->actingAs($admin)->get(route('admin.reports.households.export.pdf', [
            'head_q' => 'Audit',
        ]))->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'report_households_export_excel',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'report_households_export_pdf',
        ]);
        $descriptions = AuditLog::query()
            ->where('user_id', $admin->id)
            ->whereIn('action', [
                'report_households_export_excel',
                'report_households_export_pdf',
            ])
            ->pluck('description')
            ->filter()
            ->implode(' | ');

        $this->assertStringContainsString('head_q=Audit', $descriptions);
    }

    private function createUser(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => 'Purok Auto Default']);

        $defaults = [
            'first_name' => 'Test',
            'middle_name' => 'M',
            'last_name' => 'Resident',
            'suffix' => null,
            'house_no' => '10',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Sample Street',
            'contact_number' => '+639171111111',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'yes',
            'resident_type' => 'permanent',
            'email' => 'user' . uniqid() . '@example.com',
            'password' => 'password123',
        ];

        $payload = array_merge($defaults, array_intersect_key($overrides, $defaults));
        $user = User::create($payload);
        $user->forceFill([
            'role' => $overrides['role'] ?? User::ROLE_RESIDENT,
            'status' => $overrides['status'] ?? User::STATUS_APPROVED,
            'is_suspended' => $overrides['is_suspended'] ?? false,
        ])->save();

        return $user->fresh();
    }
}
