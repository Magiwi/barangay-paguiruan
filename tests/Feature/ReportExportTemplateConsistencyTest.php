<?php

namespace Tests\Feature;

use App\Models\Purok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTemplateConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_exports_generate_valid_xlsx_files_for_all_report_modules(): void
    {
        $admin = $this->createAdmin();

        $routes = [
            ['name' => 'admin.reports.population.export.excel', 'mysql_only' => true],
            ['name' => 'admin.reports.classification.export.excel'],
            ['name' => 'admin.reports.services.export.excel'],
            ['name' => 'admin.reports.households.export'],
            ['name' => 'admin.reports.households.view.export.excel'],
            ['name' => 'admin.reports.blotter.export.excel'],
            ['name' => 'admin.reports.export', 'mysql_only' => true],
        ];

        foreach ($routes as $route) {
            if (($route['mysql_only'] ?? false) && config('database.default') === 'sqlite') {
                continue;
            }

            $response = $this->actingAs($admin)->get(route($route['name']));

            $response->assertOk();
            $response->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $response->assertHeader('content-disposition');
            $this->assertStringContainsString(
                '.xlsx',
                (string) $response->headers->get('content-disposition')
            );
        }
    }

    private function createAdmin(): User
    {
        $purok = Purok::firstOrCreate(['name' => 'Purok 1']);

        $user = User::create([
            'first_name' => 'Admin',
            'middle_name' => 'A',
            'last_name' => 'User',
            'suffix' => null,
            'house_no' => '1',
            'street_name' => 'Main St',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'contact_number' => '+639171234567',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'yes',
            'resident_type' => 'permanent',
            'email' => 'admin' . uniqid() . '@example.com',
            'password' => 'password123',
            'head_of_family_id' => null,
            'family_link_status' => null,
            'relationship_to_head' => null,
            'household_id' => null,
        ]);

        $user->forceFill([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ])->save();

        return $user->fresh();
    }
}
