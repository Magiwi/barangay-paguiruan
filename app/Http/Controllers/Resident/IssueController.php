<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\IssueReport;
use App\Models\Purok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function index(): View
    {
        $issues = auth()->user()->issueReports()->latest()->paginate(10);

        return view('resident.issues.index', compact('issues'));
    }

    public function create(): View
    {
        $puroks = Purok::orderBy('name')->get();

        return view('resident.issues.create', compact('puroks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:Infrastructure,Noise,Sanitation,Safety,Flooding,Stray Animals,Illegal Activity,Other'],
            'description' => ['required', 'string', 'max:5000'],
            'purok_id' => ['required', 'exists:puroks,id'],
            'location' => ['required', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'other_details' => ['nullable', 'string', 'max:2000'],
        ];
        if ($request->input('category') === 'Other') {
            $rules['other_details'] = ['required', 'string', 'max:2000'];
        }
        $validated = $request->validate($rules, [
            'attachment.mimes' => 'Only JPG, PNG, and PDF files are allowed.',
            'attachment.max' => 'Attachment must not exceed 5MB.',
            'other_details.required' => 'Please specify your issue when category is Other.',
        ]);

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')
                ->store('complaints', 'public');
        }

        // Remove the raw file from validated (not a DB column)
        unset($validated['attachment']);

        // Create model and set guarded fields before first insert.
        $issue = new IssueReport($validated);
        $issue->forceFill([
            'user_id' => auth()->id(),
            'status' => IssueReport::STATUS_PENDING,
        ]);
        $issue->save();

        return redirect()->route('resident.issues.index')
            ->with('success', 'Complaint submitted successfully.');
    }

    /**
     * Show a complaint detail. Residents can only view their own.
     */
    public function show(IssueReport $issue): View
    {
        if ((int) $issue->user_id !== (int) auth()->id()) {
            abort(403, 'You can only view your own complaints.');
        }

        $issue->load(['purok', 'resolvedBy']);

        return view('resident.issues.show', compact('issue'));
    }
}
