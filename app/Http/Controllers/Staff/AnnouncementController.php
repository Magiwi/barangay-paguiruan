<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    private array $viewData = [
        'layout' => 'layouts.staff',
        'routePrefix' => 'staff',
    ];

    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('announcements')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(Request $request): View
    {
        $filter = $request->input('tab', 'active');
        $query = Announcement::with(['user', 'labels']);

        if ($filter === 'pending') {
            $query->where('status', Announcement::STATUS_PENDING);
        } elseif ($filter === 'rejected') {
            $query->where('status', Announcement::STATUS_REJECTED);
        }

        $query->latest();

        $announcements = $query->paginate(15)->withQueryString();
        $labels = AnnouncementLabel::orderBy('name')->get();

        $counts = [
            'active' => Announcement::count(),
            'pending' => Announcement::where('status', Announcement::STATUS_PENDING)->count(),
            'rejected' => Announcement::where('status', Announcement::STATUS_REJECTED)->count(),
            'archived' => 0,
        ];

        return view('admin.announcements.index', array_merge(compact('announcements', 'labels', 'counts', 'filter'), $this->viewData));
    }

    public function create(): View
    {
        $labels = AnnouncementLabel::orderBy('name')->get();

        return view('admin.announcements.create', array_merge(compact('labels'), $this->viewData));
    }

    public function store(Request $request): RedirectResponse
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
        $validated['status'] = Announcement::STATUS_PENDING;
        $validated['is_published'] = false;
        $validated['published_at'] = null;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement = Announcement::create($validated);
        $announcement->labels()->sync($labelIds);

        return redirect()->route('staff.announcements.index')
            ->with('success', 'Announcement submitted for approval.');
    }

    public function edit(Announcement $announcement): View
    {
        if ($announcement->status === Announcement::STATUS_APPROVED) {
            abort(403, 'Approved announcements cannot be edited by staff.');
        }

        $labels = AnnouncementLabel::orderBy('name')->get();
        $selectedLabels = $announcement->labels->pluck('id')->toArray();

        return view('admin.announcements.edit', array_merge(compact('announcement', 'labels', 'selectedLabels'), $this->viewData));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        if ($announcement->status === Announcement::STATUS_APPROVED) {
            abort(403, 'Approved announcements cannot be edited by staff.');
        }

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

        return redirect()->route('staff.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }
}
