<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\Household;
use App\Models\RegistrationAlertRun;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\RegistrationEscalationConfig;
use App\Notifications\RegistrationDecisionNotification;
use Throwable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class PendingRegistrationsController extends Controller
{
    /**
     * List users filtered by registration status.
     */
    public function index(Request $request): View
    {
        $settings = RegistrationEscalationConfig::get();
        $status = $request->get('status', 'pending');

        $rejectionLogs = null;

        if ($status === 'rejected') {
            $rejectionLogs = ApprovalLog::query()
                ->with('performer')
                ->where('action', ApprovalLog::ACTION_REJECTED)
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString();

            $users = User::query()->whereRaw('0 = 1')->paginate(15)->withQueryString();
        } else {
            $query = User::countable();

            if (in_array($status, ['pending', 'approved', 'suspended'], true)) {
                $query->where('status', $status);
            }

            if ($status === User::STATUS_PENDING) {
                $this->applyPendingQueueFilters($query, $request, $settings);
                $query->orderByRaw(
                    "CASE
                        WHEN created_at <= ? THEN 0
                        WHEN created_at <= ? THEN 1
                        ELSE 2
                    END",
                    [now()->subHours($settings['overdue_hours']), now()->subHours($settings['due_soon_hours'])]
                )->orderBy('created_at');
            } else {
                $query->orderByDesc('created_at');
            }

            $users = $query->paginate(15)->withQueryString();
        }

        $counts = [
            'pending' => User::countable()->where('status', User::STATUS_PENDING)->count(),
            'approved' => User::countable()->where('status', User::STATUS_APPROVED)->count(),
            'rejected' => ApprovalLog::query()->where('action', ApprovalLog::ACTION_REJECTED)->count(),
            'suspended' => User::countable()->where('status', User::STATUS_SUSPENDED)->count(),
        ];

        $rejectionReasonOptions = User::REGISTRATION_REJECTION_REASON_LABELS;
        $pendingQueueStats = [
            'overdue' => User::countable()->where('status', User::STATUS_PENDING)->where('created_at', '<=', now()->subHours($settings['overdue_hours']))->count(),
            'due_soon' => User::countable()->where('status', User::STATUS_PENDING)->where('created_at', '<=', now()->subHours($settings['due_soon_hours']))->where('created_at', '>', now()->subHours($settings['overdue_hours']))->count(),
            'missing_id' => User::countable()->where('status', User::STATUS_PENDING)->where(function ($q) {
                $q->whereNull('government_id_path')->orWhereNull('government_id_type');
            })->count(),
        ];
        $latestEscalationRun = $this->latestEscalationRun();
        $manualReminderCooldownSeconds = max(
            $this->manualReminderCooldownSeconds($settings),
            $this->manualReminderCooldownSeconds($settings, auth()->id())
        );
        $escalationAnalytics = $this->escalationAnalytics();
        $failedQueueStats = $this->failedQueueStats();

        return view('admin.pending-registrations.index', compact('users', 'rejectionLogs', 'status', 'counts', 'rejectionReasonOptions', 'pendingQueueStats', 'latestEscalationRun', 'manualReminderCooldownSeconds', 'escalationAnalytics', 'failedQueueStats', 'settings'));
    }

    public function sendReminderNow(Request $request): RedirectResponse
    {
        $settings = RegistrationEscalationConfig::get();
        $validated = $request->validate([
            'hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'trigger_reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $hours = (int) ($validated['hours'] ?? $settings['overdue_hours']);
        $reason = trim((string) $validated['trigger_reason']);
        $globalCooldownSeconds = $this->manualReminderCooldownSeconds($settings);
        $userCooldownSeconds = $this->manualReminderCooldownSeconds($settings, $request->user()?->id);
        $cooldownSeconds = max($globalCooldownSeconds, $userCooldownSeconds);
        if ($cooldownSeconds > 0) {
            $waitMinutes = (int) ceil($cooldownSeconds / 60);

            return back()->with('error', "Please wait {$waitMinutes} minute(s) before sending another reminder.");
        }

        Artisan::call('registrations:notify-overdue', [
            '--hours' => $hours,
            '--trigger-source' => 'manual',
            '--triggered-by' => (int) $request->user()->id,
            '--trigger-reason' => $reason,
        ]);

        AuditService::log(
            'registration_escalation_manual_triggered',
            $request->user(),
            "Manually triggered overdue registration reminders (threshold: {$hours}h). Reason: {$reason}"
        );

        return back()->with('success', "Manual registration reminder trigger completed (threshold: {$hours}h).");
    }

    public function retryFailedReminderJobs(Request $request): RedirectResponse
    {
        $settings = RegistrationEscalationConfig::get();
        if (! Schema::hasTable('failed_jobs')) {
            return back()->with('error', 'Failed jobs table is not available.');
        }

        $failedRows = DB::table('failed_jobs')
            ->where('payload', 'like', '%OverdueRegistrationSummaryNotification%')
            ->orderByDesc('id')
            ->limit($settings['retry_batch_limit'])
            ->get(['id']);

        if ($failedRows->isEmpty()) {
            return back()->with('error', 'No failed overdue reminder jobs to retry.');
        }

        $retried = 0;
        foreach ($failedRows as $row) {
            Artisan::call('queue:retry', ['id' => [(string) $row->id]]);
            $retried++;
        }

        AuditService::log(
            'registration_escalation_retry_failed_jobs',
            $request->user(),
            "Retried {$retried} failed overdue reminder queue job(s)."
        );

        return back()->with('success', "Retried {$retried} failed overdue reminder job(s).");
    }

    public function updateEscalationSettings(Request $request): RedirectResponse
    {
        if (! $this->canManageEscalationSettings($request)) {
            abort(403, 'Only admin users can update escalation settings.');
        }

        $validated = $request->validate([
            'overdue_hours' => ['required', 'integer', 'min:2', 'max:720'],
            'due_soon_hours' => ['required', 'integer', 'min:1', 'max:719'],
            'cooldown_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'retry_batch_limit' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        if ((int) $validated['due_soon_hours'] >= (int) $validated['overdue_hours']) {
            return back()->with('error', 'Due soon hours must be lower than overdue hours.');
        }

        RegistrationEscalationConfig::update($validated, $request->user()?->id);

        AuditService::log(
            'registration_escalation_settings_updated',
            $request->user(),
            "Updated escalation settings: overdue={$validated['overdue_hours']}h, due_soon={$validated['due_soon_hours']}h, cooldown={$validated['cooldown_minutes']}m, retry_limit={$validated['retry_batch_limit']}."
        );

        return back()->with('success', 'Escalation settings updated.');
    }

    /**
     * Approve a pending registration.
     * Transition: pending -> approved
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        if ($user->status !== User::STATUS_PENDING) {
            abort(403, 'Only pending registrations can be approved.');
        }

        // Safety guard: pending accounts must still satisfy 18+ policy at approval time.
        $age = $user->birthdate ? (int) $user->birthdate->age : null;
        if ($age === null || $age < 18) {
            return back()->with('error', "Cannot approve {$user->full_name}: resident must be 18 years old or above.");
        }

        if (! $user->government_id_path || ! $user->government_id_type) {
            return back()->with('error', "Cannot approve {$user->full_name}: government ID proof is required.");
        }

        DB::transaction(function () use ($user, $request) {
            $user->forceFill([
                'status' => User::STATUS_APPROVED,
                'rejection_reason_code' => null,
                'rejection_reason_details' => null,
            ])->save();

            ApprovalLog::create([
                'user_id' => $user->id,
                'action' => ApprovalLog::ACTION_APPROVED,
                'performed_by' => Auth::id(),
                'remarks' => $request->input('remarks'),
            ]);

            $this->attemptFamilyLink($user);
        });

        AuditService::log('registration_status_changed', $user, 'Changed status to approved');
        $this->notifyRegistrationDecision($user, User::STATUS_APPROVED);

        return back()->with('success', "Registration for {$user->full_name} approved.");
    }

    /**
     * Reject a pending registration.
     * Transition: pending -> rejected
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        if ($user->status !== User::STATUS_PENDING) {
            abort(403, 'Only pending registrations can be rejected.');
        }

        $payload = $this->validateRejectPayload($request);
        $fullName = $user->full_name;

        DB::transaction(function () use ($user, $request, $payload) {
            $this->purgeRejectedRegistration($user, $payload, $request->input('remarks'));
        });

        return back()->with('success', "Registration for {$fullName} rejected and registration data deleted.");
    }

    /**
     * Suspend an approved user.
     * Transition: approved -> suspended
     */
    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($user->status !== User::STATUS_APPROVED) {
            abort(403, 'Only approved users can be suspended.');
        }

        if ($request->user()->id === $user->id) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        DB::transaction(function () use ($user, $request) {
            $user->forceFill([
                'status' => User::STATUS_SUSPENDED,
                'is_suspended' => true,
                'suspended_at' => now(),
            ])->save();

            ApprovalLog::create([
                'user_id' => $user->id,
                'action' => ApprovalLog::ACTION_SUSPENDED,
                'performed_by' => Auth::id(),
                'remarks' => $request->input('remarks'),
            ]);
        });

        AuditService::log('registration_status_changed', $user, 'Changed status to suspended');

        return back()->with('success', "Account for {$user->full_name} suspended.");
    }

    /**
     * Unsuspend a suspended user (restore to approved).
     * Transition: suspended -> approved
     */
    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        if ($user->status !== User::STATUS_SUSPENDED) {
            abort(403, 'Only suspended users can be unsuspended.');
        }

        DB::transaction(function () use ($user, $request) {
            $user->forceFill([
                'status' => User::STATUS_APPROVED,
                'is_suspended' => false,
                'suspended_at' => null,
            ])->save();

            ApprovalLog::create([
                'user_id' => $user->id,
                'action' => ApprovalLog::ACTION_UNSUSPENDED,
                'performed_by' => Auth::id(),
                'remarks' => $request->input('remarks'),
            ]);
        });

        AuditService::log('registration_status_changed', $user, 'Changed status to approved (unsuspended)');

        return back()->with('success', "Account for {$user->full_name} unsuspended.");
    }

    /**
     * Bulk approve selected pending users.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = [];
        }
        $ids = array_filter(array_map('intval', $ids));

        $users = User::whereIn('id', $ids)->where('status', User::STATUS_PENDING)->get();

        DB::transaction(function () use ($users) {
            foreach ($users as $user) {
                $user->forceFill([
                    'status' => User::STATUS_APPROVED,
                    'rejection_reason_code' => null,
                    'rejection_reason_details' => null,
                ])->save();

                ApprovalLog::create([
                    'user_id' => $user->id,
                    'action' => ApprovalLog::ACTION_APPROVED,
                    'performed_by' => Auth::id(),
                    'remarks' => null,
                ]);

                $this->attemptFamilyLink($user);

                AuditService::log('registration_status_changed', $user, 'Changed status to approved (bulk)');
            }
        });

        foreach ($users as $user) {
            $this->notifyRegistrationDecision($user, User::STATUS_APPROVED);
        }

        $count = $users->count();
        if ($count === 0) {
            return back()->with('error', 'No pending registrations were selected or all selected were already processed.');
        }

        return back()->with('success', $count === 1
            ? '1 registration approved.'
            : "{$count} registrations approved.");
    }

    /**
     * Bulk reject selected pending users.
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $payload = $this->validateRejectPayload($request);

        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = [];
        }
        $ids = array_filter(array_map('intval', $ids));

        $users = User::whereIn('id', $ids)->where('status', User::STATUS_PENDING)->get();

        DB::transaction(function () use ($users, $payload) {
            foreach ($users as $user) {
                $this->purgeRejectedRegistration($user, $payload, null, true);
            }
        });

        $count = $users->count();
        if ($count === 0) {
            return back()->with('error', 'No pending registrations were selected or all selected were already processed.');
        }

        return back()->with('success', $count === 1
            ? '1 registration rejected and deleted.'
            : "{$count} registrations rejected and deleted.");
    }

    private function validateRejectPayload(Request $request): array
    {
        $validated = $request->validate([
            'rejection_reason_code' => ['required', 'string', 'in:' . implode(',', array_keys(User::REGISTRATION_REJECTION_REASON_LABELS))],
            'rejection_reason_details' => ['nullable', 'string', 'max:500'],
        ], [
            'rejection_reason_code.required' => 'Please select a rejection reason.',
            'rejection_reason_code.in' => 'Selected rejection reason is invalid.',
        ]);

        $reasonCode = (string) $validated['rejection_reason_code'];
        $details = trim((string) ($validated['rejection_reason_details'] ?? ''));

        if ($reasonCode === 'other' && $details === '') {
            throw ValidationException::withMessages([
                'rejection_reason_details' => 'Please provide details when reason is Other.',
            ]);
        }

        return [
            'rejection_reason_code' => $reasonCode,
            'rejection_reason_details' => $details !== '' ? $details : null,
        ];
    }

    private function buildRejectionAuditRemarks(array $payload, ?string $remarks): string
    {
        $label = User::REGISTRATION_REJECTION_REASON_LABELS[$payload['rejection_reason_code']] ?? $payload['rejection_reason_code'];
        $details = $payload['rejection_reason_details'] ? ' | Details: ' . $payload['rejection_reason_details'] : '';
        $extra = $remarks ? ' | Notes: ' . trim($remarks) : '';

        return "Reason: {$label}{$details}{$extra}";
    }

    private function notifyRegistrationDecision(User $user, string $decision): void
    {
        if ($decision === User::STATUS_APPROVED) {
            NotificationService::notify(
                $user,
                'Registration Approved',
                'Your registration has been approved. You can now log in.',
                'registration',
                $user->id
            );
        } elseif ($decision === User::STATUS_REJECTED) {
            $reason = $user->registrationRejectionReasonLabel();
            $details = $user->rejection_reason_details ? ' Details: ' . $user->rejection_reason_details : '';
            $reasonText = $reason ? " Reason: {$reason}." : '';

            NotificationService::notify(
                $user,
                'Registration Rejected',
                "Your registration has been rejected.{$reasonText}{$details}",
                'registration',
                $user->id
            );
        }

        if (! $user->email) {
            return;
        }

        try {
            $user->notify(new RegistrationDecisionNotification($user, $decision));
        } catch (Throwable $exception) {
            Log::warning('Failed to send registration decision email.', [
                'user_id' => $user->id,
                'decision' => $decision,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function purgeRejectedRegistration(User $user, array $payload, ?string $remarks, bool $bulk = false): void
    {
        $filePaths = array_filter([
            $user->government_id_path,
            $user->pwd_proof_path,
            $user->senior_proof_path,
        ]);

        if (! empty($filePaths)) {
            try {
                Storage::disk('public')->delete($filePaths);
            } catch (Throwable $exception) {
                Log::warning('Failed to delete one or more rejected registration files.', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $applicantSnapshot = sprintf(
            "Applicant: %s | Email: %s | User ID at rejection: %d\n",
            $user->full_name,
            $user->email ?? '—',
            $user->id
        );

        ApprovalLog::create([
            'user_id' => $user->id,
            'action' => ApprovalLog::ACTION_REJECTED,
            'performed_by' => Auth::id(),
            'remarks' => $applicantSnapshot . $this->buildRejectionAuditRemarks($payload, $remarks),
        ]);

        AuditService::log(
            $bulk ? 'registration_rejected_deleted_bulk' : 'registration_rejected_deleted',
            $user,
            'Rejected registration and deleted submitted resident data.'
        );

        $user->delete();
    }

    private function applyPendingQueueFilters(Builder $query, Request $request, array $settings): void
    {
        $sla = $request->string('sla')->toString();
        if ($sla === 'overdue') {
            $query->where('created_at', '<=', now()->subHours($settings['overdue_hours']));
        } elseif ($sla === 'due_soon') {
            $query->where('created_at', '<=', now()->subHours($settings['due_soon_hours']))
                ->where('created_at', '>', now()->subHours($settings['overdue_hours']));
        }

        $risk = $request->string('risk')->toString();
        if ($risk === 'missing_id') {
            $query->where(function ($q) {
                $q->whereNull('government_id_path')->orWhereNull('government_id_type');
            });
        } elseif ($risk === 'underage') {
            $query->whereDate('birthdate', '>', now()->subYears(18)->toDateString());
        } elseif ($risk === 'no_birthdate') {
            $query->whereNull('birthdate');
        }
    }

    /**
     * Attempt to auto-link a non-head resident to their declared head of family.
     */
    private function attemptFamilyLink(User $user): void
    {
        if ($user->head_of_family === 'yes') {
            $household = $this->resolveHeadHousehold($user);
            $user->update([
                'head_of_family_id' => null,
                'household_id' => $household->id,
                'family_link_status' => 'linked',
            ]);

            return;
        }

        if ($user->head_of_family === 'no' && $user->head_first_name && $user->head_last_name) {
            $head = User::where('status', User::STATUS_APPROVED)
                ->where(function ($query) {
                    $query->whereNull('is_suspended')->orWhere('is_suspended', false);
                })
                ->where('head_of_family', 'yes')
                ->whereNull('head_of_family_id')
                ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim($user->head_first_name))])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim($user->head_last_name))])
                ->when($user->head_middle_name, function ($q) use ($user) {
                    $q->whereRaw('LOWER(TRIM(middle_name)) = ?', [mb_strtolower(trim($user->head_middle_name))]);
                })
                ->first();

            if ($head) {
                $household = $this->resolveHeadHousehold($head);
                $user->update([
                    'head_of_family_id' => $head->id,
                    'household_id' => $household->id,
                    'head_of_family' => 'no',
                    'family_link_status' => 'linked',
                ]);
            } else {
                $user->update([
                    'household_id' => null,
                    'family_link_status' => 'pending_link',
                ]);
            }
        }
    }

    private function latestEscalationRun(): ?RegistrationAlertRun
    {
        if (! Schema::hasTable('registration_alert_runs')) {
            return null;
        }

        return RegistrationAlertRun::query()
            ->latest('ran_at')
            ->first();
    }

    private function manualReminderCooldownSeconds(array $settings, ?int $triggeredByUserId = null): int
    {
        $latest = $this->latestManualEscalationRun($triggeredByUserId);
        if (! $latest || ! $latest->ran_at) {
            return 0;
        }

        $nextAllowedAt = $latest->ran_at->copy()->addMinutes((int) $settings['cooldown_minutes']);
        $remaining = now()->diffInSeconds($nextAllowedAt, false);

        return $remaining > 0 ? (int) $remaining : 0;
    }

    private function latestManualEscalationRun(?int $triggeredByUserId = null): ?RegistrationAlertRun
    {
        if (! Schema::hasTable('registration_alert_runs')) {
            return null;
        }

        $query = RegistrationAlertRun::query();

        if (Schema::hasColumn('registration_alert_runs', 'trigger_source')) {
            $query->where('trigger_source', 'manual');
        }

        if ($triggeredByUserId && Schema::hasColumn('registration_alert_runs', 'triggered_by_user_id')) {
            $query->where('triggered_by_user_id', $triggeredByUserId);
        }

        return $query->latest('ran_at')->first();
    }

    private function canManageEscalationSettings(Request $request): bool
    {
        $role = $request->user()?->role;

        return in_array($role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true);
    }

    private function escalationAnalytics(): array
    {
        $defaults = [
            'runs_7d' => 0,
            'sent_7d' => 0,
            'targeted_7d' => 0,
            'success_rate_7d' => 0.0,
            'failed_runs_7d' => 0,
            'manual_runs_7d' => 0,
            'scheduled_runs_7d' => 0,
            'daily' => [],
        ];

        if (! Schema::hasTable('registration_alert_runs')) {
            return $defaults;
        }

        $from = now()->subDays(6)->startOfDay();
        $base7d = RegistrationAlertRun::query()
            ->where('ran_at', '>=', $from);

        $runs7d = (clone $base7d)->count();
        $sent7d = (int) ((clone $base7d)->sum('recipients_sent') ?? 0);
        $targeted7d = (int) ((clone $base7d)->sum('recipients_targeted') ?? 0);
        $failedRuns7d = (clone $base7d)->where('status', 'send_failed')->count();
        $manualRuns7d = Schema::hasColumn('registration_alert_runs', 'trigger_source')
            ? (clone $base7d)->where('trigger_source', 'manual')->count()
            : 0;
        $scheduledRuns7d = Schema::hasColumn('registration_alert_runs', 'trigger_source')
            ? (clone $base7d)->where('trigger_source', 'scheduled')->count()
            : 0;

        $rows = RegistrationAlertRun::query()
            ->where('ran_at', '>=', $from)
            ->selectRaw('DATE(ran_at) as run_date, COUNT(*) as runs_count, SUM(recipients_sent) as recipients_sent_sum, SUM(overdue_count) as overdue_count_sum')
            ->groupByRaw('DATE(ran_at)')
            ->orderBy('run_date')
            ->get();

        $byDate = [];
        foreach ($rows as $row) {
            $byDate[(string) $row->run_date] = [
                'runs' => (int) ($row->runs_count ?? 0),
                'sent' => (int) ($row->recipients_sent_sum ?? 0),
                'overdue' => (int) ($row->overdue_count_sum ?? 0),
            ];
        }

        $daily = [];
        foreach (range(0, 6) as $i) {
            $day = $from->copy()->addDays($i);
            $key = $day->toDateString();
            $metrics = $byDate[$key] ?? ['runs' => 0, 'sent' => 0, 'overdue' => 0];

            $daily[] = [
                'date' => $day->format('M d'),
                'runs' => $metrics['runs'],
                'sent' => $metrics['sent'],
                'overdue' => $metrics['overdue'],
            ];
        }

        return [
            'runs_7d' => $runs7d,
            'sent_7d' => $sent7d,
            'targeted_7d' => $targeted7d,
            'success_rate_7d' => $targeted7d > 0 ? round(($sent7d / $targeted7d) * 100, 1) : 0.0,
            'failed_runs_7d' => $failedRuns7d,
            'manual_runs_7d' => $manualRuns7d,
            'scheduled_runs_7d' => $scheduledRuns7d,
            'daily' => $daily,
        ];
    }

    private function failedQueueStats(): array
    {
        $defaults = [
            'failed_count' => 0,
            'latest_failed_at' => null,
        ];

        if (! Schema::hasTable('failed_jobs')) {
            return $defaults;
        }

        $base = DB::table('failed_jobs')
            ->where('payload', 'like', '%OverdueRegistrationSummaryNotification%');

        return [
            'failed_count' => (clone $base)->count(),
            'latest_failed_at' => (clone $base)->max('failed_at'),
        ];
    }

    private function resolveHeadHousehold(User $head): Household
    {
        $household = $head->householdAsHead;

        if (! $household) {
            $household = Household::create([
                'head_id' => $head->id,
                'purok' => $head->purok ?? '',
            ]);
        } elseif (($head->purok ?? '') !== '' && $household->purok !== $head->purok) {
            $household->update(['purok' => $head->purok]);
        }

        return $household;
    }
}
