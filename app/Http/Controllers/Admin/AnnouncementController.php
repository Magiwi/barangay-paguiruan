<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementLabel;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('tab', 'active');

        $query = Announcement::with(['user', 'labels']);

        if ($filter === 'archived') {
            $query->onlyTrashed();
        } elseif ($filter === 'pending') {
            $query->where('status', Announcement::STATUS_PENDING);
        } elseif ($filter === 'rejected') {
            $query->where('status', Announcement::STATUS_REJECTED);
        }
        // 'active' shows all non-trashed (draft + pending + approved + rejected)

        if ($slug = $request->input('label')) {
            $query->whereHas('labels', function ($q) use ($slug) {
                $q->where('slug', $slug);
            });

            if ($slug === 'emergency') {
                $query->orderByDesc('created_at');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $announcements = $query->paginate(15)->withQueryString();
        $labels = AnnouncementLabel::orderBy('name')->get();

        $counts = [
            'active' => Announcement::count(),
            'pending' => Announcement::where('status', Announcement::STATUS_PENDING)->count(),
            'rejected' => Announcement::where('status', Announcement::STATUS_REJECTED)->count(),
            'archived' => Announcement::onlyTrashed()->count(),
        ];

        return view('admin.announcements.index', compact('announcements', 'labels', 'counts', 'filter'));
    }

    public function create()
    {
        $labels = AnnouncementLabel::orderBy('name')->get();

        return view('admin.announcements.create', compact('labels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['exists:announcement_labels,id'],
        ]);

        $labelIds = $validated['labels'] ?? [];
        unset($validated['labels']);

        $validated['user_id'] = auth()->id();

        // Admin auto-approves; other roles go to pending
        if (in_array($request->user()->role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN], true)) {
            $validated['status'] = Announcement::STATUS_APPROVED;
            $validated['is_published'] = true;
            $validated['published_at'] = now();
        } else {
            $validated['status'] = Announcement::STATUS_PENDING;
            $validated['is_published'] = false;
            $validated['published_at'] = null;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement = Announcement::create($validated);
        $announcement->labels()->sync($labelIds);

        $message = $validated['status'] === Announcement::STATUS_APPROVED
            ? 'Announcement created and published.'
            : 'Announcement submitted for approval.';

        return redirect()->route('admin.announcements.index')
            ->with('success', $message);
    }

    public function edit(Announcement $announcement)
    {
        $labels = AnnouncementLabel::orderBy('name')->get();
        $selectedLabels = $announcement->labels->pluck('id')->toArray();

        return view('admin.announcements.edit', compact('announcement', 'labels', 'selectedLabels'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['exists:announcement_labels,id'],
        ]);

        $labelIds = $validated['labels'] ?? [];
        unset($validated['labels']);

        if ($request->hasFile('image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $validated['image'] = $request->file('image')->store('announcements', 'public');
        } elseif (! empty($validated['remove_image'])) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $validated['image'] = null;
        }

        unset($validated['remove_image']);
        $announcement->update($validated);
        $announcement->labels()->sync($labelIds);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function approve(Announcement $announcement)
    {
        if (! in_array(auth()->user()->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            abort(403, 'Only administrators can approve announcements.');
        }

        $announcement->forceFill([
            'status' => Announcement::STATUS_APPROVED,
            'is_published' => true,
            'published_at' => $announcement->published_at ?? now(),
        ])->save();

        AuditService::log('announcement_approved', $announcement, "Approved announcement: {$announcement->title}");

        return back()->with('success', 'Announcement approved and published.');
    }

    public function reject(Announcement $announcement)
    {
        if (! in_array(auth()->user()->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            abort(403, 'Only administrators can reject announcements.');
        }

        $announcement->forceFill([
            'status' => Announcement::STATUS_REJECTED,
            'is_published' => false,
            'published_at' => null,
        ])->save();

        AuditService::log('announcement_rejected', $announcement, "Rejected announcement: {$announcement->title}");

        return back()->with('success', 'Announcement rejected.');
    }

    public function destroy(Announcement $announcement)
    {
        AuditService::log('announcement_archived', $announcement, "Archived announcement: {$announcement->title}");

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement archived successfully.');
    }

    public function restore(int $id)
    {
        $announcement = Announcement::withTrashed()->findOrFail($id);
        $announcement->restore();

        AuditService::log('announcement_restored', $announcement, "Restored announcement: {$announcement->title}");

        return redirect()->route('admin.announcements.index', ['tab' => 'active'])
            ->with('success', 'Announcement restored successfully.');
    }
}
