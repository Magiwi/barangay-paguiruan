<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\Purok;
use App\Models\StaffPermission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Local-only demo accounts so you can wipe the DB and run `php artisan db:seed`
 * without registering residents manually. Disabled in production by DatabaseSeeder.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $passwordPlain = (string) env('DEV_SEED_PASSWORD', 'TestPass123!');
        $password = Hash::make($passwordPlain);

        $purok = Purok::query()->orderBy('id')->first();
        if (! $purok) {
            $this->command?->warn('DemoUsersSeeder skipped: run PurokSeeder first.');

            return;
        }

        $base = [
            'middle_name' => 'M',
            'suffix' => null,
            'house_no' => '100',
            'street_name' => 'Demo Street',
            'sitio_subdivision' => null,
            'contact_number' => '+639171111111',
            'gender' => 'male',
            'civil_status' => 'married',
            'resident_type' => 'permanent',
            'purok_id' => $purok->id,
            'purok' => $purok->name,
            'password' => $password,
        ];

        $head = User::updateOrCreate(
            ['email' => 'demo.head@barangay.local'],
            array_merge($base, [
                'first_name' => 'Demo',
                'last_name' => 'Puno',
                'age' => 45,
                'birthdate' => '1980-06-15',
                'head_of_family' => User::HEAD_YES,
                'head_of_family_id' => null,
                'household_id' => null,
                'family_link_status' => null,
                'relationship_to_head' => null,
            ])
        );
        $head->forceFill([
            'role' => User::ROLE_RESIDENT,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ])->save();

        $household = Household::firstOrCreate(
            ['head_id' => $head->id],
            ['purok' => $head->purok]
        );
        $head->forceFill(['household_id' => $household->id])->save();

        $member = User::updateOrCreate(
            ['email' => 'demo.member@barangay.local'],
            array_merge($base, [
                'first_name' => 'Demo',
                'last_name' => 'Anak',
                'age' => 20,
                'birthdate' => '2005-03-10',
                'head_of_family' => 'no',
                'head_of_family_id' => $head->id,
                'household_id' => $household->id,
                'family_link_status' => 'linked',
                'relationship_to_head' => 'daughter',
            ])
        );
        $member->forceFill([
            'role' => User::ROLE_RESIDENT,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ])->save();

        $staff = User::updateOrCreate(
            ['email' => 'demo.staff@barangay.local'],
            array_merge($base, [
                'first_name' => 'Demo',
                'last_name' => 'Staff',
                'age' => 35,
                'birthdate' => '1990-01-20',
                'head_of_family' => 'no',
                'head_of_family_id' => null,
                'household_id' => null,
                'family_link_status' => null,
                'relationship_to_head' => null,
            ])
        );
        $staff->forceFill([
            'role' => User::ROLE_STAFF,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ])->save();

        StaffPermission::updateOrCreate(
            ['user_id' => $staff->id],
            [
                'can_manage_registrations' => true,
                'can_manage_blotter' => true,
                'can_manage_announcements' => true,
                'can_manage_complaints' => true,
                'can_manage_reports' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'demo.admin@barangay.local'],
            array_merge($base, [
                'first_name' => 'Demo',
                'last_name' => 'Admin',
                'age' => 40,
                'birthdate' => '1985-08-08',
                'head_of_family' => 'no',
                'head_of_family_id' => null,
                'household_id' => null,
                'family_link_status' => null,
                'relationship_to_head' => null,
            ])
        );
        $admin->forceFill([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ])->save();

        if ($this->command) {
            $this->command->info('Demo users ready (local only). Password from DEV_SEED_PASSWORD or default TestPass123!');
            $this->command->table(
                ['Email', 'Role'],
                [
                    ['demo.head@barangay.local', 'resident (head)'],
                    ['demo.member@barangay.local', 'resident (linked member)'],
                    ['demo.staff@barangay.local', 'staff (all modules)'],
                    ['demo.admin@barangay.local', 'admin'],
                ]
            );
        }
    }
}
