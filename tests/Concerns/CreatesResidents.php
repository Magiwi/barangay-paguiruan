<?php

namespace Tests\Concerns;

use App\Models\Purok;
use App\Models\User;

/**
 * Shared factory for approved User rows in feature tests (avoids duplicating createResident helpers).
 */
trait CreatesResidents
{
    protected function createResidentUser(array $overrides = []): User
    {
        $purok = isset($overrides['purok_id'])
            ? Purok::findOrFail($overrides['purok_id'])
            : Purok::firstOrCreate(['name' => $overrides['purok_name'] ?? 'Purok Test']);

        unset($overrides['purok_name']);

        $defaults = [
            'first_name' => 'Test',
            'middle_name' => 'M',
            'last_name' => 'Resident',
            'suffix' => null,
            'house_no' => '10',
            'purok' => $purok->name,
            'purok_id' => $purok->id,
            'street_name' => 'Main St',
            'contact_number' => '+639171234567',
            'age' => 30,
            'gender' => 'male',
            'birthdate' => now()->subYears(30)->toDateString(),
            'civil_status' => 'single',
            'head_of_family' => 'no',
            'resident_type' => 'permanent',
            'email' => 'resident.' . uniqid() . '@example.com',
            'password' => 'password123',
            'head_of_family_id' => null,
            'family_link_status' => null,
            'household_id' => null,
            'relationship_to_head' => null,
            'permanent_house_no' => null,
            'permanent_street' => null,
            'permanent_region' => null,
            'permanent_barangay' => null,
            'permanent_city' => null,
            'permanent_province' => null,
        ];

        $data = array_merge($defaults, array_intersect_key($overrides, $defaults));
        $user = User::create($data);
        $user->forceFill([
            'role' => $overrides['role'] ?? User::ROLE_RESIDENT,
            'status' => $overrides['status'] ?? User::STATUS_APPROVED,
            'is_suspended' => $overrides['is_suspended'] ?? false,
        ])->save();

        return $user->fresh();
    }

    /**
     * Approved admin user (typical for actingAs() in admin module tests).
     */
    protected function createAdminUser(array $overrides = []): User
    {
        return $this->createResidentUser(array_merge([
            'role' => User::ROLE_ADMIN,
            'head_of_family' => 'no',
        ], $overrides));
    }
}
