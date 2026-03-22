<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportPotentialDuplicateResidents extends Command
{
    protected $signature = 'residents:report-duplicates {--limit=50 : Max duplicate groups to display}';

    protected $description = 'Report probable duplicate resident records based on full name + birthdate.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $groups = User::query()
            ->selectRaw('LOWER(TRIM(first_name)) as first_name_key')
            ->selectRaw('LOWER(TRIM(COALESCE(middle_name, ""))) as middle_name_key')
            ->selectRaw('LOWER(TRIM(last_name)) as last_name_key')
            ->selectRaw('TRIM(COALESCE(suffix, "")) as suffix_key')
            ->selectRaw('DATE(birthdate) as birthdate_key')
            ->selectRaw('COUNT(*) as duplicate_count')
            ->where('role', User::ROLE_RESIDENT)
            ->whereNotNull('birthdate')
            ->groupBy([
                DB::raw('LOWER(TRIM(first_name))'),
                DB::raw('LOWER(TRIM(COALESCE(middle_name, "")))'),
                DB::raw('LOWER(TRIM(last_name))'),
                DB::raw('TRIM(COALESCE(suffix, ""))'),
                DB::raw('DATE(birthdate)'),
            ])
            ->having('duplicate_count', '>', 1)
            ->orderByDesc('duplicate_count')
            ->limit($limit)
            ->get();

        if ($groups->isEmpty()) {
            $this->info('No probable duplicate resident groups found.');

            return self::SUCCESS;
        }

        $this->warn('Potential duplicate resident groups:');

        foreach ($groups as $index => $group) {
            $first = ucfirst((string) $group->first_name_key);
            $middle = ucfirst((string) $group->middle_name_key);
            $last = ucfirst((string) $group->last_name_key);
            $suffix = (string) $group->suffix_key;
            $birthdate = (string) $group->birthdate_key;
            $fullName = trim("{$first} {$middle} {$last} {$suffix}");

            $this->line('');
            $this->line(($index + 1) . ". {$fullName} | DOB: {$birthdate} | Matches: {$group->duplicate_count}");

            $records = User::query()
                ->where('role', User::ROLE_RESIDENT)
                ->whereDate('birthdate', $birthdate)
                ->whereRaw('LOWER(TRIM(first_name)) = ?', [$group->first_name_key])
                ->whereRaw('LOWER(TRIM(COALESCE(middle_name, ""))) = ?', [$group->middle_name_key])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [$group->last_name_key])
                ->whereRaw('TRIM(COALESCE(suffix, "")) = ?', [$group->suffix_key])
                ->orderBy('id')
                ->get(['id', 'email', 'contact_number', 'status', 'head_of_family_id', 'household_id', 'family_link_status']);

            foreach ($records as $record) {
                $this->line(sprintf(
                    '   - ID:%d | Email:%s | Contact:%s | Status:%s | Head:%s | Household:%s | Link:%s',
                    $record->id,
                    (string) ($record->email ?: '—'),
                    (string) ($record->contact_number ?: '—'),
                    (string) $record->status,
                    (string) ($record->head_of_family_id ?: '—'),
                    (string) ($record->household_id ?: '—'),
                    (string) ($record->family_link_status ?: '—')
                ));
            }
        }

        return self::SUCCESS;
    }
}

