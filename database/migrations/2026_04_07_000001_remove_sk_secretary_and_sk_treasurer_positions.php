<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SK Secretary / SK Treasurer are not used for this barangay; remove positions and rely on SK Chairman + SK Kagawad only.
     */
    public function up(): void
    {
        $remove = ['SK Secretary', 'SK Treasurer'];

        DB::table('positions')->whereIn('name', $remove)->delete();

        $sortOrder = [
            'Barangay Chairman' => 1,
            'Barangay Secretary' => 2,
            'Barangay Treasurer' => 3,
            'Barangay Investigator' => 4,
            'Kagawad' => 5,
            'SK Chairman' => 6,
            'SK Kagawad' => 7,
            'Staff Admin Officer' => 8,
            'Staff Records Officer' => 9,
            'Staff Public Assistance Officer' => 10,
            'Staff Blotter Officer' => 11,
        ];

        foreach ($sortOrder as $name => $order) {
            DB::table('positions')->where('name', $name)->update(['sort_order' => $order]);
        }
    }

    public function down(): void
    {
        DB::table('positions')->insert([
            [
                'name' => 'SK Secretary',
                'max_seats' => 1,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SK Treasurer',
                'max_seats' => 1,
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('positions')->where('name', 'SK Kagawad')->update(['sort_order' => 9]);
        DB::table('positions')->where('name', 'Staff Admin Officer')->update(['sort_order' => 10]);
        DB::table('positions')->where('name', 'Staff Records Officer')->update(['sort_order' => 11]);
        DB::table('positions')->where('name', 'Staff Public Assistance Officer')->update(['sort_order' => 12]);
        DB::table('positions')->where('name', 'Staff Blotter Officer')->update(['sort_order' => 13]);
    }
};
