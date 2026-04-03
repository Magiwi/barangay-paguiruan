<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;

class ReportSpreadsheetExportService
{
    public function __construct(
        private readonly BlotterSpreadsheetExportService $blotterExport,
        private readonly HouseholdSpreadsheetExportService $householdExport,
        private readonly SummarySpreadsheetExportService $summaryExport
    ) {
    }

    public function buildBlotterExportRows(Request $request): array
    {
        return $this->blotterExport->buildBlotterExportRows($request);
    }

    public function generateBlotterExcel(array $records): array
    {
        return $this->blotterExport->generateBlotterExcel($records);
    }

    public function generateHouseholdsReportExcel(?int $purokId): array
    {
        return $this->householdExport->generateHouseholdsReportExcel($purokId);
    }

    public function generateBarangayReportExcel(?int $purokId): array
    {
        return $this->summaryExport->generateBarangayReportExcel($purokId);
    }
}
