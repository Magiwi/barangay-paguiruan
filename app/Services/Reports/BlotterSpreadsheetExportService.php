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
        private readonly OfficialFormSpreadsheetTheme $spreadsheetTheme
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

        $query = Blotter::withTrashed()
            ->with(['latestHearing', 'latestSummon'])
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
            switch ((string) $status) {
                case Blotter::STATUS_ACTIVE:
                    $query->where('status', Blotter::STATUS_ACTIVE)->whereNull('deleted_at');
                    break;
                case Blotter::STATUS_ARCHIVED:
                    $query->onlyTrashed()->where('status', Blotter::STATUS_ARCHIVED);
                    break;
                case 'scheduled':
                case 'ongoing':
                case 'done':
                    $query->whereNull('deleted_at')
                        ->whereHas('latestHearing', function ($q) use ($status): void {
                            $q->where('status', $status);
                        });
                    break;
                case 'settled':
                case 'not_settled':
                case 'reschedule':
                    $query->whereNull('deleted_at')
                        ->whereHas('latestHearing', function ($q) use ($status): void {
                            $q->where('status', 'done')->where('result', $status);
                        });
                    break;
                case 'no_show':
                    $query->whereNull('deleted_at')
                        ->where(function ($q): void {
                            $q->whereHas('latestHearing', function ($hq): void {
                                $hq->where('status', 'no_show');
                            })->orWhere(function ($sq): void {
                                $sq->whereDoesntHave('latestHearing')
                                    ->whereHas('latestSummon', function ($ssq): void {
                                        $ssq->where('status', 'no_show');
                                    });
                            });
                        });
                    break;
                case 'pending':
                case 'served':
                case 'completed':
                    $query->whereNull('deleted_at')
                        ->whereDoesntHave('latestHearing')
                        ->whereHas('latestSummon', function ($q) use ($status): void {
                            $q->where('status', $status);
                        });
                    break;
            }
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

        return $query->orderByDesc('created_at')->get()->map(function ($item): array {
            $statusLabel = $this->resolveReportStatusLabel($item);

            return [
                'case_number' => $item->case_number ?: $item->blotter_number,
                'complainant_name' => $item->complainant_name ?: '—',
                'respondent_name' => $item->respondent_name ?: 'N/A',
                'complaint_type' => $item->complaint_type ?: 'Others',
                'status' => $item->status ?: Blotter::STATUS_ACTIVE,
                'status_label' => $statusLabel,
                'created_at' => $item->created_at,
            ];
        })->all();
    }

    private function resolveReportStatusLabel(Blotter $blotter): string
    {
        if ($blotter->trashed() || $blotter->status === Blotter::STATUS_ARCHIVED) {
            return 'Archived';
        }

        if ($blotter->latestHearing) {
            $hearingStatus = (string) $blotter->latestHearing->status;
            $hearingResult = (string) ($blotter->latestHearing->result ?? '');

            if ($hearingStatus === 'done' && $hearingResult === 'settled') {
                return 'Settled';
            }
            if ($hearingStatus === 'done' && $hearingResult === 'not_settled') {
                return 'Not Settled';
            }
            if ($hearingStatus === 'done' && $hearingResult === 'reschedule') {
                return 'For Further Hearing';
            }
            if ($hearingStatus === 'scheduled') {
                return 'Scheduled';
            }
            if ($hearingStatus === 'ongoing') {
                return 'Ongoing';
            }
            if ($hearingStatus === 'no_show') {
                return 'No Show';
            }
            if ($hearingStatus === 'done') {
                return 'Done';
            }
        }

        if ($blotter->latestSummon) {
            return match ((string) $blotter->latestSummon->status) {
                'pending' => 'Pending',
                'served' => 'Served',
                'no_show' => 'No Show',
                'completed' => 'Completed',
                default => 'Active',
            };
        }

        return 'Active';
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
                $record['status_label'] ?? ucfirst((string) $record['status']),
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
}
