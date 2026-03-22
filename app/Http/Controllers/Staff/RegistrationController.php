<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Admin\ApprovalHistoryController;
use App\Http\Controllers\Admin\PendingRegistrationsController;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Controller;
use App\Models\RegistrationAlertRun;
use App\Models\User;
use App\Services\RegistrationEscalationConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    private array $viewData = [
        'layout' => 'layouts.staff',
        'routePrefix' => 'staff',
    ];

    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('registrations')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(Request $request): View
    {
        $settings = RegistrationEscalationConfig::get();
        $status = $request->get('status', 'pending');
        $query = User::countable();

        if (in_array($status, ['pending', 'approved', 'rejected', 'suspended'], true)) {
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

        $counts = [
            'pending' => User::countable()->where('status', User::STATUS_PENDING)->count(),
            'approved' => User::countable()->where('status', User::STATUS_APPROVED)->count(),
            'rejected' => User::countable()->where('status', User::STATUS_REJECTED)->count(),
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

        return view('admin.pending-registrations.index', array_merge(compact('users', 'status', 'counts', 'rejectionReasonOptions', 'pendingQueueStats', 'latestEscalationRun', 'manualReminderCooldownSeconds', 'escalationAnalytics', 'failedQueueStats', 'settings'), $this->viewData));
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        app(PendingRegistrationsController::class)->approve($request, $user);

        return back()->with('success', "Registration for {$user->full_name} approved.");
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        return app(PendingRegistrationsController::class)->reject($request, $user);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        app(PendingRegistrationsController::class)->suspend($request, $user);

        return back()->with('success', "Account for {$user->full_name} suspended.");
    }

    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        app(PendingRegistrationsController::class)->unsuspend($request, $user);

        return back()->with('success', "Account for {$user->full_name} unsuspended.");
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        return app(PendingRegistrationsController::class)->bulkApprove($request);
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        return app(PendingRegistrationsController::class)->bulkReject($request);
    }

    public function sendReminderNow(Request $request): RedirectResponse
    {
        return app(PendingRegistrationsController::class)->sendReminderNow($request);
    }

    public function retryFailedReminderJobs(Request $request): RedirectResponse
    {
        return app(PendingRegistrationsController::class)->retryFailedReminderJobs($request);
    }

    public function approvalHistory(Request $request): View
    {
        $view = app(ApprovalHistoryController::class)->index($request);

        return $view->with($this->viewData);
    }

    public function verifications(Request $request): View
    {
        $view = app(VerificationController::class)->index($request);

        return $view->with($this->viewData);
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

        $base = \Illuminate\Support\Facades\DB::table('failed_jobs')
            ->where('payload', 'like', '%OverdueRegistrationSummaryNotification%');

        return [
            'failed_count' => (clone $base)->count(),
            'latest_failed_at' => (clone $base)->max('failed_at'),
        ];
    }
}
