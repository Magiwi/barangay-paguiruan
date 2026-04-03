<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesResidents;
use Tests\TestCase;

class ReportExportTemplateConsistencyTest extends TestCase
{
    use CreatesResidents;
    use RefreshDatabase;

    public function test_excel_exports_generate_valid_xlsx_files_for_all_report_modules(): void
    {
        $admin = $this->createAdminUser(['head_of_family' => 'yes']);

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
}
