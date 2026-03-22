<?php

namespace App\Console\Commands;

use App\Models\RegistrationAlertRun;
use App\Models\User;
use App\Notifications\OverdueRegistrationSummaryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class NotifyOverdueRegistrations extends Command
{
    protected $signature = 'registrations:notify-overdue
        {--hours=48 : Pending threshold in hours for overdue queue}
        {--trigger-source=scheduled : Trigger source (scheduled/manual)}
        {--triggered-by= : User ID who manually triggered this run}
        {--trigger-reason= : Reason text for manual trigger}';

    protected $description = 'Send email reminders to registration managers when pending registrations are overdue';

    public function handle(): int
    {
        $thresholdHours = max(1, (int) $this->option('hours'));
        $triggerSource = (string) $this->option('trigger-source');
        $triggeredByUserId = $this->option('triggered-by') !== null ? (int) $this->option('triggered-by') : null;
        $triggerReason = $this->option('trigger-reason') !== null ? trim((string) $this->option('trigger-reason')) : null;

        $overdueBase = User::query()
            ->where('role', User::ROLE_RESIDENT)
            ->where('status', User::STATUS_PENDING)
            ->where('created_at', '<=', now()->subHours($thresholdHours));

        $overdueCount = (clone $overdueBase)->count();
        $summary = [
            'overdue' => $overdueCount,
            'due_soon' => User::query()
                ->where('role', User::ROLE_RESIDENT)
                ->where('status', User::STATUS_PENDING)
                ->where('created_at', '<=', now()->subHours(24))
                ->where('created_at', '>', now()->subHours($thresholdHours))
                ->count(),
            'missing_id' => User::query()
                ->where('role', User::ROLE_RESIDENT)
                ->where('status', User::STATUS_PENDING)
                ->where(function ($query) {
                    $query->whereNull('government_id_type')
                        ->orWhereNull('government_id_path');
                })
                ->count(),
            'oldest_pending_at' => optional(
                (clone $overdueBase)->oldest('created_at')->first(['created_at'])
            )?->created_at?->timezone('Asia/Manila')?->format('M d, Y h:i A'),
        ];

        if ($overdueCount === 0) {
            $this->recordRun(
                thresholdHours: $thresholdHours,
                summary: $summary,
                recipientsTargeted: 0,
                recipientsSent: 0,
                status: 'no_overdue',
                notes: "No overdue pending registrations (threshold: {$thresholdHours}h).",
                triggerSource: $triggerSource,
                triggeredByUserId: $triggeredByUserId,
                triggerReason: $triggerReason
            );

            $this->info("No overdue pending registrations (threshold: {$thresholdHours}h).");

            return self::SUCCESS;
        }

        $recipients = User::query()
            ->whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->whereNotNull('email')
            ->with('staffPermission')
            ->get()
            ->filter(fn (User $user) => $user->canAccess('registrations'));

        if ($recipients->isEmpty()) {
            $this->recordRun(
                thresholdHours: $thresholdHours,
                summary: $summary,
                recipientsTargeted: 0,
                recipientsSent: 0,
                status: 'no_recipients',
                notes: 'No staff/admin recipients with registration module access and valid email.',
                triggerSource: $triggerSource,
                triggeredByUserId: $triggeredByUserId,
                triggerReason: $triggerReason
            );

            $this->warn('No staff/admin recipients with registration module access and valid email.');

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new OverdueRegistrationSummaryNotification($summary, $thresholdHours));
                $sent++;
            } catch (Throwable $exception) {
                Log::warning('Failed to send overdue registration summary email.', [
                    'recipient_id' => $recipient->id,
                    'email' => $recipient->email,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->recordRun(
            thresholdHours: $thresholdHours,
            summary: $summary,
            recipientsTargeted: $recipients->count(),
            recipientsSent: $sent,
            status: $sent > 0 ? 'sent' : 'send_failed',
            notes: "Overdue registration summary send result: {$sent}/{$recipients->count()} recipients.",
            triggerSource: $triggerSource,
            triggeredByUserId: $triggeredByUserId,
            triggerReason: $triggerReason
        );

        $this->info("Overdue registration summary sent to {$sent} recipient(s).");

        return self::SUCCESS;
    }

    private function recordRun(
        int $thresholdHours,
        array $summary,
        int $recipientsTargeted,
        int $recipientsSent,
        string $status,
        ?string $notes = null,
        string $triggerSource = 'scheduled',
        ?int $triggeredByUserId = null,
        ?string $triggerReason = null
    ): void {
        if (! Schema::hasTable('registration_alert_runs')) {
            return;
        }

        $payload = [
            'command_name' => $this->getName() ?: 'registrations:notify-overdue',
            'threshold_hours' => $thresholdHours,
            'overdue_count' => (int) ($summary['overdue'] ?? 0),
            'due_soon_count' => (int) ($summary['due_soon'] ?? 0),
            'missing_id_count' => (int) ($summary['missing_id'] ?? 0),
            'recipients_targeted' => $recipientsTargeted,
            'recipients_sent' => $recipientsSent,
            'status' => $status,
            'notes' => $notes,
            'ran_at' => now(),
        ];

        if (Schema::hasColumn('registration_alert_runs', 'trigger_source')) {
            $payload['trigger_source'] = $triggerSource === 'manual' ? 'manual' : 'scheduled';
        }
        if (Schema::hasColumn('registration_alert_runs', 'triggered_by_user_id')) {
            $payload['triggered_by_user_id'] = $triggeredByUserId;
        }
        if (Schema::hasColumn('registration_alert_runs', 'trigger_reason')) {
            $payload['trigger_reason'] = $triggerReason !== '' ? $triggerReason : null;
        }

        RegistrationAlertRun::create($payload);
    }
}
