<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed or recover the default super admin account.
     */
    public function run(): void
    {
        $email = (string) env('SUPER_ADMIN_EMAIL', 'superadmin@barangaypaguiruan.local');
        $password = (string) env('SUPER_ADMIN_PASSWORD', 'ChangeMe123!');
        $firstName = (string) env('SUPER_ADMIN_FIRST_NAME', 'System');
        $lastName = (string) env('SUPER_ADMIN_LAST_NAME', 'Administrator');

        $user = User::where('email', $email)->first();

        $payload = [
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => $lastName,
            'suffix' => null,
            'house_no' => 'N/A',
            'purok' => 'N/A',
            'street_name' => 'N/A',
            'contact_number' => '+639000000000',
            'age' => 30,
            'gender' => 'other',
            'birthdate' => '1995-01-01',
            'civil_status' => 'single',
            'head_of_family' => 'yes',
            'resident_type' => 'permanent',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_APPROVED,
            'is_suspended' => false,
        ];

        if ($user) {
            // Keep profile data intact; enforce only critical account controls.
            $user->forceFill([
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => User::STATUS_APPROVED,
                'is_suspended' => false,
            ])->save();

            return;
        }

        User::create($payload);
    }
}
