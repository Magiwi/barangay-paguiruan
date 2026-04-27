<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Sample published CMS page (Phase 1). Edit content in Admin → Site pages.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about-barangay',
                'title' => 'About Barangay Paguiruan',
                'body' => <<<'MD'
## Our barangay

**Barangay Paguiruan** is one of the barangays of **Floridablanca**, Pampanga. Edit this page in **Admin → Site pages** to add history, mission/vision, and public service priorities.

Residents may use the **e-Governance portal** to request certificates, file reports, and follow announcements.

MD,
            ],
        ];

        foreach ($pages as $row) {
            Page::firstOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'body' => $row['body'],
                    'status' => Page::STATUS_PUBLISHED,
                    'published_at' => now(),
                ]
            );
        }
    }
}
