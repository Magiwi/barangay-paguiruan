<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature = 'user:make-super-admin
                            {identifier : User ID or email}
                            {--demote : Demote to admin instead}';

    protected $description = 'Promote a user account to super_admin (or demote to admin)';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('identifier'));

        $user = User::query()
            ->when(is_numeric($identifier), fn ($query) => $query->where('id', (int) $identifier))
            ->when(! is_numeric($identifier), fn ($query) => $query->where('email', $identifier))
            ->first();

        if (! $user) {
            $this->error('User not found. Use a valid ID or email.');

            return self::FAILURE;
        }

        if ($user->status !== User::STATUS_APPROVED) {
            $this->warn("User status is '{$user->status}'. Continuing anyway.");
        }

        $newRole = $this->option('demote') ? User::ROLE_ADMIN : User::ROLE_SUPER_ADMIN;
        $oldRole = (string) $user->role;

        if ($oldRole === $newRole) {
            $this->info("No changes made. User is already '{$newRole}'.");

            return self::SUCCESS;
        }

        $user->forceFill(['role' => $newRole])->save();

        $this->info("Updated user #{$user->id} ({$user->email}) role: {$oldRole} -> {$newRole}");

        return self::SUCCESS;
    }
}
