<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ComplaintNote;
use App\Models\IssueReport;
use App\Models\Purok;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    private array $viewData = [
        'layout' => 'layouts.staff',
        'routePrefix' => 'staff',
    ];

    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('complaints')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(Request $request): View
    {
        $query = IssueReport::with(['user', 'assignedOfficer', 'purok'])->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($purokId = $request->get('purok_id')) {
            $query->where('purok_id', $purokId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        $issues = $query->paginate(15)->withQueryString();

        $officers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])->orderBy('last_name')->get();
        $puroks = Purok::active()->orderBy('name')->get();
        $categories = IssueReport::whereNotNull('category')->distinct()->pluck('category')->sort()->values();

        $stats = IssueReport::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'pending' then 1 else 0 end) as pending")
            ->selectRaw("sum(case when status = 'in_progress' then 1 else 0 end) as in_progress")
            ->selectRaw("sum(case when status = 'resolved' then 1 else 0 end) as resolved")
            ->selectRaw("sum(case when status = 'closed' then 1 else 0 end) as closed")
            ->first();

        return view('admin.issues.index', array_merge(compact('issues', 'officers', 'puroks', 'categories', 'stats'), $this->viewData));
    }

    public function show(IssueReport $complaint): View
    {
        $complaint->load(['user', 'assignedOfficer', 'purok', 'notes.author', 'resolvedBy']);

        $officers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])->orderBy('last_name')->get();
        $nextStatus = IssueReport::TRANSITIONS[$complaint->status] ?? null;

        return view('admin.issues.show', array_merge([
            'issue' => $complaint,
            'officers' => $officers,
            'nextStatus' => $nextStatus,
        ], $this->viewData));
    }

    public function update(Request $request, IssueReport $complaint): RedirectResponse
    {
        $user = $request->user();

        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true) && (int) $complaint->assigned_to !== (int) $user->id) {
            abort(403, 'You are not authorized to update this complaint.');
        }

        if ($complaint->isClosed()) {
            abort(422, 'This complaint is closed and cannot be modified.');
        }

        $newStatus = $request->input('status');

        $rules = [
            'status' => ['required', 'string', 'in:pending,in_progress,resolved,closed'],
            'remarks' => IssueReport::requiresRemarks($newStatus)
                ? ['required', 'string', 'max:2000']
                : ['nullable', 'string', 'max:2000'],
        ];

        if ($newStatus === IssueReport::STATUS_RESOLVED) {
            $rules['resolution_notes'] = ['required', 'string', 'max:5000'];
            $rules['action_taken'] = ['nullable', 'string', 'max:100'];
            $rules['other_details'] = ['nullable', 'string', 'max:2000'];
            $rules['after_photo'] = ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'];
            if ($complaint->requiresAfterPhoto()) {
                $rules['after_photo'] = ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'];
            }
            if ($complaint->requiresOtherDetails()) {
                $rules['other_details'] = ['required', 'string', 'max:2000'];
            }
        }

        $validated = $request->validate($rules);

        if ($newStatus !== $complaint->status && ! $complaint->canTransitionTo($newStatus)) {
            abort(422, 'Invalid status transition.');
        }

        if (array_key_exists('remarks', $validated)) {
            $complaint->remarks = $validated['remarks'];
        }

        if ($newStatus === IssueReport::STATUS_RESOLVED) {
            $complaint->resolution_notes = $validated['resolution_notes'];
            $complaint->action_taken = $validated['action_taken'] ?? null;
            $complaint->other_details = $validated['other_details'] ?? null;
            $complaint->remarks = $validated['resolution_notes'];
            $complaint->forceFill(['resolved_at' => now(), 'resolved_by' => $user->id]);
            if ($request->hasFile('after_photo')) {
                $complaint->after_photo_path = $request->file('after_photo')
                    ->store('complaints/after', 'public');
            }
        }

        if ($newStatus !== $complaint->status) {
            $complaint->forceFill(['status' => $newStatus]);
            if ($newStatus === IssueReport::STATUS_CLOSED) {
                $complaint->forceFill(['closed_at' => now()]);
            }
        }

        $complaint->save();

        return redirect()->route('staff.issues.show', $complaint)
            ->with('success', 'Complaint status updated successfully.');
    }

    public function storeNote(Request $request, IssueReport $complaint): RedirectResponse
    {
        $user = $request->user();

        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true) && (int) $complaint->assigned_to !== (int) $user->id) {
            abort(403, 'You are not authorized to add notes to this complaint.');
        }

        if ($complaint->isClosed()) {
            abort(422, 'Cannot add notes to a closed complaint.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $note = new ComplaintNote($validated);
        $note->forceFill([
            'issue_report_id' => $complaint->id,
            'user_id' => $user->id,
        ])->save();

        return redirect()->route('staff.issues.show', $complaint)
            ->with('success', 'Note added successfully.');
    }

    public function assign(Request $request, IssueReport $complaint): RedirectResponse
    {
        if (! in_array($request->user()->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            abort(403, 'Only administrators can assign officers.');
        }

        if ($complaint->isClosed()) {
            abort(422, 'Cannot assign an officer to a closed complaint.');
        }

        $validated = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $officer = User::findOrFail($validated['assigned_to']);
        if (! in_array($officer->role, [User::ROLE_STAFF, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            return back()->withErrors(['assigned_to' => 'Only staff or admin users can be assigned.']);
        }

        $complaint->forceFill(['assigned_to' => $officer->id])->save();

        return redirect()->route('staff.issues.show', $complaint)
            ->with('success', "Complaint assigned to {$officer->full_name}.");
    }

    /**
     * Assign the authenticated user to a complaint ("Assign to me").
     */
    public function assignToMe(Request $request, IssueReport $complaint): RedirectResponse
    {
        $user = $request->user();

        if ($complaint->isClosed()) {
            abort(422, 'Cannot assign an officer to a closed complaint.');
        }

        if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            abort(403, 'Only staff or admin can assign themselves to complaints.');
        }

        $complaint->forceFill(['assigned_to' => $user->id])->save();

        return redirect()->route('staff.issues.show', $complaint)
            ->with('success', 'Complaint assigned to you.');
    }
}
