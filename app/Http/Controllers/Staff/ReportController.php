<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    private array $viewData = [
        'layout' => 'layouts.staff',
        'routePrefix' => 'staff',
    ];

    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('reports')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(): View
    {
        $admin = app(AdminReportController::class);
        $view = $admin->index();

        return $view->with($this->viewData);
    }

    public function population(Request $request): View
    {
        $admin = app(AdminReportController::class);

        return $admin->population($request)->with($this->viewData);
    }

    public function populationExportPdf(Request $request)
    {
        return app(AdminReportController::class)->populationExportPdf($request);
    }

    public function populationExportExcel(Request $request)
    {
        return app(AdminReportController::class)->populationExportExcel($request);
    }

    public function classification(Request $request): View
    {
        $admin = app(AdminReportController::class);

        return $admin->classification($request)->with($this->viewData);
    }

    public function classificationExportPdf(Request $request)
    {
        return app(AdminReportController::class)->classificationExportPdf($request);
    }

    public function classificationExportExcel(Request $request)
    {
        return app(AdminReportController::class)->classificationExportExcel($request);
    }

    public function services(Request $request): View
    {
        $admin = app(AdminReportController::class);

        return $admin->services($request)->with($this->viewData);
    }

    public function servicesExportPdf(Request $request)
    {
        return app(AdminReportController::class)->servicesExportPdf($request);
    }

    public function servicesExportExcel(Request $request)
    {
        return app(AdminReportController::class)->servicesExportExcel($request);
    }

    public function households(Request $request): View
    {
        $admin = app(AdminReportController::class);

        return $admin->households($request)->with($this->viewData);
    }

    public function householdHeadSuggestions(Request $request): JsonResponse
    {
        return app(AdminReportController::class)->householdHeadSuggestions($request);
    }

    public function householdsView(Request $request): View
    {
        return app(AdminReportController::class)->householdsView($request)->with($this->viewData);
    }

    public function householdsViewPrint(Request $request): View
    {
        return app(AdminReportController::class)->householdsViewPrint($request)->with($this->viewData);
    }

    public function householdsViewExportPdf(Request $request)
    {
        return app(AdminReportController::class)->householdsViewExportPdf($request);
    }

    public function householdsViewExportExcel(Request $request)
    {
        return app(AdminReportController::class)->householdsViewExportExcel($request);
    }

    public function householdsTimeline(Request $request): View
    {
        return app(AdminReportController::class)->householdsTimeline($request)->with($this->viewData);
    }

    public function householdsTimelineExportPdf(Request $request)
    {
        return app(AdminReportController::class)->householdsTimelineExportPdf($request);
    }

    public function householdsExport(Request $request)
    {
        return app(AdminReportController::class)->householdsExport($request);
    }

    public function householdsExportPrint(Request $request)
    {
        return app(AdminReportController::class)->householdsExportPrint($request);
    }

    public function householdsExportPdf(Request $request)
    {
        return app(AdminReportController::class)->householdsExportPdf($request);
    }

    public function blotter(Request $request): View
    {
        return app(AdminReportController::class)->blotter($request)->with($this->viewData);
    }

    public function blotterExportPdf(Request $request)
    {
        return app(AdminReportController::class)->blotterExportPdf($request);
    }

    public function blotterExportExcel(Request $request)
    {
        return app(AdminReportController::class)->blotterExportExcel($request);
    }

    public function export(Request $request)
    {
        return app(AdminReportController::class)->export($request);
    }
}
