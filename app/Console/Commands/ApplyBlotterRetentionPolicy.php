<?php

namespace App\Console\Commands;

use App\Models\Blotter;
use App\Models\BlotterRequest;
use App\Models\Hearing;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ApplyBlotterRetentionPolicy extends Command
{
    protected $signature = 'blotters:apply-retention {--days= : Retention age in days before auto-archiving}';

    protected $description = 'Auto-archive old blotter records based on retention policy safety rules.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: env('BLOTTER_RETENTION_DAYS', 365));
        if ($days < 30) {
            $this->error('Retention days must be at least 30.');

            return self::FAILURE;
        }

        $cutoffDate = Carbon::today()->subDays($days)->toDateString();
        $this->info("Applying blotter retention policy. Cutoff incident date: {$cutoffDate}");

        $eligibleQuery = Blotter::query()
            ->whereNull('deleted_at')
            ->where('status', Blotter::STATUS_ACTIVE)
            ->whereDate('incident_date', '<=', $cutoffDate)
            ->whereDoesntHave('requests', function ($query): void {
                $query->whereIn('status', [
                    BlotterRequest::STATUS_PENDING,
                    BlotterRequest::STATUS_APPROVED,
                ]);
            })
            ->whereDoesntHave('hearings', function ($query): void {
                $query->whereIn('status', [
                    Hearing::STATUS_SCHEDULED,
                    Hearing::STATUS_ONGOING,
                ]);
            });

        $eligibleCount = (clone $eligibleQuery)->count();
        if ($eligibleCount === 0) {
            $this->info('No eligible blotter records found for auto-archiving.');

            return self::SUCCESS;
        }

        $archived = 0;
        $eligibleQuery->orderBy('id')->chunkById(100, function ($blotters) use (&$archived): void {
            foreach ($blotters as $blotter) {
                $blotter->forceFill(['status' => Blotter::STATUS_ARCHIVED])->save();
                $blotter->delete();

                AuditService::log(
                    'blotter_auto_archived_retention',
                    $blotter,
                    "Auto-archived blotter {$blotter->blotter_number} via retention policy."
                );

                $archived++;
            }
        });

        $this->info("Auto-archived {$archived} blotter record(s).");

        return self::SUCCESS;
    }
}

