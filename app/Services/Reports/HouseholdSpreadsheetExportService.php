<?php

namespace App\Services\Reports;

use App\Models\FamilyMember;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HouseholdSpreadsheetExportService
{
    public function __construct(
        private readonly OfficialFormSpreadsheetTheme $spreadsheetTheme
    ) {
    }

    public function generateHouseholdsReportExcel(?int $purokId): array
    {
        $today = Carbon::today()->toDateString();
        $purokName = $purokId ? Purok::find($purokId)?->name ?? 'Unknown' : 'All Puroks';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('e-Barangay System')->setTitle('Household Report')->setDescription("Generated on {$today} — Scope: {$purokName}");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Household Summary');
        $summaryHeaderRow = $this->applySheetHeader($sheet, 'Household Summary by Purok', $purokName, $today, 'D');

        $row = $summaryHeaderRow;
        $sheet->fromArray(['Purok', 'Households', 'Total Residents', 'Avg Size'], null, "A{$row}");
        $this->applyHeaderStyle($sheet, "A{$row}:D{$row}");
        $row++;

        $purokData = Purok::select('id', 'name')
            ->when($purokId, fn ($q) => $q->where('puroks.id', $purokId))
            ->orderBy('puroks.name')
            ->get();

        $headCountsByPurok = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->when($purokId, fn ($q) => $q->where('purok_id', $purokId))
            ->selectRaw('purok_id, COUNT(*) as total')
            ->groupBy('purok_id')
            ->pluck('total', 'purok_id');

        $memberCountsByPurok = FamilyMember::query()
            ->when($purokId, fn ($q) => $q->where('purok_id', $purokId))
            ->selectRaw('purok_id, COUNT(*) as total')
            ->groupBy('purok_id')
            ->pluck('total', 'purok_id');

        foreach ($purokData as $d) {
            $headCount = (int) ($headCountsByPurok[$d->id] ?? 0);
            $residentCount = $headCount + (int) ($memberCountsByPurok[$d->id] ?? 0);
            $householdCount = $headCount;
            $avg = $householdCount > 0 ? round($residentCount / $householdCount, 1) : 0;
            $sheet->fromArray([$d->name, $householdCount, $residentCount, $avg], null, "A{$row}");
            $row++;
        }
        $this->applyTableBorders($sheet, 'A4:D' . ($row - 1));
        $sheet->getColumnDimension('A')->setWidth(22);
        foreach (['B', 'C', 'D'] as $c) {
            $sheet->getColumnDimension($c)->setWidth(16);
        }

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Household Members');
        $detailHeaderRow = $this->applySheetHeader($sheet2, 'Household Members Detail', $purokName, $today, 'E');

        $row = $detailHeaderRow;
        $sheet2->fromArray(['Head of Family', 'Purok', 'Member Count', 'Resident Type', 'Status'], null, "A{$row}");
        $this->applyHeaderStyle($sheet2, "A{$row}:E{$row}");
        $row++;

        $allHouseholds = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->when($purokId, fn ($q) => $q->where('purok_id', $purokId))
            ->withCount(['familyMemberRecords as family_members_count'])
            ->with('purokRelation')
            ->orderBy('last_name')
            ->get();

        foreach ($allHouseholds as $hh) {
            $memberCount = $hh->family_members_count + 1;
            $sheet2->fromArray([
                $hh->last_name . ', ' . $hh->first_name . ($hh->middle_name ? ' ' . $hh->middle_name : ''),
                $hh->purokRelation?->name ?? '—',
                $memberCount,
                ucfirst($hh->resident_type ?? '—'),
                $hh->is_suspended ? 'Suspended' : ucfirst($hh->status ?? '—'),
            ], null, "A{$row}");
            $row++;
        }
        $this->applyTableBorders($sheet2, 'A4:E' . ($row - 1));
        $sheet2->getColumnDimension('A')->setWidth(30);
        $sheet2->getColumnDimension('B')->setWidth(18);
        $sheet2->getColumnDimension('C')->setWidth(16);
        $sheet2->getColumnDimension('D')->setWidth(16);
        $sheet2->getColumnDimension('E')->setWidth(14);

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'household_report_' . now()->format('Ymd') . '.xlsx';
        $tempPath = storage_path("app/private/{$filename}");
        (new Xlsx($spreadsheet))->save($tempPath);
        $spreadsheet->disconnectWorksheets();

        return ['filename' => $filename, 'tempPath' => $tempPath, 'contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    }

    private function applySheetHeader($sheet, string $title, string $scope, string $date, string $lastColumn): int
    {
        $generated = Carbon::parse($date)->format('F d, Y');
        return $this->spreadsheetTheme->applyOfficialHeader(
            $sheet,
            $title,
            $scope,
            $lastColumn,
            ['Generated' => $generated],
            []
        );
    }

    private function applyHeaderStyle($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '374151'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E4E9F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
    }

    private function applyTableBorders($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
    }
}
