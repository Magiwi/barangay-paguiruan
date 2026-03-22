<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintNote;
use App\Models\IssueReport;
use App\Models\Purok;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function index(Request $request): View
    {
        $query = IssueReport::with(['user', 'assignedOfficer', 'purok'])->latest();

        // ── Conditional filters ──
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        if ($purokId = $request->get('purok_id')) {
            $query->where('purok_id', $purokId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $issues = $query->paginate(15)->withQueryString();

        // ── Filter options ──
        $officers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
            ->orderBy('last_name')->get();
        $puroks = Purok::active()->orderBy('name')->get();
        $categories = IssueReport::whereNotNull('category')
            ->distinct()->pluck('category')->sort()->values();

        // ── Statistics (single query) ──
        $stats = IssueReport::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'pending' then 1 else 0 end) as pending")
            ->selectRaw("sum(case when status = 'in_progress' then 1 else 0 end) as in_progress")
            ->selectRaw("sum(case when status = 'resolved' then 1 else 0 end) as resolved")
            ->selectRaw("sum(case when status = 'closed' then 1 else 0 end) as closed")
            ->selectRaw('avg(case when closed_at is not null then timestampdiff(HOUR, created_at, closed_at) end) as avg_resolution_hours')
            ->first();

        return view('admin.issues.index', compact('issues', 'officers', 'puroks', 'categories', 'stats'));
    }

    /**
     * Show a single complaint with full details and notes timeline.
     */
    public function show(IssueReport $issue_report): View
    {
        $issue_report->load(['user', 'assignedOfficer', 'purok', 'notes.author', 'resolvedBy']);

        $officers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])
            ->orderBy('last_name')->get();

        $nextStatus = IssueReport::TRANSITIONS[$issue_report->status] ?? null;

        return view('admin.issues.show', [
            'issue' => $issue_report,
            'officers' => $officers,
            'nextStatus' => $nextStatus,
        ]);
    }

    /**
     * Advance the status of a complaint with strict state-machine enforcement.
     */
    public function update(Request $request, IssueReport $issue_report): RedirectResponse
    {
        $user = $request->user();

        // ── Authorization: only admin or assigned officer ──
        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true) && (int) $issue_report->assigned_to !== (int) $user->id) {
            abort(403, 'You are not authorized to update this complaint.');
        }

        // ── Closed cases are immutable ──
        if ($issue_report->isClosed()) {
            abort(422, 'This complaint is closed and cannot be modified.');
        }

        $newStatus = $request->input('status');

        // ── Build dynamic validation rules ──
        $rules = [
            'status' => ['required', 'string', 'in:pending,in_progress,resolved,closed'],
            'remarks' => IssueReport::requiresRemarks($newStatus)
                ? ['required', 'string', 'max:2000']
                : ['nullable', 'string', 'max:2000'],
        ];

        // Resolution-specific validation when transitioning to resolved
        if ($newStatus === IssueReport::STATUS_RESOLVED) {
            $rules['resolution_notes'] = ['required', 'string', 'max:5000'];
            $rules['action_taken'] = ['nullable', 'string', 'max:100'];
            $rules['other_details'] = ['nullable', 'string', 'max:2000'];
            $rules['after_photo'] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'];

            if ($issue_report->requiresAfterPhoto()) {
                $rules['after_photo'] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'];
            }
            if ($issue_report->requiresOtherDetails()) {
                $rules['other_details'] = ['required', 'string', 'max:2000'];
            }
        }

        $messages = [
            'remarks.required' => 'A resolution summary is required when marking a complaint as ' . str_replace('_', ' ', $newStatus) . '.',
            'resolution_notes.required' => 'Resolution notes are required when resolving a complaint.',
            'after_photo.required' => 'An after photo is required for ' . $issue_report->category . ' complaints.',
            'other_details.required' => 'Please specify the issue details for Other category.',
        ];

        $validated = $request->validate($rules, $messages);

        // ── State machine: only allow the next valid transition ──
        if ($newStatus !== $issue_report->status && ! $issue_report->canTransitionTo($newStatus)) {
            $allowed = IssueReport::TRANSITIONS[$issue_report->status] ?? 'none';
            abort(422, "Invalid transition from '{$issue_report->status}' to '{$newStatus}'. Allowed next status: {$allowed}.");
        }

        // ── Apply changes ──
        if (array_key_exists('remarks', $validated)) {
            $issue_report->remarks = $validated['remarks'];
        }

        if ($newStatus === IssueReport::STATUS_RESOLVED) {
            $issue_report->resolution_notes = $validated['resolution_notes'];
            $issue_report->action_taken = $validated['action_taken'] ?? null;
            $issue_report->other_details = $validated['other_details'] ?? null;
            $issue_report->remarks = $validated['resolution_notes']; // sync for backward compatibility
            $issue_report->forceFill(['resolved_at' => now(), 'resolved_by' => $user->id]);

            if ($request->hasFile('after_photo')) {
                $path = $request->file('after_photo')
                    ->store('complaints/after', 'public');
                $issue_report->after_photo_path = $path;
            }
        }

        $oldStatus = $issue_report->status;

        if ($newStatus !== $oldStatus) {
            $issue_report->forceFill(['status' => $newStatus]);

            if ($newStatus === IssueReport::STATUS_CLOSED) {
                $issue_report->forceFill(['closed_at' => now()]);
            }
        }

        $issue_report->save();

        if ($newStatus !== $oldStatus && $issue_report->user) {
            $statusLabel = str_replace('_', ' ', ucfirst($newStatus));
            $remarksText = $validated['remarks'] ?? null;
            NotificationService::notify(
                $issue_report->user,
                "Complaint {$statusLabel}",
                "Your complaint \"{$issue_report->subject}\" has been updated to {$statusLabel}." . ($remarksText ? " Remarks: {$remarksText}" : ''),
                'complaint',
                $issue_report->id
            );
        }

        return redirect()->route('admin.issues.show', $issue_report)
            ->with('success', 'Complaint status updated successfully.');
    }

    /**
     * Assign a staff/admin officer to a complaint.
     */
    public function assign(Request $request, IssueReport $issue_report): RedirectResponse
    {
        // Only admin can assign
        if (! in_array($request->user()->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            abort(403, 'Only administrators can assign officers.');
        }

        if ($issue_report->isClosed()) {
            abort(422, 'Cannot assign an officer to a closed complaint.');
        }

        $validated = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        // Verify the target user is staff or admin
        $officer = User::findOrFail($validated['assigned_to']);
        if (! in_array($officer->role, [User::ROLE_STAFF, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            return back()->withErrors(['assigned_to' => 'Only staff or admin users can be assigned as officers.']);
        }

        $issue_report->forceFill(['assigned_to' => $officer->id])->save();

        return redirect()->route('admin.issues.show', $issue_report)
            ->with('success', "Complaint assigned to {$officer->full_name}.");
    }

    /**
     * Assign the authenticated user to a complaint. Used by staff/admin for "Assign to me".
     */
    public function assignToMe(Request $request, IssueReport $issue_report): RedirectResponse
    {
        $user = $request->user();

        if ($issue_report->isClosed()) {
            abort(422, 'Cannot assign an officer to a closed complaint.');
        }

        if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            abort(403, 'Only staff or admin can assign themselves to complaints.');
        }

        $issue_report->forceFill(['assigned_to' => $user->id])->save();

        return redirect()->route('admin.issues.show', $issue_report)
            ->with('success', 'Complaint assigned to you.');
    }

    /**
     * Add a case note to a complaint.
     * Only admin or assigned officer can add notes.
     */
    public function storeNote(Request $request, IssueReport $issue_report): RedirectResponse
    {
        $user = $request->user();

        // ── Authorization: only admin or assigned officer ──
        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true) && (int) $issue_report->assigned_to !== (int) $user->id) {
            abort(403, 'You are not authorized to add notes to this complaint.');
        }

        if ($issue_report->isClosed()) {
            abort(422, 'Cannot add notes to a closed complaint.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $note = new ComplaintNote($validated);
        $note->forceFill([
            'issue_report_id' => $issue_report->id,
            'user_id' => $user->id,
        ])->save();

        return redirect()->route('admin.issues.show', $issue_report)
            ->with('success', 'Note added successfully.');
    }
}
