<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementLabelController extends Controller
{
    public function index()
    {
        $labels = AnnouncementLabel::withCount('announcements')
            ->orderBy('name')
            ->get();

        return view('admin.announcement-labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:announcement_labels,name',
        ]);

        $slug = Str::slug($validated['name']);
        $color = AnnouncementLabel::colorForSlug($slug);

        $label = new AnnouncementLabel([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $color,
        ]);
        $label->created_by = $request->user()->id;
        $label->save();

        return redirect()->route('admin.announcement-labels.index')
            ->with('success', 'Label created successfully.');
    }

    public function destroy(AnnouncementLabel $label)
    {
        if ($label->announcements()->exists()) {
            return back()->withErrors([
                'label' => 'Cannot delete this label because it is attached to ' . $label->announcements()->count() . ' announcement(s).',
            ]);
        }

        $label->delete();

        return redirect()->route('admin.announcement-labels.index')
            ->with('success', 'Label deleted successfully.');
    }
}
