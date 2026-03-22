<?php

namespace Database\Seeders;

use App\Models\Purok;
use Illuminate\Database\Seeder;

class PurokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $puroks = [
            ['name' => 'Purok 1', 'description' => null, 'is_active' => true],
            ['name' => 'Purok 2', 'description' => null, 'is_active' => true],
            ['name' => 'Purok 3', 'description' => null, 'is_active' => true],
            ['name' => 'Purok 4', 'description' => null, 'is_active' => true],
            ['name' => 'Purok 5', 'description' => null, 'is_active' => true],
            ['name' => 'Purok 6', 'description' => null, 'is_active' => true],
            ['name' => 'Purok 7', 'description' => null, 'is_active' => true],
        ];

        foreach ($puroks as $purok) {
            Purok::firstOrCreate(
                ['name' => $purok['name']],
                $purok
            );
        }
    }
}
