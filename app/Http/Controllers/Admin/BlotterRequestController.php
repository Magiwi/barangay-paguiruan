<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlotterRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlotterRequestController extends Controller
{
    // ──────────────────────────────────────────────────
    // INDEX — list all blotter requests with filters
    // ──────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $query = BlotterRequest::with('blotter', 'user', 'processedBy')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('blotter', function ($b) use ($search) {
                    $b->where('blotter_number', 'like', "%{$search}%");
                });
            });
        }

        $blotterRequests = $query->paginate(10)->withQueryString();

        $stats = BlotterRequest::query()
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'pending' then 1 else 0 end) as pending_count")
            ->selectRaw("sum(case when status = 'approved' then 1 else 0 end) as approved_count")
            ->selectRaw("sum(case when status = 'rejected' then 1 else 0 end) as rejected_count")
            ->selectRaw("sum(case when status = 'released' then 1 else 0 end) as released_count")
            ->first();

        $rejectionReasonOptions = BlotterRequest::rejectionReasonOptions();

        return view('admin.blotter-requests.index', compact('blotterRequests', 'stats', 'rejectionReasonOptions'));
    }

    // ──────────────────────────────────────────────────
    // UPDATE STATUS — with transition & role enforcement
    // ──────────────────────────────────────────────────
    public function updateStatus(Request $request, BlotterRequest $blotterRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected,released'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'rejection_reason_code' => ['nullable', 'string', 'in:' . implode(',', BlotterRequest::rejectionReasonCodes())],
        ]);

        $newStatus = $validated['status'];
        $user = $request->user();

        // ── Transition validation ──
        if (! $blotterRequest->canTransitionTo($newStatus)) {
            abort(422, "Cannot transition from '{$blotterRequest->status}' to '{$newStatus}'.");
        }

        // ── Role-based access control ──
        // Only admin can approve or reject
        if (in_array($newStatus, [BlotterRequest::STATUS_APPROVED, BlotterRequest::STATUS_REJECTED], true)) {
            if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
                abort(403, 'Only administrators can approve or reject blotter requests.');
            }
        }

        // Staff can only mark as released (admin can too)
        if ($newStatus === BlotterRequest::STATUS_RELEASED) {
            if (! in_array($user->role, [User::ROLE_STAFF, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
                abort(403, 'Only staff or administrators can release blotter requests.');
            }
        }

        // ── Standard rejection reason validation ──
        if ($newStatus === BlotterRequest::STATUS_REJECTED) {
            $reasonCode = $validated['rejection_reason_code'] ?? null;
            if (empty($reasonCode)) {
                return back()->withErrors([
                    'rejection_reason_code' => 'Please select a rejection reason.',
                ])->withInput();
            }

            if ($reasonCode === BlotterRequest::REJECTION_REASON_OTHERS && empty($validated['remarks'])) {
                return back()->withErrors([
                    'remarks' => 'Please provide details when "Others" is selected.',
                ])->withInput();
            }
        }

        // ── Persist ──
        $blotterRequest->forceFill([
            'status' => $newStatus,
            'processed_by' => $user->id,
            'processed_at' => now(),
            'rejection_reason_code' => $newStatus === BlotterRequest::STATUS_REJECTED
                ? ($validated['rejection_reason_code'] ?? null)
                : null,
        ]);

        if (! empty($validated['remarks'])) {
            $blotterRequest->remarks = $validated['remarks'];
        } elseif ($newStatus !== BlotterRequest::STATUS_REJECTED) {
            $blotterRequest->remarks = null;
        }

        $blotterRequest->save();

        AuditService::log('blotter_request_' . $newStatus, $blotterRequest, ucfirst($newStatus) . " blotter request #{$blotterRequest->id}");

        if ($blotterRequest->user) {
            $label = ucfirst($newStatus);
            $reasonLabel = $blotterRequest->rejectionReasonLabel();
            $msg = match ($newStatus) {
                'approved' => 'Your blotter record request has been approved.',
                'rejected' => 'Your blotter record request has been rejected.'
                    . (! empty($reasonLabel) ? " Reason: {$reasonLabel}." : '')
                    . (! empty($validated['remarks']) ? " Details: {$validated['remarks']}" : ''),
                'released' => 'Your blotter record has been released. You may now claim it at the barangay hall.',
                default => "Your blotter record request status has been updated to {$label}.",
            };
            NotificationService::notify($blotterRequest->user, "Blotter Request {$label}", $msg, 'blotter', $blotterRequest->id);
        }

        $label = ucfirst($newStatus);

        return redirect()->route('admin.blotter-requests.index')
            ->with('success', "Blotter request {$label} successfully.");
    }
}
