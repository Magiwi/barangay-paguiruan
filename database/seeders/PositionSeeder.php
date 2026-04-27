<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Barangay Chairman', 'max_seats' => 1, 'sort_order' => 1],
            ['name' => 'Barangay Secretary', 'max_seats' => 1, 'sort_order' => 2],
            ['name' => 'Barangay Treasurer', 'max_seats' => 1, 'sort_order' => 3],
            ['name' => 'Barangay Investigator', 'max_seats' => 1, 'sort_order' => 4],
            ['name' => 'Kagawad', 'max_seats' => 7, 'sort_order' => 5],
            ['name' => 'SK Chairman', 'max_seats' => 1, 'sort_order' => 6],
            ['name' => 'SK Kagawad', 'max_seats' => 7, 'sort_order' => 7],
            ['name' => 'Staff Admin Officer', 'max_seats' => 1, 'sort_order' => 8],
            ['name' => 'Staff Records Officer', 'max_seats' => 1, 'sort_order' => 9],
            ['name' => 'Staff Public Assistance Officer', 'max_seats' => 1, 'sort_order' => 10],
            ['name' => 'Staff Blotter Officer', 'max_seats' => 1, 'sort_order' => 11],
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(
                ['name' => $position['name']],
                $position
            );
        }
    }
}
