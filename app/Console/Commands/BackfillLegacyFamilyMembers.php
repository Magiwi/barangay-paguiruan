<?php

namespace App\Console\Commands;

use App\Models\FamilyMember;
use App\Models\Household;
use App\Models\User;
use Illuminate\Console\Command;

class BackfillLegacyFamilyMembers extends Command
{
    protected $signature = 'family:backfill-legacy-members
        {--dry-run : Preview changes without writing to database}
        {--detach-users : After backfill, detach legacy user household links}';

    protected $description = 'Backfill legacy linked resident users into family_members records.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $detachUsers = (bool) $this->option('detach-users');

        $stats = [
            'scanned' => 0,
            'backfilled' => 0,
            'already_exists' => 0,
            'skipped_invalid_head' => 0,
            'detached_users' => 0,
        ];

        $legacyUsers = User::query()
            ->where('role', User::ROLE_RESIDENT)
            ->whereNotNull('head_of_family_id')
            ->where('head_of_family', 'no')
            ->where('family_link_status', 'linked')
            ->orderBy('id')
            ->get();

        foreach ($legacyUsers as $legacyUser) {
            $stats['scanned']++;

            $head = User::query()
                ->whereKey($legacyUser->head_of_family_id)
                ->where('head_of_family', 'yes')
                ->first();

            if (! $head) {
                $stats['skipped_invalid_head']++;
                continue;
            }

            $household = Household::query()
                ->where('head_id', $head->id)
                ->orderBy('id')
                ->first();

            if (! $household && ! $dryRun) {
                $household = Household::create([
                    'head_id' => $head->id,
                    'purok' => $head->purok ?? '',
                ]);
            }

            if (! $household) {
                // Dry-run without existing household still needs a placeholder id for matching.
                $household = new Household(['id' => (int) ($legacyUser->household_id ?: 0)]);
            }

            $existing = FamilyMember::query()
                ->where('head_user_id', $head->id)
                ->where('household_id', (int) $household->id)
                ->whereRaw('LOWER(TRIM(first_name)) = ?', [mb_strtolower(trim((string) $legacyUser->first_name))])
                ->whereRaw('LOWER(TRIM(last_name)) = ?', [mb_strtolower(trim((string) $legacyUser->last_name))])
                ->whereRaw('LOWER(TRIM(COALESCE(middle_name, ""))) = ?', [mb_strtolower(trim((string) ($legacyUser->middle_name ?? '')))])
                ->whereRaw('TRIM(COALESCE(suffix, "")) = ?', [trim((string) ($legacyUser->suffix ?? ''))])
                ->whereDate('birthdate', optional($legacyUser->birthdate)->toDateString())
                ->first();

            if ($existing) {
                $stats['already_exists']++;
            } else {
                if (! $dryRun) {
                    FamilyMember::create([
                        'head_user_id' => $head->id,
                        'household_id' => (int) $household->id,
                        'purok_id' => $head->purok_id,
                        'first_name' => $legacyUser->first_name,
                        'middle_name' => $legacyUser->middle_name,
                        'last_name' => $legacyUser->last_name,
                        'suffix' => $legacyUser->suffix,
                        'birthdate' => $legacyUser->birthdate,
                        'age' => $legacyUser->birthdate ? $legacyUser->birthdate->age : $legacyUser->age,
                        'gender' => $legacyUser->gender,
                        'contact_number' => $legacyUser->contact_number,
                        'relationship_to_head' => $legacyUser->relationship_to_head,
                        'house_no' => $head->house_no,
                        'street_name' => $head->street_name,
                        'purok' => $head->purok,
                        'resident_type' => $head->resident_type,
                    ]);
                }
                $stats['backfilled']++;
            }

            if ($detachUsers) {
                if (! $dryRun) {
                    $legacyUser->update([
                        'head_of_family_id' => null,
                        'household_id' => null,
                        'family_link_status' => 'unlinked',
                    ]);
                }
                $stats['detached_users']++;
            }
        }

        $mode = $dryRun ? 'DRY RUN' : 'APPLIED';
        $this->info("Legacy family member backfill {$mode}:");
        $this->line("- Legacy linked users scanned: {$stats['scanned']}");
        $this->line("- New family_members backfilled: {$stats['backfilled']}");
        $this->line("- Already existing family_members: {$stats['already_exists']}");
        $this->line("- Skipped invalid head links: {$stats['skipped_invalid_head']}");
        $this->line("- Legacy users detached: {$stats['detached_users']}" . ($detachUsers ? '' : ' (use --detach-users to enable)'));

        return self::SUCCESS;
    }
}
