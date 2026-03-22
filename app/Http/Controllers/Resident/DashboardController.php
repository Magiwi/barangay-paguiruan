<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $unreadNotifications = $user->unreadNotificationsCount();

        $certificates = $user->certificateRequests()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'approved') as approved,
                SUM(status = 'released') as released,
                SUM(status = 'rejected') as rejected
            ")
            ->first();

        $permits = $user->permits()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'approved') as approved,
                SUM(status = 'released') as released,
                SUM(status = 'rejected') as rejected
            ")
            ->first();

        $complaints = $user->issueReports()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'in_progress') as in_progress,
                SUM(status = 'resolved') as resolved,
                SUM(status = 'closed') as closed
            ")
            ->first();

        $blotterRequests = $user->blotterRequests()
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'approved') as approved,
                SUM(status = 'released') as released
            ")
            ->first();

        $recentAnnouncements = Announcement::approved()
            ->latest('published_at')
            ->take(3)
            ->get(['id', 'slug', 'title', 'content', 'published_at', 'image']);

        return view('resident.dashboard', compact(
            'unreadNotifications',
            'certificates',
            'permits',
            'complaints',
            'blotterRequests',
            'recentAnnouncements'
        ));
    }
}
