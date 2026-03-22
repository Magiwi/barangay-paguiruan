<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\BlotterRequest;
use App\Models\CertificateRequest;
use App\Models\IssueReport;
use App\Models\Permit;
use App\Models\User;
use App\Services\PopulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PopulationService $populationService
    ) {
    }

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        $user->load(['staffPermission', 'position', 'activeOfficial']);
        $perm = $user->staffPermission;

        if (! $user->hasAnyModuleAccess()) {
            return redirect()->route('staff.no-access');
        }

        $stats = [];
        $recentActivity = collect();

        $stats['total_residents'] = $this->populationService->getTotalResidents();

        // Registration stats (gated by permission)
        if ($user->hasModuleAccess('registrations')) {
            $stats['pending_registrations'] = User::where('status', User::STATUS_PENDING)->count();
        }

        // Module-specific stats based on permissions
        if ($user->hasModuleAccess('blotter')) {
            $stats['pending_blotter_requests'] = BlotterRequest::where('status', 'pending')->count();
            $stats['total_blotter_requests'] = BlotterRequest::count();
        }

        if ($user->hasModuleAccess('announcements')) {
            $stats['pending_announcements'] = Announcement::where('status', Announcement::STATUS_PENDING)->count();
            $stats['total_announcements'] = Announcement::count();
        }

        if ($user->hasModuleAccess('complaints')) {
            $stats['pending_issues'] = IssueReport::where('status', 'pending')->count();
            $stats['in_progress_issues'] = IssueReport::where('status', 'in_progress')->count();
        }

        if ($user->hasModuleAccess('reports')) {
            $stats['pending_certificates'] = CertificateRequest::where('status', 'pending')->count();
            $stats['pending_permits'] = Permit::where('status', 'pending')->count();
        }

        // Recent activity for accessible modules
        if ($user->hasModuleAccess('complaints')) {
            $recentIssues = IssueReport::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($i) => [
                    'type' => 'issue',
                    'title' => $i->subject,
                    'by' => $i->user->full_name ?? 'Unknown',
                    'status' => $i->status,
                    'date' => $i->created_at,
                ]);
            $recentActivity = $recentActivity->merge($recentIssues);
        }

        if ($user->hasModuleAccess('blotter')) {
            $recentBlotters = BlotterRequest::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($b) => [
                    'type' => 'blotter_request',
                    'title' => 'Blotter Request #' . $b->id,
                    'by' => $b->user->full_name ?? 'Unknown',
                    'status' => $b->status,
                    'date' => $b->created_at,
                ]);
            $recentActivity = $recentActivity->merge($recentBlotters);
        }

        $recentActivity = $recentActivity->sortByDesc('date')->take(8)->values();

        return view('staff.dashboard', compact('user', 'perm', 'stats', 'recentActivity'));
    }
}
