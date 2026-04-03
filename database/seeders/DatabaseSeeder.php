<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed default puroks
        $this->call(PurokSeeder::class);

        // Seed default positions
        $this->call(PositionSeeder::class);

        // Seed or recover default super admin account
        $this->call(SuperAdminSeeder::class);

        // Local-only: optional seeder file (gitignored — see PendingRegistrationUsersSeeder.php)
        if (app()->isLocal() && class_exists(PendingRegistrationUsersSeeder::class)) {
            $this->call(PendingRegistrationUsersSeeder::class);
        }

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
