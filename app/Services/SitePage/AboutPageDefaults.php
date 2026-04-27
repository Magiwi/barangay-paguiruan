<?php

namespace App\Services\SitePage;

/**
 * Default About page sections (matches legacy hardcoded about.blade.php).
 *
 * @phpstan-type Section array{id: string, type: string, visible: bool, data: array<string, mixed>}
 */
final class AboutPageDefaults
{
    public const SCHEMA_VERSION = 1;

    /** @var list<string> */
    public const SECTION_TYPES = [
        'hero',
        'intro',
        'location',
        'gallery',
        'stats',
        'mission_vision',
        'priorities',
        'officials',
        'contact',
    ];

    /**
     * @return list<Section>
     */
    public static function sections(): array
    {
        return [
            [
                'id' => 'sec-hero',
                'type' => 'hero',
                'visible' => true,
                'data' => [
                    'badge' => 'Republic of the Philippines',
                    'title_line1' => 'About Barangay',
                    'title_line2' => 'Paguiruan',
                    'subtitle' => 'A growing community in Floridablanca, Pampanga, guided by active local leadership, strong bayanihan spirit, and continuous programs that support families, youth, and senior citizens.',
                    'primary_button_label' => 'Learn More',
                    'primary_button_href' => '#about-system',
                    'secondary_button_label' => 'Contact Us',
                    'secondary_button_href' => '#contact',
                    'hero_visual' => 'icon',
                    'hero_image_path' => null,
                ],
            ],
            [
                'id' => 'sec-intro',
                'type' => 'intro',
                'visible' => true,
                'data' => [
                    'kicker' => 'About the Barangay',
                    'heading' => 'About Barangay Paguiruan',
                    'body' => 'Barangay Paguiruan is one of the active communities in Floridablanca, Pampanga, known for strong local leadership, close community ties, and resident-centered public service. The barangay continuously works to improve everyday services, community programs, and communication so residents can access support, information, and assistance more efficiently.',
                    'features' => [
                        [
                            'accent' => 'blue',
                            'title' => 'Active Community Programs',
                            'body' => 'Barangay Paguiruan continues to support programs for health, sanitation, youth development, and livelihood for local families.',
                        ],
                        [
                            'accent' => 'green',
                            'title' => 'Transparent Local Governance',
                            'body' => 'Barangay officials promote accountability through open communication, public advisories, and regular community coordination.',
                        ],
                        [
                            'accent' => 'blue',
                            'title' => 'Inclusive Public Service',
                            'body' => 'Barangay services are focused on accessibility and fairness so residents from all puroks can receive timely support and assistance.',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'sec-location',
                'type' => 'location',
                'visible' => true,
                'data' => [
                    'kicker' => 'Location',
                    'heading' => 'Our Barangay',
                    'paragraphs' => [
                        'Barangay Paguiruan is located in the Municipality of Floridablanca, Province of Pampanga, in Central Luzon, Philippines. The barangay is a vibrant community known for its warm residents and strong sense of local governance.',
                        'The Barangay Hall serves as the center of governance and community services, offering document processing, dispute resolution, and various social programs.',
                    ],
                    'bullets' => [
                        'Strategic location in Floridablanca, Pampanga',
                        'Accessible roads to town proper and neighboring barangays',
                        'Peaceful, community-centered environment',
                    ],
                    'purok_label' => 'View by Purok',
                    'purok_options' => [
                        ['value' => 'default', 'label' => 'Barangay Paguiruan (Overview)', 'map_embed_url' => ''],
                        ['value' => 'purok1', 'label' => 'Purok 1', 'map_embed_url' => ''],
                        ['value' => 'purok2', 'label' => 'Purok 2', 'map_embed_url' => ''],
                        ['value' => 'purok3', 'label' => 'Purok 3', 'map_embed_url' => ''],
                        ['value' => 'purok4', 'label' => 'Purok 4', 'map_embed_url' => ''],
                        ['value' => 'purok5', 'label' => 'Purok 5', 'map_embed_url' => ''],
                        ['value' => 'purok6', 'label' => 'Purok 6', 'map_embed_url' => ''],
                    ],
                    'map_embed_url' => '',
                    'map_caption' => 'Barangay Paguiruan, Floridablanca, Pampanga',
                ],
            ],
            [
                'id' => 'sec-gallery',
                'type' => 'gallery',
                'visible' => true,
                'data' => [
                    'kicker' => 'Gallery',
                    'heading' => 'Barangay Photo Highlights',
                    'subtitle' => 'Key locations and community spaces in Barangay Paguiruan.',
                    'slides' => [
                        ['path' => 'images/ui design/barangayhall.jpg', 'label' => 'Barangay Hall', 'alt' => ''],
                        ['path' => 'images/ui design/health center.jpg', 'label' => 'Health Center', 'alt' => ''],
                        ['path' => 'images/ui design/park1.jpg', 'label' => 'Community Park', 'alt' => ''],
                        ['path' => 'images/ui design/street1.jpg', 'label' => 'Barangay Street', 'alt' => ''],
                    ],
                ],
            ],
            [
                'id' => 'sec-stats',
                'type' => 'stats',
                'visible' => true,
                'data' => [
                    'kicker' => 'Demographics',
                    'heading' => 'Community at a Glance',
                    'labels' => [
                        'residents' => 'Total Residents',
                        'households' => 'Households',
                        'puroks' => 'Puroks (Zones)',
                    ],
                ],
            ],
            [
                'id' => 'sec-mission',
                'type' => 'mission_vision',
                'visible' => true,
                'data' => [
                    'kicker' => 'Our Purpose',
                    'heading' => 'Mission & Vision',
                    'mission_title' => 'Our Mission',
                    'mission_body' => 'To provide responsive, fair, and people-centered public service that protects community welfare, strengthens peace and order, and supports sustainable growth for every family in Barangay Paguiruan.',
                    'vision_title' => 'Our Vision',
                    'vision_body' => 'A progressive and united Barangay Paguiruan where governance is transparent, opportunities are inclusive, and every resident actively contributes to a safe and thriving community.',
                ],
            ],
            [
                'id' => 'sec-priorities',
                'type' => 'priorities',
                'visible' => true,
                'data' => [
                    'kicker' => 'Community Priorities',
                    'heading' => 'What Matters in Barangay Paguiruan',
                    'intro' => 'Barangay Paguiruan focuses on programs and actions that directly improve daily life, strengthen community relationships, and build long-term local resilience.',
                    'cards' => [
                        [
                            'title' => 'Health and Cleanliness',
                            'body' => 'Continuous support for health campaigns, clean surroundings, and safe community spaces for all residents.',
                        ],
                        [
                            'title' => 'Youth and Education Support',
                            'body' => 'Programs that encourage leadership, learning, and civic participation among children and young adults.',
                        ],
                        [
                            'title' => 'Peace and Order',
                            'body' => 'Strong coordination with local leaders and authorities to keep neighborhoods safe and maintain harmony.',
                        ],
                        [
                            'title' => 'Disaster Preparedness',
                            'body' => 'Preparedness planning and community awareness help residents respond quickly during emergencies.',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'sec-officials',
                'type' => 'officials',
                'visible' => true,
                'data' => [
                    'kicker' => 'Leadership',
                    'heading' => 'Barangay Officials',
                    'subtitle' => 'Current officials including chairman, kagawads, and other active leadership roles.',
                ],
            ],
            [
                'id' => 'sec-contact',
                'type' => 'contact',
                'visible' => true,
                'data' => [
                    'kicker' => 'Get in Touch',
                    'heading' => 'Contact Information',
                    'use_site_settings' => true,
                    'label_address' => 'Address',
                    'label_phone' => 'Phone',
                    'label_email' => 'Email',
                    'manual_address_html' => 'Barangay Paguiruan,<br>Floridablanca, Pampanga',
                    'manual_phone' => '(096) 1254-9690',
                    'manual_email' => 'barangaypaguiruan2024@gmail.com',
                    'office_hours_line' => 'Office Hours: Monday – Friday, 8:00 AM – 5:00 PM',
                ],
            ],
        ];
    }

    /**
     * Default purok → map embed URLs (used by location section JS).
     *
     * @return array<string, string>
     */
    public static function defaultPurokMapUrls(): array
    {
        return [
            'default' => 'https://maps.google.com/maps?q=Barangay%20Paguiruan%20Floridablanca%20Pampanga&z=15&output=embed',
            'purok1' => 'https://maps.google.com/maps?q=Paguiruan%20Purok%201%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok2' => 'https://maps.google.com/maps?q=Paguiruan%20Purok%202%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok3' => 'https://maps.google.com/maps?q=Paguiruan%20Purok%203%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok4' => 'https://maps.google.com/maps?q=Paguiruan%20Purok%204%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok5' => 'https://maps.google.com/maps?q=Paguiruan%20Purok%205%20Floridablanca%20Pampanga&z=16&output=embed',
            'purok6' => 'https://maps.google.com/maps?q=Paguiruan%20Purok%206%20Floridablanca%20Pampanga&z=16&output=embed',
        ];
    }
}
