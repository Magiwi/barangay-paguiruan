<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementLabel;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with('labels')
            ->published();

        if ($slug = $request->input('label')) {
            $query->whereHas('labels', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });

            if ($slug === 'emergency') {
                $query->orderByDesc('created_at');
            } else {
                $query->orderByDesc('published_at')
                    ->orderByDesc('created_at');
            }
        } else {
            $query->orderByRaw("
                EXISTS (
                    SELECT 1 FROM announcement_announcement_label aal
                    JOIN announcement_labels al ON al.id = aal.announcement_label_id
                    WHERE aal.announcement_id = announcements.id
                    AND al.slug = 'emergency'
                ) DESC
            ")
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');
        }

        $announcements = $query->paginate(15)->withQueryString();
        $labels = AnnouncementLabel::orderBy('name')->get();

        return view('resident.announcements.index', compact('announcements', 'labels'));
    }

    public function show(Announcement $announcement)
    {
        if (! $announcement->isApproved()) {
            abort(404);
        }

        $announcement->load(['labels', 'user']);

        return view('resident.announcements.show', compact('announcement'));
    }
}
