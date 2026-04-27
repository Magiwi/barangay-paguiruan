<?php

namespace App\Support;

/**
 * Committee labels for officials who chair a standing committee (Kagawad, SK).
 * Barangay Kagawad list matches certificate / permit templates (Barangay Paguiruan).
 */
class OfficialCommittees
{
    /** @var array<string, string> */
    public const BARANGAY_KAGAWAD = [
        'environment' => 'Committee on Environment',
        'transportation' => 'Committee on Transportation',
        'appropriation_agriculture' => 'Committee on Appropriation and Agriculture',
        'peace_order' => 'Committee on Peace and Order',
        'public_works' => 'Committee on Public Works',
        'education' => 'Committee on Education',
        'health' => 'Committee on Health',
    ];

    /** SK Chairman — matches certificate (Sports, Youth, Development). */
    public const SK_CHAIRMAN = [
        'sports_youth_development' => 'Committee on Sports, Youth, and Development',
    ];

    /**
     * SK Kagawad — one committee per slot (typical SK sanggunian set; edit here if your barangay differs).
     *
     * @var array<string, string>
     */
    public const SK_KAGAWAD = [
        'sports_youth_development' => 'Committee on Sports, Youth, and Development',
        'education_culture' => 'Committee on Education and Culture',
        'health_environment' => 'Committee on Health and Environment',
        'livelihood' => 'Committee on Livelihood and Entrepreneurship',
        'peace_order' => 'Committee on Peace and Order',
        'anti_drug' => 'Committee on Anti-Drug Abuse',
        'disaster' => 'Committee on Disaster Preparedness and Climate Change',
    ];

    /**
     * @return array<string, string> key => label
     */
    public static function forPositionName(string $name): array
    {
        return match ($name) {
            'Kagawad' => self::BARANGAY_KAGAWAD,
            'SK Chairman' => self::SK_CHAIRMAN,
            'SK Kagawad' => self::SK_KAGAWAD,
            default => [],
        };
    }

    public static function requiresCommittee(string $positionName): bool
    {
        return self::forPositionName($positionName) !== [];
    }

    public static function label(string $positionName, ?string $key): ?string
    {
        if ($key === null || $key === '') {
            return null;
        }

        return self::forPositionName($positionName)[$key] ?? null;
    }

    /**
     * @return list<string>
     */
    public static function keys(string $positionName): array
    {
        return array_keys(self::forPositionName($positionName));
    }
}
