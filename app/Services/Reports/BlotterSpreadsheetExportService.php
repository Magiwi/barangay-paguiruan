<?php

namespace App\Services\Reports;

use App\Models\Blotter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BlotterSpreadsheetExportService
{
    public function __construct(
        private readonly OfficialFormSpreadsheetTheme $spreadsheetTheme,
        private readonly ReportCsvFormatter $csvFormatter
    ) {
    }

    public function buildBlotterExportRows(Request $request): array
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');
        $complaintType = $request->get('complaint_type');
        $search = trim((string) $request->get('search'));

        $hasCaseNumber = Schema::hasColumn('blotters', 'case_number');
        $hasRespondent = Schema::hasColumn('blotters', 'respondent_name');
        $hasComplaintType = Schema::hasColumn('blotters', 'complaint_type');

        $query = Blotter::query()
            ->select(['id', 'blotter_number', 'complainant_name', 'remarks', 'status', 'created_at']);

        $query->selectRaw($hasCaseNumber ? 'case_number' : 'blotter_number as case_number');
        $query->selectRaw($hasRespondent ? 'respondent_name' : "'—' as respondent_name");
        $query->selectRaw($hasComplaintType ? 'complaint_type' : "'Others' as complaint_type");

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($complaintType) {
            if ($hasComplaintType) {
                $query->where('complaint_type', $complaintType);
            } elseif (strtolower($complaintType) !== 'others') {
                $query->whereRaw('1 = 0');
            }
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search, $hasCaseNumber, $hasRespondent, $hasComplaintType) {
                $q->where('blotter_number', 'like', "%{$search}%")
                    ->orWhere('complainant_name', 'like', "%{$search}%");
                if ($hasCaseNumber) {
                    $q->orWhere('case_number', 'like', "%{$search}%");
                }
                if ($hasRespondent) {
                    $q->orWhere('respondent_name', 'like', "%{$search}%");
                }
                if ($hasComplaintType) {
                    $q->orWhere('complaint_type', 'like', "%{$search}%");
                }
            });
        }

        return $query->orderByDesc('created_at')->get()->map(fn ($item) => [
            'case_number' => $item->case_number ?: $item->blotter_number,
            'complainant_name' => $item->complainant_name ?: '—',
            'respondent_name' => $item->respondent_name ?: 'N/A',
            'complaint_type' => $item->complaint_type ?: 'Others',
            'status' => $item->status ?: 'pending',
            'created_at' => $item->created_at,
        ])->all();
    }

    public function generateBlotterExcel(array $records): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Blotter Report');

        $headers = ['Case Number', 'Complainant', 'Respondent', 'Complaint Type', 'Status', 'Date Filed'];
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $headerRow = $this->spreadsheetTheme->applyOfficialHeader(
            $sheet,
            'Blotter Report',
            'Barangay-wide',
            $lastColumn,
            ['Total Rows' => (string) count($records)]
        );
        $sheet->fromArray($headers, null, 'A' . $headerRow);

        $row = $headerRow + 1;
        foreach ($records as $record) {
            $sheet->fromArray([
                $record['case_number'],
                $record['complainant_name'],
                $record['respondent_name'],
                $record['complaint_type'],
                ucfirst($record['status']),
                Carbon::parse($record['created_at'])->format('M d, Y'),
            ], null, "A{$row}");
            $row++;
        }

        $lastDataRow = max($headerRow, $row - 1);
        $this->spreadsheetTheme->styleTable($sheet, $headerRow, $lastColumn, $lastDataRow);
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $finalRow = $this->spreadsheetTheme->applySignatureFooter($sheet, $lastColumn, $lastDataRow);
        $this->spreadsheetTheme->applyPageSetup($sheet, $lastColumn, $finalRow, $headerRow);

        $filename = 'blotter_report_' . now()->format('Ymd_His') . '.xlsx';
        $tempPath = storage_path("app/temp/{$filename}");
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }
        (new Xlsx($spreadsheet))->save($tempPath);

        return ['filename' => $filename, 'tempPath' => $tempPath, 'contentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    }

    public function generateBlotterCsv(array $records): array
    {
        $filename = 'blotter_report_' . now()->format('Ymd_His') . '.csv';
        $tempPath = storage_path("app/temp/{$filename}");
        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $handle = fopen($tempPath, 'w');
        $this->csvFormatter->writeOfficialCsv(
            $handle,
            'Blotter Report',
            [['Total Rows', (string) count($records)]],
            ['Case Number', 'Complainant', 'Respondent', 'Complaint Type', 'Status', 'Date Filed'],
            $records,
            fn (array $record) => [
                $record['case_number'],
                $record['complainant_name'],
                $record['respondent_name'],
                $record['complaint_type'],
                ucfirst($record['status']),
                Carbon::parse($record['created_at'])->format('M d, Y'),
            ]
        );
        fclose($handle);

        return ['filename' => $filename, 'tempPath' => $tempPath, 'contentType' => 'text/csv; charset=UTF-8'];
    }
}
