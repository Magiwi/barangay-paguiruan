<?php

namespace Tests\Feature;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\Purok;
use App\Models\StaffPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class HouseholdReportWaveBTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_household_view_and_print_routes_render_for_admin(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);

        [$head] = $this->seedHouseholdDetailData();

        $query = [
            'head_id' => $head->id,
            'head_q' => $head->last_name . ', ' . $head->first_name,
        ];

        $viewResponse = $this->actingAs($admin)
            ->get(route('admin.reports.households.view', $query));

        $viewResponse->assertOk();
        $viewResponse->assertSeeText('Garcia, Pedro');
        $viewResponse->assertSeeText('Export PDF');
        $viewResponse->assertSeeText('Export Excel');
        $viewResponse->assertSeeText('Print Preview');
        $viewResponse->assertSeeText('Garcia, Pedro');
        $viewResponse->assertSeeText('Maria Santos');

        $printResponse = $this->actingAs($admin)
            ->get(route('admin.reports.households.view.print', $query));

        $printResponse->assertOk();
        $printResponse->assertSeeText('Total Records');
        $printResponse->assertSeeText('Household Heads');
        $printResponse->assertSeeText('Garcia, Pedro');
    }

    public function test_household_view_pdf_and_excel_exports_follow_filters_and_sort(): void
    {
        $admin = $this->createResidentUser([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'yes',
            'first_name' => 'Admin',
            'last_name' => 'User',
        ]);

        [$head] = $this->seedHouseholdDetailData();

        $query = [
            'head_id' => $head->id,
            'head_q' => $head->last_name . ', ' . $head->first_name,
            'view_sort' => 'relationship',
            'view_order' => 'desc',
        ];

        $pdfResponse = $this->actingAs($admin)
            ->get(route('admin.reports.households.view.export.pdf', $query));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $pdfResponse->assertHeader('content-disposition');
        $this->assertStringContainsString('.pdf', (string) $pdfResponse->headers->get('content-disposition'));

        $excelResponse = $this->actingAs($admin)
            ->get(route('admin.reports.households.view.export.excel', $query));

        $excelResponse->assertOk();
        $excelResponse->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        $excelResponse->assertHeader('content-disposition');
        $this->assertStringContainsString('.xlsx', (string) $excelResponse->headers->get('content-disposition'));
    }

    public function test_guest_is_redirected_from_admin_and_staff_household_view_routes(): void
    {
        $this->seedHouseholdDetailData();

        $this->get(route('admin.reports.households.view'))
            ->assertRedirect(route('login'));

        $this->get(route('staff.reports.households.view'))
            ->assertRedirect(route('login'));
    }

    public function test_resident_role_cannot_access_admin_or_staff_household_view_routes(): void
    {
        $resident = $this->createResidentUser([
            'role' => User::ROLE_RESIDENT,
            'head_of_family' => 'no',
        ]);

        $this->actingAs($resident)
            ->get(route('admin.reports.households.view'))
            ->assertForbidden();

        $this->actingAs($resident)
            ->get(route('staff.reports.households.view'))
            ->assertForbidden();
    }

    public function test_staff_without_reports_module_cannot_access_staff_household_endpoints(): void
    {
        $staff = $this->createResidentUser([
            'role' => User::ROLE_STAFF,
            'head_of_family' => 'yes',
        ]);

        StaffPermission::create([
            'user_id' => $staff->id,
            'can_manage_registrations' => false,
            'can_manage_blotter' => false,
            'can_manage_announcements' => false,
            'can_manage_complaints' => false,
            'can_manage_reports' => false,
        ]);

        [$head] = $this->seedHouseholdDetailData();
        $query = [
            'head_id' => $head->id,
            'head_q' => $head->last_name . ', ' . $head->first_name,
        ];

        $this->actingAs($staff)
            ->get(route('staff.reports.households.view', $query))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('staff.reports.households.view.print', $query))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('staff.reports.households.view.export.pdf', $query))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('staff.reports.households.view.export.excel', $query))
            ->assertForbidden();
    }

    public function test_staff_with_reports_module_can_access_staff_household_endpoints(): void
    {
        $staff = $this->createResidentUser([
            'role' => User::ROLE_STAFF,
            'head_of_family' => 'yes',
        ]);

        StaffPermission::create([
            'user_id' => $staff->id,
            'can_manage_registrations' => false,
            'can_manage_blotter' => false,
            'can_manage_announcements' => false,
            'can_manage_complaints' => false,
            'can_manage_reports' => true,
        ]);

        [$head] = $this->seedHouseholdDetailData();
        $query = [
            'head_id' => $head->id,
            'head_q' => $head->last_name . ', ' . $head->first_name,
        ];

        $this->actingAs($staff)
            ->get(route('staff.reports.households.view', $query))
            ->assertOk()
            ->assertSeeText('Export PDF');

        $this->actingAs($staff)
            ->get(route('staff.reports.households.view.print', $query))
            ->assertOk()
            ->assertSeeText('Household Heads');
    }

    private function seedHouseholdDetailData(): array
    {
        $purok = Purok::firstOrCreate(['name' => 'Purok 1']);
        $head = $this->createResidentUser([
            'head_of_family' => 'yes',
            'first_name' => 'Pedro',
            'last_name' => 'Garcia',
            'purok_id' => $purok->id,
            'purok' => $purok->name,
            'house_no' => '22',
            'street_name' => 'Rizal St',
        ]);

        $household = Household::create([
            'head_id' => $head->id,
            'purok' => $head->purok,
        ]);

        FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'purok_id' => $purok->id,
            'first_name' => 'Mario',
            'middle_name' => null,
            'last_name' => 'Santos',
            'suffix' => null,
            'birthdate' => now()->subYears(21)->toDateString(),
            'age' => 21,
            'gender' => 'male',
            'relationship_to_head' => 'Brother',
            'house_no' => '22',
            'street_name' => 'Rizal St',
            'purok' => $purok->name,
            'resident_type' => 'permanent',
        ]);

        FamilyMember::create([
            'head_user_id' => $head->id,
            'household_id' => $household->id,
            'purok_id' => $purok->id,
            'first_name' => 'Maria',
            'middle_name' => null,
            'last_name' => 'Santos',
            'suffix' => null,
            'birthdate' => now()->subYears(19)->toDateString(),
            'age' => 19,
            'gender' => 'female',
            'relationship_to_head' => 'Aunt',
            'house_no' => '22',
            'street_name' => 'Rizal St',
            'purok' => $purok->name,
            'resident_type' => 'permanent',
        ]);

        return [$head, $household];
    }

}
