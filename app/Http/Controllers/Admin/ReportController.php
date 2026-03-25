<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\CertificateRequest;
use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\IssueReport;
use App\Models\Permit;
use App\Models\Purok;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PopulationService;
use App\Services\Reports\HouseholdReportService;
use App\Services\Reports\OfficialFormSpreadsheetTheme;
use App\Services\Reports\ReportCsvFormatter;
use App\Services\Reports\ReportSpreadsheetExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportSpreadsheetExportService $spreadsheetExportService,
        private readonly HouseholdReportService $householdReportService,
        private readonly OfficialFormSpreadsheetTheme $spreadsheetTheme,
        private readonly ReportCsvFormatter $csvFormatter,
        private readonly PopulationService $populationService
    ) {
    }

    /**
     * Display the main reports dashboard with summary statistics.
     */
    public function index()
    {
        $registrationBase = User::countable();

        $totalHouseholds = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->count();
        $populationBase = $this->populationPeopleBaseQuery();
        $totalResidents = $this->populationService->getTotalResidents();
        $avgApprovalHours = (clone $registrationBase)
            ->where('status', User::STATUS_APPROVED)
            ->whereColumn('updated_at', '>', 'created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours');

        $stats = [
            'total_residents' => $totalResidents,
            'approved_residents' => User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->where('is_suspended', false)
                ->count(),
            'total_pwd' => User::countable()->where('status', User::STATUS_APPROVED)->where('is_pwd', true)->count(),
            'total_senior' => User::countable()->where('status', User::STATUS_APPROVED)->where('is_senior', true)->count(),
            'pending_certificates' => CertificateRequest::where('status', 'pending')->count(),
            'released_certificates' => CertificateRequest::where('status', 'released')->count(),
            'pending_issues' => IssueReport::where('status', 'pending')->count(),
            'pending_permits' => Permit::where('status', 'pending')->count(),
            'total_permits' => Permit::count(),
            'pending_registrations' => (clone $registrationBase)->where('status', User::STATUS_PENDING)->count(),
            'rejected_registrations' => (clone $registrationBase)->where('status', User::STATUS_REJECTED)->count(),
            'approved_today_registrations' => (clone $registrationBase)
                ->where('status', User::STATUS_APPROVED)
                ->whereDate('updated_at', Carbon::today())
                ->count(),
            'avg_approval_hours' => round((float) ($avgApprovalHours ?? 0), 1),
            'total_households' => $totalHouseholds,
            'avg_household_size' => $totalHouseholds > 0
                ? round($totalResidents / $totalHouseholds, 1)
                : 0,
        ];

        return view('admin.reports.index', compact('stats'));
    }

    /**
     * Display population reports with optional purok filter.
     */
    public function population(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $purokInput = (string) $request->query('purok', 'all');
        $purokId = ctype_digit($purokInput) ? (int) $purokInput : null;
        $ageRange = $this->normalizePopulationAgeRange((string) $request->query('age_range', 'all'));
        $gender = $this->normalizePopulationGender((string) $request->query('gender', 'all'));
        $allPuroks = Purok::orderBy('name')->get();
        if ($purokId !== null && ! $allPuroks->contains('id', $purokId)) {
            $purokId = null;
        }
        $puroks = $purokId
            ? Purok::where('id', $purokId)->orderBy('name')->get()
            : Purok::orderBy('name')->get();

        $populationBase = $this->populationPeopleBaseQuery($purokId);
        $this->applyPopulationBaseFilters($populationBase, $today, $ageRange, $gender);

        $demographicsRows = (clone $populationBase)
            ->selectRaw('purok_id, COUNT(*) as total_residents')
            ->selectRaw("SUM(CASE WHEN birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthdate, ?) < 18 THEN 1 ELSE 0 END) as minors", [$today])
            ->selectRaw("SUM(CASE WHEN birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthdate, ?) >= 18 AND TIMESTAMPDIFF(YEAR, birthdate, ?) < 60 THEN 1 ELSE 0 END) as adults", [$today, $today])
            ->selectRaw("SUM(CASE WHEN birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthdate, ?) >= 60 THEN 1 ELSE 0 END) as seniors", [$today])
            ->selectRaw("SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male")
            ->selectRaw("SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female")
            ->whereNotNull('purok_id')
            ->groupBy('purok_id')
            ->get()
            ->keyBy(fn ($row) => (int) $row->purok_id);

        // Total residents per purok (includes approved users + non-linked family members).
        $residentsPerPurok = $puroks->map(function (Purok $purok) use ($demographicsRows) {
            $row = $demographicsRows->get((int) $purok->id);

            return (object) [
                'id' => (int) $purok->id,
                'name' => (string) $purok->name,
                'residents_count' => (int) ($row->total_residents ?? 0),
            ];
        });

        // Permanent vs Non-permanent per purok from unified population source.
        $residentTypePerPurok = (clone $populationBase)
            ->selectRaw('purok_id, resident_type, COUNT(*) as total')
            ->whereNotNull('purok_id')
            ->groupBy('purok_id', 'resident_type')
            ->get()
            ->groupBy(fn ($row) => (int) $row->purok_id);

        // Active vs Inactive totals
        $activeQuery = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false);

        $inactiveQuery = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', true);

        $this->applyPopulationUserFilters($activeQuery, $today, $ageRange, $gender);
        $this->applyPopulationUserFilters($inactiveQuery, $today, $ageRange, $gender);

        if ($purokId) {
            $activeQuery->where('purok_id', $purokId);
            $inactiveQuery->where('purok_id', $purokId);
        }

        $activeCount = $activeQuery->count();
        $inactiveCount = $inactiveQuery->count();

        // Active per purok
        $activePerPurokQuery = User::select('purok_id', DB::raw('COUNT(*) as total'))
            ->countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false)
            ->whereNotNull('purok_id');
        $this->applyPopulationUserFilters($activePerPurokQuery, $today, $ageRange, $gender);

        if ($purokId) {
            $activePerPurokQuery->where('purok_id', $purokId);
        }

        $activePerPurok = $activePerPurokQuery->groupBy('purok_id')->pluck('total', 'purok_id');

        // Age breakdown from unified population source.
        $ageBase = (clone $populationBase)->whereNotNull('birthdate');
        $ageStats = [
            'minors' => (clone $ageBase)
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 18", [$today])
                ->count(),
            'adults' => (clone $ageBase)
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 18", [$today])
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 60", [$today])
                ->count(),
            'seniors' => (clone $ageBase)
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 60", [$today])
                ->count(),
        ];

        // Gender breakdown from unified population source.
        $genderBase = clone $populationBase;
        $genderStats = [
            'male' => (clone $genderBase)->where('gender', 'male')->count(),
            'female' => (clone $genderBase)->where('gender', 'female')->count(),
        ];

        // Per-purok demographics (age + gender), preserving empty puroks.
        $demographicsPerPurok = $puroks->map(function (Purok $purok) use ($demographicsRows) {
            $row = $demographicsRows->get((int) $purok->id);

            return (object) [
                'id' => (int) $purok->id,
                'name' => (string) $purok->name,
                'total_residents' => (int) ($row->total_residents ?? 0),
                'minors' => (int) ($row->minors ?? 0),
                'adults' => (int) ($row->adults ?? 0),
                'seniors' => (int) ($row->seniors ?? 0),
                'male' => (int) ($row->male ?? 0),
                'female' => (int) ($row->female ?? 0),
            ];
        });

        $purokLabel = $this->resolvePurokLabel($purokId);
        $ageRangeLabel = $this->resolvePopulationAgeRangeLabel($ageRange);
        $genderLabel = $this->resolvePopulationGenderLabel($gender);
        $activePopulationFilterLabel = "Purok: {$purokLabel} | Age: {$ageRangeLabel} | Gender: {$genderLabel}";

        return view('admin.reports.population', compact(
            'residentsPerPurok',
            'residentTypePerPurok',
            'activeCount',
            'inactiveCount',
            'activePerPurok',
            'ageStats',
            'genderStats',
            'demographicsPerPurok',
            'puroks',
            'allPuroks',
            'purokId',
            'ageRange',
            'gender',
            'activePopulationFilterLabel'
        ));
    }

    /**
     * Display classification reports (PWD and Senior) with optional purok filter.
     */
    public function classification()
    {
        $purokId = request('purok');
        $allPuroks = Purok::orderBy('name')->get();

        $residentBase = User::countable()
            ->where('status', User::STATUS_APPROVED);
        if ($purokId) {
            $residentBase->where('purok_id', $purokId);
        }

        $totals = [
            'pwd' => (clone $residentBase)->where('is_pwd', true)->count(),
            'pwd_verified' => (clone $residentBase)->where('is_pwd', true)->where('pwd_status', 'verified')->count(),
            'pwd_pending' => (clone $residentBase)->where('is_pwd', true)->where('pwd_status', 'pending')->count(),
            'senior' => (clone $residentBase)->where('is_senior', true)->count(),
            'senior_verified' => (clone $residentBase)->where('is_senior', true)->where('senior_status', 'verified')->count(),
            'senior_pending' => (clone $residentBase)->where('is_senior', true)->where('senior_status', 'pending')->count(),
        ];

        // Classification breakdown per purok (LEFT JOIN preserves empty puroks)
        $classificationQuery = Purok::select('puroks.id', 'puroks.name')
            ->leftJoin('users', function ($join) {
                $join->on('puroks.id', '=', 'users.purok_id')
                     ->where('users.role', '!=', User::ROLE_SUPER_ADMIN)
                     ->where('users.status', User::STATUS_APPROVED);
            })
            ->groupBy('puroks.id', 'puroks.name')
            ->selectRaw("
                puroks.id,
                puroks.name,
                SUM(CASE WHEN users.is_pwd = 1 THEN 1 ELSE 0 END) as pwd_count,
                SUM(CASE WHEN users.is_senior = 1 THEN 1 ELSE 0 END) as senior_count
            ")
            ->orderBy('puroks.name');

        if ($purokId) {
            $classificationQuery->where('puroks.id', $purokId);
        }

        $classificationPerPurok = $classificationQuery->get();

        return view('admin.reports.classification', compact(
            'totals',
            'classificationPerPurok',
            'allPuroks',
            'purokId'
        ));
    }

    /**
     * Display service reports (Certificates, Permits & Issues) with optional purok filter.
     */
    public function services()
    {
        $purokId = request('purok');
        $allPuroks = Purok::orderBy('name')->get();

        // Certificate statistics (filter via user's purok)
        $certBase = CertificateRequest::query();
        if ($purokId) {
            $certBase->whereHas('user', fn ($q) => $q->where('purok_id', $purokId));
        }

        $certificateStats = [
            'total' => (clone $certBase)->count(),
            'pending' => (clone $certBase)->where('status', 'pending')->count(),
            'approved' => (clone $certBase)->where('status', 'approved')->count(),
            'released' => (clone $certBase)->where('status', 'released')->count(),
            'rejected' => (clone $certBase)->where('status', 'rejected')->count(),
        ];

        // Certificate count by type
        $certByTypeQuery = CertificateRequest::select('certificate_type', DB::raw('COUNT(*) as total'));
        if ($purokId) {
            $certByTypeQuery->whereHas('user', fn ($q) => $q->where('purok_id', $purokId));
        }
        $certificatesByType = $certByTypeQuery
            ->groupBy('certificate_type')
            ->orderByDesc('total')
            ->get();

        // Issue statistics (filter via purok_id directly on issue_reports)
        $issueBase = IssueReport::query();
        if ($purokId) {
            $issueBase->whereHas('user', fn ($q) => $q->where('purok_id', $purokId));
        }

        $issueStats = [
            'total' => (clone $issueBase)->count(),
            'pending' => (clone $issueBase)->where('status', 'pending')->count(),
            'in_progress' => (clone $issueBase)->where('status', 'in_progress')->count(),
            'resolved' => (clone $issueBase)->where('status', 'resolved')->count(),
            'closed' => (clone $issueBase)->where('status', 'closed')->count(),
        ];

        // Permit statistics (filter via applicant's purok)
        $permitBase = Permit::query();
        if ($purokId) {
            $permitBase->whereHas('applicant', fn ($q) => $q->where('purok_id', $purokId));
        }

        $permitStats = [
            'total' => (clone $permitBase)->count(),
            'pending' => (clone $permitBase)->where('status', 'pending')->count(),
            'approved' => (clone $permitBase)->where('status', 'approved')->count(),
            'rejected' => (clone $permitBase)->where('status', 'rejected')->count(),
            'released' => (clone $permitBase)->where('status', 'released')->count(),
        ];

        // Permit count by type
        $permitByTypeQuery = Permit::select('permit_type', DB::raw('COUNT(*) as total'));
        if ($purokId) {
            $permitByTypeQuery->whereHas('applicant', fn ($q) => $q->where('purok_id', $purokId));
        }
        $permitsByType = $permitByTypeQuery
            ->groupBy('permit_type')
            ->orderByDesc('total')
            ->get();

        // Permits per purok (LEFT JOIN preserves empty puroks)
        $permitsPerPurokQuery = Purok::select('puroks.id', 'puroks.name')
            ->leftJoin('users', function ($join) {
                $join->on('puroks.id', '=', 'users.purok_id')
                     ->where('users.role', '!=', User::ROLE_SUPER_ADMIN)
                     ->where('users.status', User::STATUS_APPROVED);
            })
            ->leftJoin('permits', 'users.id', '=', 'permits.user_id')
            ->groupBy('puroks.id', 'puroks.name')
            ->selectRaw('puroks.id, puroks.name, COUNT(permits.id) as permits_count')
            ->orderBy('puroks.name');

        if ($purokId) {
            $permitsPerPurokQuery->where('puroks.id', $purokId);
        }

        $permitsPerPurok = $permitsPerPurokQuery->get();

        return view('admin.reports.services', compact(
            'certificateStats',
            'certificatesByType',
            'issueStats',
            'permitStats',
            'permitsByType',
            'permitsPerPurok',
            'allPuroks',
            'purokId'
        ));
    }

    /**
     * Export population report as PDF.
     */
    public function populationExportPdf(Request $request)
    {
        $records = $this->buildPopulationExportRows($request);
        $purokInput = (string) $request->query('purok', 'all');
        $purokId = ctype_digit($purokInput) ? (int) $purokInput : null;
        $ageRange = $this->normalizePopulationAgeRange((string) $request->query('age_range', 'all'));
        $gender = $this->normalizePopulationGender((string) $request->query('gender', 'all'));

        $pdf = Pdf::loadView('admin.reports.population-pdf', [
            'records' => $records,
            'filters' => [
                'purok' => $this->resolvePurokLabel($purokId),
                'age_range' => $this->resolvePopulationAgeRangeLabel($ageRange),
                'gender' => $this->resolvePopulationGenderLabel($gender),
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('population_report_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export population report as CSV.
     */
    public function populationExportCsv(Request $request)
    {
        $records = $this->buildPopulationExportRows($request);
        $filename = 'population_report_' . now()->format('Ymd_His') . '.csv';
        $purokInput = (string) $request->query('purok', 'all');
        $purokId = ctype_digit($purokInput) ? (int) $purokInput : null;
        $ageRange = $this->normalizePopulationAgeRange((string) $request->query('age_range', 'all'));
        $gender = $this->normalizePopulationGender((string) $request->query('gender', 'all'));

        return $this->streamOfficialCsvResponse(
            $filename,
            'Population Report',
            [
                ['Purok', $this->resolvePurokLabel($purokId)],
                ['Age Range', $this->resolvePopulationAgeRangeLabel($ageRange)],
                ['Gender', $this->resolvePopulationGenderLabel($gender)],
                ['Total Rows', (string) count($records)],
            ],
            ['Purok', 'Total Residents', 'Active Residents', 'Permanent', 'Non-Permanent', 'Minors', 'Adults', 'Seniors', 'Male', 'Female'],
            $records,
            fn (array $record) => [
                $record['purok'],
                $record['total_residents'],
                $record['active_residents'],
                $record['permanent'],
                $record['non_permanent'],
                $record['minors'],
                $record['adults'],
                $record['seniors'],
                $record['male'],
                $record['female'],
            ]
        );
    }

    /**
     * Export population report as XLSX.
     */
    public function populationExportExcel(Request $request)
    {
        $records = $this->buildPopulationExportRows($request);
        $purokInput = (string) $request->query('purok', 'all');
        $purokId = ctype_digit($purokInput) ? (int) $purokInput : null;
        $ageRange = $this->normalizePopulationAgeRange((string) $request->query('age_range', 'all'));
        $gender = $this->normalizePopulationGender((string) $request->query('gender', 'all'));
        $scopeLabel = 'Purok: ' . $this->resolvePurokLabel($purokId)
            . ' | Age: ' . $this->resolvePopulationAgeRangeLabel($ageRange)
            . ' | Gender: ' . $this->resolvePopulationGenderLabel($gender);

        return $this->createOfficialExcelResponse(
            filename: 'population_report_' . now()->format('Ymd_His') . '.xlsx',
            sheetName: 'Population Report',
            reportTitle: 'Population Report',
            scope: $scopeLabel,
            headers: ['Purok', 'Total Residents', 'Active Residents', 'Permanent', 'Non-Permanent', 'Minors', 'Adults', 'Seniors', 'Male', 'Female'],
            rows: $records,
            rowMapper: fn (array $record) => [
                $record['purok'],
                $record['total_residents'],
                $record['active_residents'],
                $record['permanent'],
                $record['non_permanent'],
                $record['minors'],
                $record['adults'],
                $record['seniors'],
                $record['male'],
                $record['female'],
            ],
            leftMeta: ['Total Rows' => (string) count($records)]
        );
    }

    /**
     * Export classification report as PDF.
     */
    public function classificationExportPdf(Request $request)
    {
        $records = $this->buildClassificationExportRows($request);
        $purokLabel = $this->resolvePurokLabel($request->get('purok'));

        $pdf = Pdf::loadView('admin.reports.classification-pdf', [
            'records' => $records,
            'filters' => [
                'purok' => $purokLabel,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('classification_report_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export classification report as CSV.
     */
    public function classificationExportCsv(Request $request)
    {
        $records = $this->buildClassificationExportRows($request);
        $filename = 'classification_report_' . now()->format('Ymd_His') . '.csv';
        $purokLabel = $this->resolvePurokLabel($request->get('purok'));

        return $this->streamOfficialCsvResponse(
            $filename,
            'Classification Report',
            [
                ['Scope', $purokLabel],
                ['Total Rows', (string) count($records)],
            ],
            ['Purok', 'PWD Total', 'PWD Verified', 'PWD Pending', 'Senior Total', 'Senior Verified', 'Senior Pending'],
            $records,
            fn (array $record) => [
                $record['purok'],
                $record['pwd_total'],
                $record['pwd_verified'],
                $record['pwd_pending'],
                $record['senior_total'],
                $record['senior_verified'],
                $record['senior_pending'],
            ]
        );
    }

    /**
     * Export classification report as XLSX.
     */
    public function classificationExportExcel(Request $request)
    {
        $records = $this->buildClassificationExportRows($request);
        $purokLabel = $this->resolvePurokLabel($request->get('purok'));
        return $this->createOfficialExcelResponse(
            filename: 'classification_report_' . now()->format('Ymd_His') . '.xlsx',
            sheetName: 'Classification Report',
            reportTitle: 'Classification Report',
            scope: $purokLabel,
            headers: ['Purok', 'PWD Total', 'PWD Verified', 'PWD Pending', 'Senior Total', 'Senior Verified', 'Senior Pending'],
            rows: $records,
            rowMapper: fn (array $record) => [
                $record['purok'],
                $record['pwd_total'],
                $record['pwd_verified'],
                $record['pwd_pending'],
                $record['senior_total'],
                $record['senior_verified'],
                $record['senior_pending'],
            ],
            leftMeta: ['Total Rows' => (string) count($records)]
        );
    }

    /**
     * Export services report as PDF.
     */
    public function servicesExportPdf(Request $request)
    {
        $records = $this->buildServicesExportRows($request);
        $purokLabel = $this->resolvePurokLabel($request->get('purok'));

        $pdf = Pdf::loadView('admin.reports.services-pdf', [
            'records' => $records,
            'filters' => [
                'purok' => $purokLabel,
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('services_report_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export services report as CSV.
     */
    public function servicesExportCsv(Request $request)
    {
        $records = $this->buildServicesExportRows($request);
        $filename = 'services_report_' . now()->format('Ymd_His') . '.csv';
        $purokLabel = $this->resolvePurokLabel($request->get('purok'));

        return $this->streamOfficialCsvResponse(
            $filename,
            'Services Report',
            [
                ['Scope', $purokLabel],
                ['Total Rows', (string) count($records)],
            ],
            ['Purok', 'Certificates Total', 'Certificates Pending', 'Permits Total', 'Permits Pending', 'Issues Total', 'Issues Pending', 'Issues In Progress', 'Issues Resolved', 'Issues Closed'],
            $records,
            fn (array $record) => [
                $record['purok'],
                $record['cert_total'],
                $record['cert_pending'],
                $record['permit_total'],
                $record['permit_pending'],
                $record['issue_total'],
                $record['issue_pending'],
                $record['issue_in_progress'],
                $record['issue_resolved'],
                $record['issue_closed'],
            ]
        );
    }

    /**
     * Export services report as XLSX.
     */
    public function servicesExportExcel(Request $request)
    {
        $records = $this->buildServicesExportRows($request);
        $purokLabel = $this->resolvePurokLabel($request->get('purok'));
        return $this->createOfficialExcelResponse(
            filename: 'services_report_' . now()->format('Ymd_His') . '.xlsx',
            sheetName: 'Services Report',
            reportTitle: 'Services Report',
            scope: $purokLabel,
            headers: ['Purok', 'Certificates Total', 'Certificates Pending', 'Permits Total', 'Permits Pending', 'Issues Total', 'Issues Pending', 'Issues In Progress', 'Issues Resolved', 'Issues Closed'],
            rows: $records,
            rowMapper: fn (array $record) => [
                $record['purok'],
                $record['cert_total'],
                $record['cert_pending'],
                $record['permit_total'],
                $record['permit_pending'],
                $record['issue_total'],
                $record['issue_pending'],
                $record['issue_in_progress'],
                $record['issue_resolved'],
                $record['issue_closed'],
            ],
            leftMeta: ['Total Rows' => (string) count($records)]
        );
    }

    /**
     * Build population export rows based on current filter.
     */
    private function buildPopulationExportRows(Request $request): array
    {
        $purokInput = (string) $request->query('purok', 'all');
        $purokId = ctype_digit($purokInput) ? (int) $purokInput : null;
        $ageRange = $this->normalizePopulationAgeRange((string) $request->query('age_range', 'all'));
        $gender = $this->normalizePopulationGender((string) $request->query('gender', 'all'));
        $today = Carbon::today()->toDateString();

        $puroks = Purok::query()
            ->when($purokId, fn ($q) => $q->where('id', $purokId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $populationBase = $this->populationPeopleBaseQuery($purokId);
        $this->applyPopulationBaseFilters($populationBase, $today, $ageRange, $gender);

        $demographicsByPurok = (clone $populationBase)
            ->selectRaw('purok_id, COUNT(*) as total_residents')
            ->selectRaw("SUM(CASE WHEN birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthdate, ?) < 18 THEN 1 ELSE 0 END) as minors", [$today])
            ->selectRaw("SUM(CASE WHEN birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthdate, ?) >= 18 AND TIMESTAMPDIFF(YEAR, birthdate, ?) < 60 THEN 1 ELSE 0 END) as adults", [$today, $today])
            ->selectRaw("SUM(CASE WHEN birthdate IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthdate, ?) >= 60 THEN 1 ELSE 0 END) as seniors", [$today])
            ->selectRaw("SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male")
            ->selectRaw("SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female")
            ->selectRaw("SUM(CASE WHEN resident_type = 'permanent' THEN 1 ELSE 0 END) as permanent_count")
            ->selectRaw("SUM(CASE WHEN resident_type = 'non-permanent' THEN 1 ELSE 0 END) as non_permanent_count")
            ->whereNotNull('purok_id')
            ->groupBy('purok_id')
            ->get()
            ->keyBy(fn ($row) => (int) $row->purok_id);

        $activeResidentsByPurokQuery = User::query()
            ->countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('is_suspended', false)
            ->whereNotNull('purok_id')
            ->when($purokId, fn ($q) => $q->where('purok_id', $purokId));
        $this->applyPopulationUserFilters($activeResidentsByPurokQuery, $today, $ageRange, $gender);
        $activeResidentsByPurok = $activeResidentsByPurokQuery
            ->groupBy('purok_id')
            ->selectRaw('purok_id, COUNT(*) as total')
            ->pluck('total', 'purok_id');

        return $puroks->map(function (Purok $purok) use ($demographicsByPurok, $activeResidentsByPurok) {
            $row = $demographicsByPurok->get((int) $purok->id);

            return [
                'purok' => (string) $purok->name,
                'total_residents' => (int) ($row->total_residents ?? 0),
                'active_residents' => (int) ($activeResidentsByPurok[(int) $purok->id] ?? 0),
                'permanent' => (int) ($row->permanent_count ?? 0),
                'non_permanent' => (int) ($row->non_permanent_count ?? 0),
                'minors' => (int) ($row->minors ?? 0),
                'adults' => (int) ($row->adults ?? 0),
                'seniors' => (int) ($row->seniors ?? 0),
                'male' => (int) ($row->male ?? 0),
                'female' => (int) ($row->female ?? 0),
            ];
        })->all();
    }

    /**
     * Population base source for reports:
     * - approved user accounts (excluding super admin)
     * - non-linked family member records (to avoid duplicate linked users)
     */
    private function populationPeopleBaseQuery(?int $purokId = null): \Illuminate\Database\Query\Builder
    {
        return $this->populationService->populationPeopleBaseQuery($purokId);
    }

    private function normalizePopulationAgeRange(string $ageRange): string
    {
        return in_array($ageRange, ['all', 'minors', 'adults', 'seniors'], true)
            ? $ageRange
            : 'all';
    }

    private function normalizePopulationGender(string $gender): string
    {
        return in_array($gender, ['all', 'male', 'female'], true)
            ? $gender
            : 'all';
    }

    private function applyPopulationBaseFilters(\Illuminate\Database\Query\Builder $query, string $today, string $ageRange, string $gender): void
    {
        if ($ageRange === 'minors') {
            $query->whereNotNull('birthdate')->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 18", [$today]);
        } elseif ($ageRange === 'adults') {
            $query->whereNotNull('birthdate')
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 18", [$today])
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 60", [$today]);
        } elseif ($ageRange === 'seniors') {
            $query->whereNotNull('birthdate')->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 60", [$today]);
        }

        if ($gender !== 'all') {
            $query->where('gender', $gender);
        }
    }

    private function applyPopulationUserFilters(Builder $query, string $today, string $ageRange, string $gender): void
    {
        if ($ageRange === 'minors') {
            $query->whereNotNull('birthdate')->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 18", [$today]);
        } elseif ($ageRange === 'adults') {
            $query->whereNotNull('birthdate')
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 18", [$today])
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) < 60", [$today]);
        } elseif ($ageRange === 'seniors') {
            $query->whereNotNull('birthdate')->whereRaw("TIMESTAMPDIFF(YEAR, birthdate, ?) >= 60", [$today]);
        }

        if ($gender !== 'all') {
            $query->where('gender', $gender);
        }
    }

    private function resolvePopulationAgeRangeLabel(string $ageRange): string
    {
        return match ($ageRange) {
            'minors' => 'Minors (0-17)',
            'adults' => 'Adults (18-59)',
            'seniors' => 'Seniors (60+)',
            default => 'All Ages',
        };
    }

    private function resolvePopulationGenderLabel(string $gender): string
    {
        return match ($gender) {
            'male' => 'Male',
            'female' => 'Female',
            default => 'All',
        };
    }

    /**
     * Build classification export rows based on current filter.
     */
    private function buildClassificationExportRows(Request $request): array
    {
        $purokId = $request->get('purok');

        $rows = Purok::query()
            ->select('puroks.id', 'puroks.name')
            ->leftJoin('users', function ($join) {
                $join->on('puroks.id', '=', 'users.purok_id')
                    ->where('users.role', '!=', User::ROLE_SUPER_ADMIN)
                    ->where('users.status', User::STATUS_APPROVED);
            })
            ->when($purokId, fn ($q) => $q->where('puroks.id', $purokId))
            ->groupBy('puroks.id', 'puroks.name')
            ->selectRaw("
                puroks.id,
                puroks.name,
                SUM(CASE WHEN users.is_pwd = 1 THEN 1 ELSE 0 END) as pwd_total,
                SUM(CASE WHEN users.is_pwd = 1 AND users.pwd_status = 'verified' THEN 1 ELSE 0 END) as pwd_verified,
                SUM(CASE WHEN users.is_pwd = 1 AND users.pwd_status = 'pending' THEN 1 ELSE 0 END) as pwd_pending,
                SUM(CASE WHEN users.is_senior = 1 THEN 1 ELSE 0 END) as senior_total,
                SUM(CASE WHEN users.is_senior = 1 AND users.senior_status = 'verified' THEN 1 ELSE 0 END) as senior_verified,
                SUM(CASE WHEN users.is_senior = 1 AND users.senior_status = 'pending' THEN 1 ELSE 0 END) as senior_pending
            ")
            ->orderBy('puroks.name')
            ->get();

        return $rows->map(function ($row) {
            return [
                'purok' => (string) $row->name,
                'pwd_total' => (int) $row->pwd_total,
                'pwd_verified' => (int) $row->pwd_verified,
                'pwd_pending' => (int) $row->pwd_pending,
                'senior_total' => (int) $row->senior_total,
                'senior_verified' => (int) $row->senior_verified,
                'senior_pending' => (int) $row->senior_pending,
            ];
        })->all();
    }

    /**
     * Build services export rows based on current filter.
     */
    private function buildServicesExportRows(Request $request): array
    {
        $purokId = $request->get('purok');
        $puroks = Purok::query()
            ->when($purokId, fn ($q) => $q->where('id', $purokId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return $puroks->map(function ($purok) {
            $certBase = CertificateRequest::whereHas('user', fn ($q) => $q->where('purok_id', $purok->id));
            $permitBase = Permit::whereHas('applicant', fn ($q) => $q->where('purok_id', $purok->id));
            $issueBase = IssueReport::whereHas('user', fn ($q) => $q->where('purok_id', $purok->id));

            return [
                'purok' => (string) $purok->name,
                'cert_total' => (int) (clone $certBase)->count(),
                'cert_pending' => (int) (clone $certBase)->where('status', 'pending')->count(),
                'permit_total' => (int) (clone $permitBase)->count(),
                'permit_pending' => (int) (clone $permitBase)->where('status', 'pending')->count(),
                'issue_total' => (int) (clone $issueBase)->count(),
                'issue_pending' => (int) (clone $issueBase)->where('status', 'pending')->count(),
                'issue_in_progress' => (int) (clone $issueBase)->where('status', 'in_progress')->count(),
                'issue_resolved' => (int) (clone $issueBase)->where('status', 'resolved')->count(),
                'issue_closed' => (int) (clone $issueBase)->where('status', 'closed')->count(),
            ];
        })->all();
    }

    /**
     * Resolve purok label from current filter.
     */
    private function resolvePurokLabel($purokId): string
    {
        if (! $purokId) {
            return 'All Puroks';
        }

        return (string) (Purok::where('id', $purokId)->value('name') ?: 'Unknown Purok');
    }

    /**
     * Display blotter reports and analytics with filters.
     */
    public function blotter(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');
        $complaintType = $request->get('complaint_type');
        $search = trim((string) $request->get('search'));
        $sort = $request->get('sort', 'created_at');
        $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $hasCaseNumber = Schema::hasColumn('blotters', 'case_number');
        $hasRespondent = Schema::hasColumn('blotters', 'respondent_name');
        $hasComplaintType = Schema::hasColumn('blotters', 'complaint_type');

        $applyFilters = function ($query, bool $withSearch = false) use (
            $from,
            $to,
            $status,
            $complaintType,
            $search,
            $hasCaseNumber,
            $hasRespondent,
            $hasComplaintType
        ) {
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

            if ($withSearch && $search !== '') {
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
        };

        $buildQuery = function (bool $withSearch = false) use (
            $hasCaseNumber,
            $hasRespondent,
            $hasComplaintType,
            $applyFilters
        ) {
            $query = Blotter::query()
                ->select([
                    'id',
                    'blotter_number',
                    'complainant_name',
                    'remarks',
                    'status',
                    'created_at',
                ]);

            $query->selectRaw($hasCaseNumber ? 'case_number' : 'blotter_number as case_number');
            $query->selectRaw($hasRespondent ? 'respondent_name' : "'—' as respondent_name");
            $query->selectRaw($hasComplaintType ? 'complaint_type' : "'Others' as complaint_type");
            $applyFilters($query, $withSearch);

            return $query;
        };

        $summaryBase = Blotter::query();
        $applyFilters($summaryBase, false);
        $totalCases = (clone $summaryBase)->count();
        $pendingCases = (clone $summaryBase)->where('status', 'pending')->count();
        $ongoingCases = (clone $summaryBase)->where('status', 'ongoing')->count();
        $resolvedCases = (clone $summaryBase)->where('status', 'resolved')->count();

        $monthlyMap = (clone $summaryBase)
            ->selectRaw('MONTH(created_at) as month_no, COUNT(*) as total')
            ->groupBy('month_no')
            ->pluck('total', 'month_no');
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlySeries = collect(range(1, 12))
            ->map(fn ($m) => (int) ($monthlyMap[$m] ?? 0))
            ->all();

        $statusDistribution = [
            'pending' => $pendingCases,
            'ongoing' => $ongoingCases,
            'resolved' => $resolvedCases,
        ];

        if ($hasComplaintType) {
            $typeMap = (clone $summaryBase)
                ->selectRaw('LOWER(TRIM(complaint_type)) as type_key, COUNT(*) as total')
                ->groupBy('type_key')
                ->pluck('total', 'type_key');
            $known = ['noise', 'physical', 'financial', 'property'];
            $knownTotal = 0;
            foreach ($known as $k) {
                $knownTotal += (int) ($typeMap[$k] ?? 0);
            }
            $complaintCategories = [
                'noise' => (int) ($typeMap['noise'] ?? 0),
                'physical' => (int) ($typeMap['physical'] ?? 0),
                'financial' => (int) ($typeMap['financial'] ?? 0),
                'property' => (int) ($typeMap['property'] ?? 0),
                'others' => max($totalCases - $knownTotal, 0),
            ];
        } else {
            $complaintCategories = [
                'noise' => 0,
                'physical' => 0,
                'financial' => 0,
                'property' => 0,
                'others' => $totalCases,
            ];
        }

        $sortable = ['case_number', 'complainant_name', 'respondent_name', 'complaint_type', 'status', 'created_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'created_at';
        }

        $rows = $buildQuery(true)
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $complaintTypeOptions = $hasComplaintType
            ? Blotter::query()
                ->whereNotNull('complaint_type')
                ->where('complaint_type', '!=', '')
                ->distinct()
                ->orderBy('complaint_type')
                ->pluck('complaint_type')
                ->values()
            : collect(['Others']);

        return view('admin.reports.blotter', compact(
            'rows',
            'totalCases',
            'pendingCases',
            'ongoingCases',
            'resolvedCases',
            'monthLabels',
            'monthlySeries',
            'statusDistribution',
            'complaintCategories',
            'complaintTypeOptions',
            'hasComplaintType'
        ));
    }

    /**
     * Export blotter report as PDF.
     */
    public function blotterExportPdf(Request $request)
    {
        $records = $this->spreadsheetExportService->buildBlotterExportRows($request);

        $pdf = Pdf::loadView('admin.reports.blotter-pdf', [
            'records' => $records,
            'filters' => [
                'from' => $request->get('from'),
                'to' => $request->get('to'),
                'status' => $request->get('status'),
                'complaint_type' => $request->get('complaint_type'),
                'search' => $request->get('search'),
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('blotter_report_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export blotter report as XLSX.
     */
    public function blotterExportExcel(Request $request)
    {
        $records = $this->spreadsheetExportService->buildBlotterExportRows($request);
        $file = $this->spreadsheetExportService->generateBlotterExcel($records);

        return response()->download($file['tempPath'], $file['filename'], [
            'Content-Type' => $file['contentType'],
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export blotter report as CSV.
     */
    public function blotterExportCsv(Request $request)
    {
        $records = $this->spreadsheetExportService->buildBlotterExportRows($request);
        $file = $this->spreadsheetExportService->generateBlotterCsv($records);

        return response()->download($file['tempPath'], $file['filename'], [
            'Content-Type' => $file['contentType'],
        ])->deleteFileAfterSend(true);
    }

    /**
     * Display household reports with optional purok filter.
     */
    public function households(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $data = $this->householdReportService->buildDashboardData($filters);

        return view('admin.reports.households', $data);
    }

    public function householdHeadSuggestions(Request $request): JsonResponse
    {
        $this->authorizeReportsAccess($request);
        $queryText = (string) $request->query('q', '');
        $purokId = $request->filled('purok') ? (int) $request->query('purok') : null;
        $data = $this->householdReportService->buildHouseholdHeadSuggestions($queryText, $purokId);

        return response()->json(['data' => $data]);
    }

    public function householdsView(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $data = $this->householdReportService->buildDetailViewData($filters, $request);

        return view('admin.reports.households-view', $data);
    }

    public function householdsViewPrint(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $data = $this->householdReportService->buildDetailPrintData($filters, $request);
        $data['groupedRows'] = $this->groupHouseholdRows($data['rows']);

        return view('admin.reports.households-view-print', $data);
    }

    public function householdsViewExportCsv(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $data = $this->householdReportService->buildDetailPrintData($filters, $request);
        $rows = $data['rows'];
        $groupedRows = $this->groupHouseholdRows($rows);
        $exportRows = $this->flattenGroupedHouseholdRows($groupedRows);
        $filename = 'household_view_report_' . now()->format('Ymd_His') . '.csv';
        $sortLabel = strtoupper($data['viewOrder']) . ' ' . str_replace('_', ' ', ucfirst($data['viewSort']));
        $filtersLabel = ! empty($data['appliedFilters'])
            ? implode(' | ', $data['appliedFilters'])
            : 'No additional filters';

        AuditService::log(
            'report_households_view_export_csv',
            null,
            $this->buildHouseholdExportAuditDescription('csv-view', array_merge($filters->toArray(), [
                'view_sort' => $data['viewSort'],
                'view_order' => $data['viewOrder'],
            ]))
        );

        return $this->streamOfficialCsvResponse(
            $filename,
            'Household Reports',
            [
                ['Generated', $data['generatedAt'] ?? now()->format('M d, Y h:i A')],
                ['Report Type', $data['reportType'] ?? 'Household View'],
                ['Scope', $data['reportScope'] ?? 'Household'],
                ['Sort', $sortLabel],
                ['Total Records', (string) number_format((int) ($data['detailTotalRecords'] ?? 0))],
                ['Household Heads', (string) number_format((int) ($data['householdHeadsCount'] ?? 0))],
                ['Filters', $filtersLabel],
            ],
            ['House Head', 'Family Member', 'Relationship', 'House No.'],
            $exportRows,
            fn (array $row) => [
                $row['house_head'],
                $row['family_member'],
                $row['relationship'],
                $row['house_no'],
            ]
        );
    }

    public function householdsViewExportPdf(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $data = $this->householdReportService->buildDetailPrintData($filters, $request);
        $data['groupedRows'] = $this->groupHouseholdRows($data['rows']);

        $pdf = Pdf::loadView('admin.reports.households-view-pdf', $data)
            ->setPaper('a4', 'landscape');

        AuditService::log(
            'report_households_view_export_pdf',
            null,
            $this->buildHouseholdExportAuditDescription('pdf-view', array_merge($filters->toArray(), [
                'view_sort' => $data['viewSort'],
                'view_order' => $data['viewOrder'],
            ]))
        );

        return $pdf->download('household_view_report_' . now()->format('Ymd_His') . '.pdf');
    }

    public function householdsViewExportExcel(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $data = $this->householdReportService->buildDetailPrintData($filters, $request);
        $rows = $data['rows'];
        $groupedRows = $this->groupHouseholdRows($rows);
        $exportRows = $this->flattenGroupedHouseholdRows($groupedRows);
        $sortLabel = strtoupper($data['viewOrder']) . ' ' . str_replace('_', ' ', ucfirst($data['viewSort']));
        $filtersLabel = ! empty($data['appliedFilters'])
            ? implode(' | ', $data['appliedFilters'])
            : 'No additional filters';

        $filename = 'household_view_report_' . now()->format('Ymd_His') . '.xlsx';

        AuditService::log(
            'report_households_view_export_excel',
            null,
            $this->buildHouseholdExportAuditDescription('excel-view', array_merge($filters->toArray(), [
                'view_sort' => $data['viewSort'],
                'view_order' => $data['viewOrder'],
            ]))
        );

        return $this->createOfficialExcelResponse(
            filename: $filename,
            sheetName: 'Household View',
            reportTitle: 'Household Reports',
            scope: $data['reportScope'] ?? 'Household',
            headers: ['House Head', 'Family Member', 'Relationship', 'House No.'],
            rows: $exportRows,
            rowMapper: fn (array $row) => [
                $row['house_head'],
                $row['family_member'],
                $row['relationship'],
                $row['house_no'],
            ],
            leftMeta: [
                'Report Type' => $data['reportType'] ?? 'Household View',
                'Generated' => $data['generatedAt'] ?? now()->format('M d, Y h:i A'),
                'Total Records' => number_format((int) ($data['detailTotalRecords'] ?? 0)),
                'Filters' => $filtersLabel,
            ],
            rightMeta: [
                'Sort' => $sortLabel,
                'Household Heads' => number_format((int) ($data['householdHeadsCount'] ?? 0)),
            ],
            includeSignature: true
        );
    }

    private function formatHeadSuggestionName(User $user): string
    {
        $givenNames = trim(implode(' ', array_filter([$user->first_name, $user->middle_name])));
        $suffix = trim((string) ($user->suffix ?? ''));

        $name = trim($user->last_name . ', ' . $givenNames);
        if ($suffix !== '') {
            $name .= ' ' . $suffix;
        }

        return $name;
    }

    private function applyHouseholdHeadSearch(Builder $query, string $keyword): Builder
    {
        $trimmed = trim($keyword);
        if ($trimmed === '') {
            return $query;
        }

        $tokens = array_values(array_filter(preg_split('/[\s,]+/', $trimmed) ?: []));
        $primaryToken = $tokens[0] ?? $trimmed;

        $query->where(function (Builder $nameQuery) use ($primaryToken) {
            $prefix = $primaryToken . '%';
            $nameQuery
                ->where('last_name', 'like', $prefix)
                ->orWhere('first_name', 'like', $prefix);
        });

        foreach ($tokens as $token) {
            $like = '%' . $token . '%';
            $query->where(function (Builder $tokenQuery) use ($like) {
                $tokenQuery
                    ->where('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('suffix', 'like', $like);
            });
        }

        return $query;
    }

    /**
     * Normalize and validate household report filter inputs.
     */
    private function resolveHouseholdFilters(Request $request): array
    {
        $allowedSorts = ['head_name', 'purok', 'members', 'resident_type', 'status', 'created_at'];
        $purokId = $request->filled('purok') ? (int) $request->query('purok') : null;
        if ($purokId !== null && ! Purok::whereKey($purokId)->exists()) {
            $purokId = null;
        }

        $filters = [
            'purokId' => $purokId,
            'sort' => in_array((string) $request->query('sort', 'head_name'), $allowedSorts, true)
                ? (string) $request->query('sort', 'head_name')
                : 'head_name',
            'direction' => strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc',
            'selectedHeadId' => $request->filled('head_id') ? (int) $request->query('head_id') : null,
            'headQuery' => trim((string) $request->query('head_q', '')),
            'residentType' => in_array((string) $request->query('resident_type', ''), ['permanent', 'non-permanent'], true)
                ? (string) $request->query('resident_type')
                : null,
            'statusFilter' => in_array((string) $request->query('household_status', ''), ['active', 'suspended'], true)
                ? (string) $request->query('household_status')
                : null,
            'membersMin' => $request->filled('members_min') ? max((int) $request->query('members_min'), 1) : null,
            'membersMax' => $request->filled('members_max') ? max((int) $request->query('members_max'), 1) : null,
            'createdFrom' => $this->normalizeHouseholdDate($request->query('created_from')),
            'createdTo' => $this->normalizeHouseholdDate($request->query('created_to')),
        ];

        if ($filters['membersMin'] !== null && $filters['membersMax'] !== null && $filters['membersMax'] < $filters['membersMin']) {
            [$filters['membersMin'], $filters['membersMax']] = [$filters['membersMax'], $filters['membersMin']];
        }

        if ($filters['selectedHeadId'] !== null) {
            $selectedHeadExists = User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->where('head_of_family', 'yes')
                ->when($filters['purokId'], fn ($q) => $q->where('purok_id', $filters['purokId']))
                ->whereKey($filters['selectedHeadId'])
                ->exists();

            if (! $selectedHeadExists) {
                $filters['selectedHeadId'] = null;
            }
        }

        return $filters;
    }

    /**
     * Build the shared household heads query used by table and exports.
     */
    private function buildHouseholdHeadsQuery(array $filters): Builder
    {
        $query = User::countable()
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->withCount(['familyMemberRecords as family_members_count']);

        $this->applyHouseholdCommonFilters($query, $filters);
        $this->applyHouseholdSorting($query, $filters);

        return $query;
    }

    /**
     * Apply common household filters on a base query.
     */
    private function applyHouseholdCommonFilters(Builder $query, array $filters): Builder
    {
        $memberTotalSql = '(SELECT COUNT(*) FROM family_members WHERE family_members.head_user_id = users.id AND family_members.deleted_at IS NULL) + 1';

        $query
            ->when($filters['purokId'], fn ($q) => $q->where('purok_id', $filters['purokId']))
            ->when($filters['selectedHeadId'], fn ($q) => $q->where('id', $filters['selectedHeadId']))
            ->when(! $filters['selectedHeadId'] && $filters['headQuery'] !== '', fn ($q) => $this->applyHouseholdHeadSearch($q, $filters['headQuery']))
            ->when($filters['residentType'], fn ($q) => $q->where('resident_type', $filters['residentType']))
            ->when($filters['statusFilter'] === 'active', fn ($q) => $q->where('is_suspended', false))
            ->when($filters['statusFilter'] === 'suspended', fn ($q) => $q->where('is_suspended', true))
            ->when($filters['createdFrom'], fn ($q) => $q->whereDate('created_at', '>=', $filters['createdFrom']))
            ->when($filters['createdTo'], fn ($q) => $q->whereDate('created_at', '<=', $filters['createdTo']))
            ->when($filters['membersMin'] !== null, fn ($q) => $q->whereRaw($memberTotalSql . ' >= ?', [$filters['membersMin']]))
            ->when($filters['membersMax'] !== null, fn ($q) => $q->whereRaw($memberTotalSql . ' <= ?', [$filters['membersMax']]));

        return $query;
    }

    /**
     * Apply reusable sort rules for household queries.
     */
    private function applyHouseholdSorting(Builder $query, array $filters): Builder
    {
        $sort = $filters['sort'] ?? 'head_name';
        $direction = $filters['direction'] ?? 'asc';

        return $query
            ->when($sort === 'head_name', fn ($q) => $q->orderBy('last_name', $direction)->orderBy('first_name', $direction))
            ->when($sort === 'purok', fn ($q) => $q->orderBy('purok', $direction))
            ->when($sort === 'members', fn ($q) => $q->orderBy('family_members_count', $direction))
            ->when($sort === 'resident_type', fn ($q) => $q->orderBy('resident_type', $direction))
            ->when($sort === 'status', fn ($q) => $q->orderBy('is_suspended', $direction)->orderBy('status', $direction))
            ->when($sort === 'created_at', fn ($q) => $q->orderBy('created_at', $direction))
            ->when(! in_array($sort, ['head_name', 'purok', 'members', 'resident_type', 'status', 'created_at'], true), fn ($q) => $q->orderBy('last_name')->orderBy('first_name'));
    }

    /**
     * Compute and cache heavy household aggregate panels/charts.
     */
    private function getHouseholdAggregateSnapshot(array $filters): array
    {
        $cacheKey = 'reports.households.aggregate.' . md5(json_encode([
            'purok' => $filters['purokId'],
            'head' => $filters['selectedHeadId'],
            'head_q' => $filters['headQuery'],
            'resident_type' => $filters['residentType'],
            'status' => $filters['statusFilter'],
            'members_min' => $filters['membersMin'],
            'members_max' => $filters['membersMax'],
            'from' => $filters['createdFrom'],
            'to' => $filters['createdTo'],
        ], JSON_THROW_ON_ERROR));

        return Cache::remember($cacheKey, now()->addSeconds(45), function () use ($filters) {
            $baseQuery = User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->where('head_of_family', 'yes')
                ->withCount(['familyMemberRecords as family_members_count']);

            $this->applyHouseholdCommonFilters($baseQuery, $filters);

            $heads = (clone $baseQuery)->get(['id', 'purok_id', 'last_name', 'first_name', 'is_suspended']);
            $totalHouseholds = $heads->count();
            $totalMembers = $heads->sum(fn ($head) => (int) $head->family_members_count + 1);
            $avgSize = $totalHouseholds > 0 ? round($totalMembers / $totalHouseholds, 1) : 0;

            $largest = $heads->sortByDesc('family_members_count')->first();
            $smallest = $heads->sortBy('family_members_count')->first();

            $sizeBands = [
                '1-2 Members' => 0,
                '3-4 Members' => 0,
                '5-6 Members' => 0,
                '7+ Members' => 0,
            ];
            foreach ($heads as $head) {
                $size = (int) $head->family_members_count + 1;
                if ($size <= 2) {
                    $sizeBands['1-2 Members']++;
                } elseif ($size <= 4) {
                    $sizeBands['3-4 Members']++;
                } elseif ($size <= 6) {
                    $sizeBands['5-6 Members']++;
                } else {
                    $sizeBands['7+ Members']++;
                }
            }

            $statusDistribution = [
                'active' => $heads->filter(fn ($head) => ! (bool) $head->is_suspended)->count(),
                'pending' => 0,
                'suspended' => $heads->filter(fn ($head) => (bool) $head->is_suspended)->count(),
            ];

            $headCountsByPurok = $heads
                ->groupBy('purok_id')
                ->map(fn ($rows) => $rows->count());

            $headIds = $heads->pluck('id');
            $memberCountsByPurok = FamilyMember::query()
                ->whereIn('head_user_id', $headIds)
                ->selectRaw('purok_id, COUNT(*) as total')
                ->groupBy('purok_id')
                ->pluck('total', 'purok_id');

            $householdsPerPurok = Purok::query()
                ->when($filters['purokId'], fn ($q) => $q->where('id', $filters['purokId']))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($purok) => [
                    'id' => $purok->id,
                    'name' => $purok->name,
                    'household_count' => (int) ($headCountsByPurok[$purok->id] ?? 0),
                ])
                ->values()
                ->all();

            $householdMembers = collect($householdsPerPurok)->map(fn ($row) => [
                'id' => $row['id'],
                'name' => $row['name'],
                'member_count' => (int) (($headCountsByPurok[$row['id']] ?? 0) + ($memberCountsByPurok[$row['id']] ?? 0)),
            ])->values()->all();

            return [
                'totalHouseholds' => $totalHouseholds,
                'totalMembers' => $totalMembers,
                'avgSize' => $avgSize,
                'largestId' => $largest?->id,
                'smallestId' => $smallest?->id,
                'sizeBands' => $sizeBands,
                'statusDistribution' => $statusDistribution,
                'householdsPerPurok' => $householdsPerPurok,
                'householdMembers' => $householdMembers,
            ];
        });
    }

    /**
     * Display household timeline based on family member audit logs.
     */
    public function householdsTimeline(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $actions = $this->householdTimelineActions();
        $query = $this->buildHouseholdTimelineQuery($request);
        $logs = (clone $query)->latest()->paginate(20)->withQueryString();

        $heads = User::query()
            ->where('role', '!=', User::ROLE_SUPER_ADMIN)
            ->where('status', User::STATUS_APPROVED)
            ->where('head_of_family', 'yes')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        $summary = [
            'added' => (clone $query)->where('action', 'family_member_added')->count(),
            'updated' => (clone $query)->where('action', 'family_member_updated')->count(),
            'removed' => (clone $query)->where('action', 'family_member_removed')->count(),
            'linked' => (clone $query)->where('action', 'family_member_linked_existing')->count(),
        ];

        return view('admin.reports.households-timeline', compact('logs', 'heads', 'summary', 'actions'));
    }

    /**
     * Export household timeline as PDF.
     */
    public function householdsTimelineExportPdf(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $records = $this->buildHouseholdTimelineRows($request);

        $pdf = Pdf::loadView('admin.reports.households-timeline-pdf', [
            'records' => $records,
            'filters' => [
                'head_id' => $request->get('head_id'),
                'action' => $request->get('action'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'search' => $request->get('search'),
            ],
        ])->setPaper('a4', 'portrait');

        return $pdf->download('household_timeline_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export household timeline as CSV.
     */
    public function householdsTimelineExportCsv(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $records = $this->buildHouseholdTimelineRows($request);
        $filename = 'household_timeline_' . now()->format('Ymd_His') . '.csv';

        return $this->streamOfficialCsvResponse(
            $filename,
            'Household Timeline Report',
            [
                ['Date From', (string) ($request->get('date_from') ?: 'All')],
                ['Date To', (string) ($request->get('date_to') ?: 'All')],
                ['Action', (string) ($request->get('action') ?: 'All')],
                ['Total Rows', (string) count($records)],
            ],
            ['Date', 'Action', 'Performed By', 'Description'],
            $records,
            fn (array $record) => [
                $record['date'],
                $record['action'],
                $record['performed_by'],
                $record['description'],
            ]
        );
    }

    /**
     * Get supported household timeline action keys.
     */
    private function householdTimelineActions(): array
    {
        return [
            'family_member_added',
            'family_member_updated',
            'family_member_removed',
            'family_member_linked_existing',
        ];
    }

    /**
     * Build household timeline query with filters.
     */
    private function buildHouseholdTimelineQuery(Request $request)
    {
        $query = AuditLog::with('user')
            ->whereIn('action', $this->householdTimelineActions());

        if ($headId = $request->get('head_id')) {
            $query->where('target_type', 'User')->where('target_id', $headId);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Build export-ready timeline rows based on current filters.
     */
    private function buildHouseholdTimelineRows(Request $request): array
    {
        $actionLabels = [
            'family_member_added' => 'Member Added',
            'family_member_updated' => 'Member Updated',
            'family_member_removed' => 'Member Removed',
            'family_member_linked_existing' => 'Existing Resident Linked',
        ];

        return $this->buildHouseholdTimelineQuery($request)
            ->latest()
            ->get()
            ->map(function ($log) use ($actionLabels) {
                return [
                    'date' => optional($log->created_at)->format('M d, Y h:i A'),
                    'action' => $actionLabels[$log->action] ?? $log->action,
                    'performed_by' => $log->user?->full_name ?? 'System',
                    'description' => $log->description ?: 'No description provided.',
                ];
            })
            ->all();
    }

    /**
     * Build household export rows based on current filter.
     */
    private function buildHouseholdExportRows(Request $request): array
    {
        $filters = $this->householdReportService->resolveFilters($request);

        return $this->householdReportService->buildExportRows($filters);
    }

    /**
     * Export household data as a multi-sheet Excel file.
     */
    public function householdsExport(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $records = $this->buildHouseholdExportRows($request);
        $filename = 'household_report_' . now()->format('Ymd_His') . '.xlsx';
        $excelRows = collect($records)->values()->map(function (array $record, int $index): array {
            $membersList = collect($record['members_list'] ?? [])
                ->map(fn ($name) => mb_strtoupper((string) $name))
                ->filter(fn ($name) => trim($name) !== '')
                ->values();

            return [
                '#' => $index + 1,
                'head_of_family' => mb_strtoupper((string) ($record['head_name'] ?? '')),
                'email' => (string) ($record['email'] ?? '—'),
                'purok' => (string) ($record['purok'] ?? '—'),
                'members' => $membersList->isNotEmpty() ? $membersList->implode(', ') : 'No members',
                'member_count' => $membersList->count(),
                'status' => (string) ($record['status'] ?? '—'),
                'registered_at' => (string) ($record['registered_at'] ?? '—'),
            ];
        })->all();

        AuditService::log(
            'report_households_export_excel',
            null,
            $this->buildHouseholdExportAuditDescription('excel', $filters->toArray())
        );

        return $this->createOfficialExcelResponse(
            filename: $filename,
            sheetName: 'Households',
            reportTitle: 'Household Report',
            scope: 'Barangay Paguiruan, Floridablanca',
            headers: ['#', 'Head of Family', 'Email', 'Purok', 'Members', 'Member Count', 'Status', 'Registered At'],
            rows: $excelRows,
            rowMapper: fn (array $record) => [
                $record['#'],
                $record['head_of_family'],
                $record['email'],
                $record['purok'],
                $record['members'],
                $record['member_count'],
                $record['status'],
                $record['registered_at'],
            ],
            leftMeta: [
                'Total Households' => (string) count($excelRows),
            ],
            includeSignature: true,
            afterRender: function ($sheet, int $headerRow, int $lastDataRow): void {
                $sheet->getStyle('A' . $headerRow . ':H' . $lastDataRow)
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('E' . $headerRow . ':E' . $lastDataRow)
                    ->getAlignment()
                    ->setWrapText(true);
                $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . ($headerRow + 1) . ':H' . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Keep the members column readable for print/export.
                $sheet->getColumnDimension('E')->setAutoSize(false);
                $sheet->getColumnDimension('E')->setWidth(48);
            }
        );
    }

    /**
     * Print household report using the same A4 layout as PDF.
     */
    public function householdsExportPrint(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $records = $this->buildHouseholdExportRows($request);

        AuditService::log(
            'report_households_export_print',
            null,
            $this->buildHouseholdExportAuditDescription('print', $filters->toArray())
        );

        return view('admin.reports.households-pdf', [
            'records' => $records,
            'filters' => [
                'purok' => $request->get('purok'),
            ],
        ]);
    }

    /**
     * Export household report as PDF.
     */
    public function householdsExportPdf(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $records = $this->buildHouseholdExportRows($request);

        $pdf = Pdf::loadView('admin.reports.households-pdf', [
            'records' => $records,
            'filters' => [
                'purok' => $request->get('purok'),
            ],
        ])->setPaper('a4', 'landscape');

        AuditService::log(
            'report_households_export_pdf',
            null,
            $this->buildHouseholdExportAuditDescription('pdf', $filters->toArray())
        );

        return $pdf->download('household_report_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Export household report as CSV.
     */
    public function householdsExportCsv(Request $request)
    {
        $this->authorizeReportsAccess($request);
        $filters = $this->householdReportService->resolveFilters($request);
        $records = $this->buildHouseholdExportRows($request);
        $filename = 'household_report_' . now()->format('Ymd_His') . '.csv';

        AuditService::log(
            'report_households_export_csv',
            null,
            $this->buildHouseholdExportAuditDescription('csv', $filters->toArray())
        );

        return $this->streamOfficialCsvResponse(
            $filename,
            'Household Reports',
            [
                ['Scope', $this->resolvePurokLabel($request->get('purok'))],
                ['Total Rows', (string) count($records)],
                ['Filters', $this->buildHouseholdExportAuditDescription('csv', $filters->toArray())],
            ],
            ['Head of Family', 'Email', 'Purok', 'Members'],
            $records,
            fn (array $record) => [
                $record['head_name'],
                $record['email'],
                $record['purok'],
                $record['members_csv'],
            ]
        );
    }

    private function buildHouseholdExportAuditDescription(string $format, array $filters): string
    {
        $parts = [
            'format=' . strtoupper($format),
            'purok=' . ($filters['purokId'] ?? 'all'),
            'head_id=' . ($filters['selectedHeadId'] ?? 'all'),
            'head_q=' . (($filters['headQuery'] ?? '') !== '' ? $filters['headQuery'] : 'none'),
            'resident_type=' . ($filters['residentType'] ?? 'all'),
            'status=' . ($filters['statusFilter'] ?? 'all'),
            'members_min=' . ($filters['membersMin'] ?? 'none'),
            'members_max=' . ($filters['membersMax'] ?? 'none'),
            'created_from=' . ($filters['createdFrom'] ?? 'none'),
            'created_to=' . ($filters['createdTo'] ?? 'none'),
            'sort=' . ($filters['sort'] ?? 'head_name'),
            'direction=' . ($filters['direction'] ?? 'asc'),
        ];

        return 'Exported household report [' . implode(', ', $parts) . ']';
    }

    /**
     * Group household detail rows by house head.
     *
     * @param  iterable<array<string, mixed>>  $rows
     * @return array<int, array{house_head:string, house_no:string, members:array<int, array<string, mixed>>}>
     */
    private function groupHouseholdRows(iterable $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $head = (string) ($row['house_head'] ?? '');
            if ($head === '') {
                continue;
            }

            if (! isset($groups[$head])) {
                $groups[$head] = [
                    'house_head' => $head,
                    'house_no' => (string) ($row['house_no'] ?? 'N/A'),
                    'members' => [],
                ];
            }

            if (($groups[$head]['house_no'] ?? '') === 'N/A' && ! empty($row['house_no'])) {
                $groups[$head]['house_no'] = (string) $row['house_no'];
            }

            $groups[$head]['members'][] = $row;
        }

        return array_values($groups);
    }

    /**
     * Flatten grouped rows with a visible head row followed by members.
     *
     * @param  array<int, array{house_head:string, house_no:string, members:array<int, array<string, mixed>>}>  $groupedRows
     * @return array<int, array<string, string>>
     */
    private function flattenGroupedHouseholdRows(array $groupedRows): array
    {
        $rows = [];
        foreach ($groupedRows as $group) {
            $rows[] = [
                'house_head' => (string) $group['house_head'],
                'family_member' => '(Head of Family)',
                'relationship' => 'Head',
                'house_no' => (string) ($group['house_no'] ?: 'N/A'),
            ];

            foreach ($group['members'] as $member) {
                $rows[] = [
                    'house_head' => '',
                    'family_member' => (string) ($member['family_member'] ?? ''),
                    'relationship' => (string) ($member['relationship'] ?? '—'),
                    'house_no' => (string) (($member['house_no'] ?? '') ?: 'N/A'),
                ];
            }

            // Spacer row between households for readability in CSV/Excel.
            $rows[] = [
                'house_head' => '',
                'family_member' => '',
                'relationship' => '',
                'house_no' => '',
            ];
        }

        if (! empty($rows)) {
            array_pop($rows);
        }

        return $rows;
    }

    private function createOfficialExcelResponse(
        string $filename,
        string $sheetName,
        string $reportTitle,
        string $scope,
        array $headers,
        iterable $rows,
        callable $rowMapper,
        array $leftMeta = [],
        array $rightMeta = [],
        bool $includeSignature = false,
        ?callable $afterRender = null
    ) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        $lastColumn = Coordinate::stringFromColumnIndex(max(1, count($headers)));
        $headerRow = $this->spreadsheetTheme->applyOfficialHeader(
            $sheet,
            $reportTitle,
            $scope,
            $lastColumn,
            $leftMeta,
            $rightMeta
        );

        $sheet->fromArray($headers, null, 'A' . $headerRow);
        $rowNumber = $headerRow + 1;
        foreach ($rows as $row) {
            $sheet->fromArray($rowMapper($row), null, 'A' . $rowNumber);
            $rowNumber++;
        }

        $lastDataRow = max($headerRow, $rowNumber - 1);
        $this->spreadsheetTheme->styleTable($sheet, $headerRow, $lastColumn, $lastDataRow);

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        if ($afterRender !== null) {
            $afterRender($sheet, $headerRow, $lastDataRow);
        }
        $finalRow = $includeSignature
            ? $this->spreadsheetTheme->applySignatureFooter($sheet, $lastColumn, $lastDataRow)
            : $lastDataRow;
        $this->spreadsheetTheme->applyPageSetup($sheet, $lastColumn, $finalRow, $headerRow);

        $path = storage_path('app/temp');
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $tempPath = $path . DIRECTORY_SEPARATOR . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function streamOfficialCsvResponse(
        string $filename,
        string $reportTitle,
        array $metaRows,
        array $headers,
        iterable $rows,
        callable $rowMapper
    ) {
        return response()->streamDownload(function () use ($reportTitle, $metaRows, $headers, $rows, $rowMapper): void {
            $handle = fopen('php://output', 'w');
            $this->csvFormatter->writeOfficialCsv($handle, $reportTitle, $metaRows, $headers, $rows, $rowMapper);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function normalizeHouseholdDate($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function authorizeReportsAccess(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! $user->canAccess('reports')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    /**
     * Export all report data as a multi-sheet Excel file.
     */
    public function export()
    {
        $file = $this->spreadsheetExportService->generateBarangayReportExcel(
            request('purok') ? (int) request('purok') : null
        );

        return response()->download($file['tempPath'], $file['filename'], [
            'Content-Type' => $file['contentType'],
        ])->deleteFileAfterSend(true);
    }

}
