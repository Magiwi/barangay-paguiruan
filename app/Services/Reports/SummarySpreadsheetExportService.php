<?php

namespace App\Services\Reports;

use App\Models\CertificateRequest;
use App\Models\IssueReport;
use App\Models\Permit;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SummarySpreadsheetExportService
{
    public function __construct(
        private readonly OfficialFormSpreadsheetTheme $spreadsheetTheme
    ) {
    }

    public function generateBarangayReportExcel(?int $purokId): array
    {
        $today = Carbon::today()->toDateString();
        $purokName = $purokId ? Purok::find($purokId)?->name ?? 'Unknown' : 'All Puroks';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('e-Barangay System')->setTitle('Barangay Report')->setDescription("Generated on {$today} — Scope: {$purokName}");

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');
        $summaryHeaderRow = $this->applySheetHeader($sheet, 'Population Summary', $purokName, $today, 'B');

        $ageBase = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->whereNotNull('birthdate');
        $genderBase = User::countable()
            ->where('status', User::STATUS_APPROVED);
        $residentBase = User::countable()
            ->where('status', User::STATUS_APPROVED);
        if ($purokId) {
            $ageBase->where('purok_id', $purokId);
            $genderBase->where('purok_id', $purokId);
            $residentBase->where('purok_id', $purokId);
        }

        $summaryData = [
            ['Total Residents', (clone $residentBase)->count()],
            ['Minors (Below 18)', (clone $ageBase)->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 18", [$today])->count()],
            ['Adults (18–59)', (clone $ageBase)->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 18", [$today])->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 60", [$today])->count()],
            ['Seniors (60+)', (clone $ageBase)->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 60", [$today])->count()],
            ['Male', (clone $genderBase)->where('gender', 'male')->count()],
            ['Female', (clone $genderBase)->where('gender', 'female')->count()],
        ];

        $row = $summaryHeaderRow;
        $sheet->fromArray(['Metric', 'Value'], null, "A{$row}");
        $this->applyHeaderStyle($sheet, "A{$row}:B{$row}");
        $row++;
        foreach ($summaryData as $item) {
            $sheet->fromArray($item, null, "A{$row}");
            $row++;
        }
        $this->applyTableBorders($sheet, 'A4:B' . ($row - 1));
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Demographics by Purok');
        $demoHeaderRow = $this->applySheetHeader($sheet2, 'Per-Purok Demographics', $purokName, $today, 'G');

        $demographicsQuery = Purok::select('puroks.id', 'puroks.name')
            ->leftJoin('users', function ($join) {
                $join->on('puroks.id', '=', 'users.purok_id')
                    ->where('users.role', User::ROLE_RESIDENT)
                    ->where('users.status', User::STATUS_APPROVED);
            })
            ->groupBy('puroks.id', 'puroks.name')
            ->selectRaw("
                puroks.name,
                COUNT(users.id) as total_residents,
                SUM(CASE WHEN users.birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, users.birthdate, ?) < 18 THEN 1 ELSE 0 END) as minors,
                SUM(CASE WHEN users.birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, users.birthdate, ?) >= 18 AND TIMESTAMPDIFF(YEAR, users.birthdate, ?) < 60 THEN 1 ELSE 0 END) as adults,
                SUM(CASE WHEN users.birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, users.birthdate, ?) >= 60 THEN 1 ELSE 0 END) as seniors,
                SUM(CASE WHEN users.gender = 'male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN users.gender = 'female' THEN 1 ELSE 0 END) as female
            ", [$today, $today, $today, $today])
            ->orderBy('puroks.name');
        if ($purokId) {
            $demographicsQuery->where('puroks.id', $purokId);
        }
        $demographics = $demographicsQuery->get();

        $row = $demoHeaderRow;
        $sheet2->fromArray(['Purok', 'Total', 'Minors', 'Adults', 'Seniors', 'Male', 'Female'], null, "A{$row}");
        $this->applyHeaderStyle($sheet2, "A{$row}:G{$row}");
        $row++;
        foreach ($demographics as $d) {
            $sheet2->fromArray([$d->name, (int) $d->total_residents, (int) $d->minors, (int) $d->adults, (int) $d->seniors, (int) $d->male, (int) $d->female], null, "A{$row}");
            $row++;
        }
        $this->applyTableBorders($sheet2, 'A4:G' . ($row - 1));
        $sheet2->getColumnDimension('A')->setWidth(20);
        foreach (range('B', 'G') as $col) {
            $sheet2->getColumnDimension($col)->setWidth(12);
        }

        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Classification');
        $classHeaderRow = $this->applySheetHeader($sheet3, 'Resident Classification', $purokName, $today, 'B');

        $classBase = User::countable()
            ->where('status', User::STATUS_APPROVED);
        if ($purokId) {
            $classBase->where('purok_id', $purokId);
        }
        $classData = [
            ['PWD — Total', (clone $classBase)->where('is_pwd', true)->count()],
            ['PWD — Verified', (clone $classBase)->where('is_pwd', true)->where('pwd_status', 'verified')->count()],
            ['PWD — Pending', (clone $classBase)->where('is_pwd', true)->where('pwd_status', 'pending')->count()],
            ['Senior Citizen — Total', (clone $classBase)->where('is_senior', true)->count()],
            ['Senior Citizen — Verified', (clone $classBase)->where('is_senior', true)->where('senior_status', 'verified')->count()],
            ['Senior Citizen — Pending', (clone $classBase)->where('is_senior', true)->where('senior_status', 'pending')->count()],
        ];

        $row = $classHeaderRow;
        $sheet3->fromArray(['Category', 'Count'], null, "A{$row}");
        $this->applyHeaderStyle($sheet3, "A{$row}:B{$row}");
        $row++;
        foreach ($classData as $item) {
            $sheet3->fromArray($item, null, "A{$row}");
            $row++;
        }
        $this->applyTableBorders($sheet3, 'A4:B' . ($row - 1));
        $sheet3->getColumnDimension('A')->setWidth(30);
        $sheet3->getColumnDimension('B')->setWidth(15);

        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Services');
        $serviceHeaderRow = $this->applySheetHeader($sheet4, 'Service Statistics', $purokName, $today, 'F');

        $certBase = CertificateRequest::query();
        $issueBase = IssueReport::query();
        $permitBase = Permit::query();
        if ($purokId) {
            $certBase->whereHas('user', fn ($q) => $q->where('purok_id', $purokId));
            $issueBase->whereHas('user', fn ($q) => $q->where('purok_id', $purokId));
            $permitBase->whereHas('applicant', fn ($q) => $q->where('purok_id', $purokId));
        }

        $row = $serviceHeaderRow;
        $sheet4->fromArray(['Service', 'Pending', 'Approved', 'Released', 'Rejected', 'Total'], null, "A{$row}");
        $this->applyHeaderStyle($sheet4, "A{$row}:F{$row}");
        $row++;

        $certPending = (clone $certBase)->where('status', 'pending')->count();
        $certApproved = (clone $certBase)->where('status', 'approved')->count();
        $certReleased = (clone $certBase)->where('status', 'released')->count();
        $certRejected = (clone $certBase)->where('status', 'rejected')->count();
        $sheet4->fromArray(['Certificates', $certPending, $certApproved, $certReleased, $certRejected, $certPending + $certApproved + $certReleased + $certRejected], null, "A{$row}");
        $row++;

        $permitPending = (clone $permitBase)->where('status', 'pending')->count();
        $permitApproved = (clone $permitBase)->where('status', 'approved')->count();
        $permitReleased = (clone $permitBase)->where('status', 'released')->count();
        $permitRejected = (clone $permitBase)->where('status', 'rejected')->count();
        $sheet4->fromArray(['Permits', $permitPending, $permitApproved, $permitReleased, $permitRejected, $permitPending + $permitApproved + $permitReleased + $permitRejected], null, "A{$row}");
        $row++;

        $issuePending = (clone $issueBase)->where('status', 'pending')->count();
        $issueInProgress = (clone $issueBase)->where('status', 'in_progress')->count();
        $issueResolved = (clone $issueBase)->where('status', 'resolved')->count();
        $issueClosed = (clone $issueBase)->where('status', 'closed')->count();
        $sheet4->fromArray(['Complaints', $issuePending, $issueInProgress, $issueResolved, $issueClosed, $issuePending + $issueInProgress + $issueResolved + $issueClosed], null, "A{$row}");
        $row++;

        $this->applyTableBorders($sheet4, 'A4:F' . ($row - 1));
        $sheet4->getColumnDimension('A')->setWidth(18);
        foreach (range('B', 'F') as $col) {
            $sheet4->getColumnDimension($col)->setWidth(14);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'barangay_report_' . now()->format('Ymd') . '.xlsx';
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
