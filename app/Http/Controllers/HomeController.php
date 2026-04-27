<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\CertificateRequest;
use App\Models\IssueReport;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latestAnnouncements = Announcement::query()
            ->where('status', Announcement::STATUS_APPROVED)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $communityStats = [
            'residents_served' => User::countable()
                ->where('status', User::STATUS_APPROVED)
                ->count(),
            'certificates_issued' => CertificateRequest::query()
                ->where('status', 'released')
                ->count(),
            'complaints_resolved' => IssueReport::query()
                ->whereIn('status', [
                    IssueReport::STATUS_RESOLVED,
                    IssueReport::STATUS_CLOSED,
                ])
                ->count(),
            'announcements_published' => Announcement::query()
                ->where('status', Announcement::STATUS_APPROVED)
                ->count(),
        ];

        $site = SiteSetting::allForPublic();

        return view('welcome', compact('communityStats', 'latestAnnouncements', 'site'));
    }
}
