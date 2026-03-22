<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseholdHeadTransferRequest;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HeadTransferService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;
use Illuminate\View\View;

class HouseholdHeadTransferRequestController extends Controller
{
    public function __construct(
        private readonly HeadTransferService $headTransferService
    ) {
    }

    public function index(Request $request): View
    {
        $this->ensureCanManageHeadTransfers($request);

        $status = strtolower((string) $request->query('status', HouseholdHeadTransferRequest::STATUS_PENDING));
        if (! in_array($status, [
            HouseholdHeadTransferRequest::STATUS_PENDING,
            HouseholdHeadTransferRequest::STATUS_APPROVED,
            HouseholdHeadTransferRequest::STATUS_REJECTED,
            'all',
        ], true)) {
            $status = HouseholdHeadTransferRequest::STATUS_PENDING;
        }
        $search = trim((string) $request->query('q', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $query = HouseholdHeadTransferRequest::query()
            ->with([
                'requester',
                'currentHead',
                'newHead',
                'processedBy',
                'processedTransferLog',
            ]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->whereHas('requester', function ($q) use ($search): void {
                        $q->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('middle_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('newHead', function ($q) use ($search): void {
                        $q->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('middle_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhere('details', 'like', '%' . $search . '%');
            });
        }

        if ($from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($status === HouseholdHeadTransferRequest::STATUS_PENDING) {
            $query->orderBy('created_at');
        } elseif ($status === 'all') {
            $query
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END ASC")
                ->orderByRaw("CASE WHEN status = 'pending' THEN created_at END ASC")
                ->orderByRaw("CASE WHEN status <> 'pending' THEN created_at END DESC");
        } else {
            $query->latest();
        }

        $requests = $query->paginate(15)->withQueryString();

        $counts = [
            HouseholdHeadTransferRequest::STATUS_PENDING => (int) HouseholdHeadTransferRequest::where('status', HouseholdHeadTransferRequest::STATUS_PENDING)->count(),
            HouseholdHeadTransferRequest::STATUS_APPROVED => (int) HouseholdHeadTransferRequest::where('status', HouseholdHeadTransferRequest::STATUS_APPROVED)->count(),
            HouseholdHeadTransferRequest::STATUS_REJECTED => (int) HouseholdHeadTransferRequest::where('status', HouseholdHeadTransferRequest::STATUS_REJECTED)->count(),
        ];
        $metrics = [
            'pending_total' => $counts[HouseholdHeadTransferRequest::STATUS_PENDING],
            'approved_today' => (int) HouseholdHeadTransferRequest::where('status', HouseholdHeadTransferRequest::STATUS_APPROVED)
                ->whereDate('processed_at', today())
                ->count(),
            'rejected_today' => (int) HouseholdHeadTransferRequest::where('status', HouseholdHeadTransferRequest::STATUS_REJECTED)
                ->whereDate('processed_at', today())
                ->count(),
            'overdue_pending' => (int) HouseholdHeadTransferRequest::where('status', HouseholdHeadTransferRequest::STATUS_PENDING)
                ->where('created_at', '<', now()->subDays(3))
                ->count(),
        ];

        return view('admin.head-transfer-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'counts' => $counts,
            'metrics' => $metrics,
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'reasonLabels' => HouseholdHeadTransferRequest::REASON_LABELS,
        ]);
    }

    public function approve(Request $request, HouseholdHeadTransferRequest $transferRequest): RedirectResponse
    {
        $this->ensureCanManageHeadTransfers($request);

        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $resident = User::query()->findOrFail((int) $transferRequest->current_head_id);
            Gate::authorize('manage-household-link', $resident);

            $this->headTransferService->approveRequest(
                $transferRequest,
                $request->user(),
                $validated['review_note'] ?? null
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors(['request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Head transfer request approved and applied successfully.');
    }

    public function reject(Request $request, HouseholdHeadTransferRequest $transferRequest): RedirectResponse
    {
        $this->ensureCanManageHeadTransfers($request);

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($request, $transferRequest, $validated): void {
                $lockedRequest = HouseholdHeadTransferRequest::query()
                    ->whereKey($transferRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedRequest) {
                    throw new RuntimeException('Head transfer request could not be found.');
                }

                if ($lockedRequest->status !== HouseholdHeadTransferRequest::STATUS_PENDING) {
                    throw new RuntimeException('This head transfer request was already processed.');
                }

                $resident = User::query()->whereKey($lockedRequest->current_head_id)->lockForUpdate()->first();
                if ($resident) {
                    Gate::authorize('manage-household-link', $resident);
                }

                $lockedRequest->fill([
                    'status' => HouseholdHeadTransferRequest::STATUS_REJECTED,
                    'review_note' => $validated['review_note'],
                    'processed_by' => $request->user()->id,
                    'processed_at' => now(),
                ])->save();

                if ($resident) {
                    AuditService::log(
                        'family_transfer_request_rejected',
                        $resident,
                        'Rejected head transfer request. Reason: ' . $validated['review_note']
                    );

                    NotificationService::notify(
                        $resident,
                        'Head transfer request rejected',
                        'Your request was rejected. Reason: ' . $validated['review_note'],
                        'household_transfer',
                        $lockedRequest->id
                    );
                }
            });
        } catch (RuntimeException $exception) {
            return back()->withErrors(['request' => $exception->getMessage()]);
        }

        return back()->with('success', 'Head transfer request rejected.');
    }

    private function ensureCanManageHeadTransfers(Request $request): void
    {
        $actor = $request->user();
        if (! $actor) {
            abort(403);
        }

        if (in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            return;
        }

        if ($actor->role === User::ROLE_STAFF && $actor->canAccess('registrations')) {
            return;
        }

        abort(403, 'You do not have access to manage head transfer requests.');
    }
}
