<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Keys used on the public home page only (Phase 2).
     *
     * @var list<string>
     */
    public const HOME_PAGE_KEYS = [
        'welcome_hero_badge',
        'welcome_hero_title_line1',
        'welcome_hero_title_line2',
        'welcome_hero_subtitle',
        'welcome_about_kicker',
        'welcome_about_heading',
        'welcome_about_body',
        'welcome_about_bullets',
        'contact_section_kicker',
        'contact_section_heading',
        'contact_address_line',
        'contact_phone',
        'contact_email',
        'contact_maps_embed_url',
        'contact_office_hours',
        'welcome_cta_heading',
        'welcome_cta_subtitle',
    ];

    /**
     * All configurable strings (home + PDF boilerplate — Phase 3).
     * Order matches admin form sections.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        // —— Home & footer (Phase 2) ——
        'welcome_hero_badge' => 'Official Barangay Portal',
        'welcome_hero_title_line1' => 'e-Governance',
        'welcome_hero_title_line2' => 'System',
        'welcome_hero_subtitle' => 'Bringing Barangay Services Closer to the Community. Request certificates, file complaints, and track permits — all online.',
        'welcome_about_kicker' => 'About the System',
        'welcome_about_heading' => 'Modernizing Barangay Governance',
        'welcome_about_body' => 'The e-Governance System is a digital platform designed to streamline barangay operations in Paguiruan, Floridablanca, Pampanga. It empowers residents with convenient online access to government services while providing officials with modern tools for transparent and efficient governance.',
        'welcome_about_bullets' => "Faster service delivery with digital workflows\nReal-time tracking of requests and complaints\nTransparent and accountable governance",
        'contact_section_kicker' => 'Find Us',
        'contact_section_heading' => 'Our Location',
        'contact_address_line' => 'Barangay Paguiruan, Floridablanca, Pampanga, Philippines',
        'contact_phone' => '(096) 1254-9690',
        'contact_email' => 'barangaypaguiruan2024@gmail.com',
        'contact_maps_embed_url' => 'https://maps.google.com/maps?q=Barangay%20Paguiruan%20Floridablanca%20Pampanga&t=&z=15&ie=UTF8&iwloc=&output=embed',
        'contact_office_hours' => 'Office Hours: Monday – Friday, 8:00 AM – 5:00 PM',
        'welcome_cta_heading' => 'Experience Faster and More Transparent Barangay Services',
        'welcome_cta_subtitle' => 'Join thousands of Paguiruan residents who are already using the e-Governance System for convenient, digital government services.',

        // —— Certificates & permits PDFs (Phase 3) — official names stay in templates until wired to Officials ——
        'doc_header_line_1' => 'Republic of the Philippines',
        'doc_header_line_2' => 'Province of Pampanga',
        'doc_header_line_3' => 'MUNICIPALITY OF FLORIDABLANCA',
        'doc_header_line_4' => 'BARANGAY PAGUIRUAN',
        'doc_header_office_line' => 'OFFICE OF THE PUNONG BARANGAY',
        'doc_seal_note' => '*NOT VALID WITHOUT SEAL*',
        'doc_jurisdiction_short' => 'Barangay Paguiruan, Floridablanca, Pampanga',
        'doc_jurisdiction_medium' => 'Paguiruan, Floridablanca, Pampanga',
        'doc_cert_legal_purpose_clause' => 'This certification is issued for whatever legal intent and purpose it may serve.',
        'doc_permit_disclaimer_general' => 'This document is issued for official barangay verification and processing purposes only.',
        'doc_permit_disclaimer_business' => 'This business permit document is issued for official barangay record and processing purposes only.',
        'doc_issued_at_suffix' => 'at Barangay Paguiruan, Floridablanca, Pampanga.',

        // —— Blotter / KP summons & Excel report headers (Phase E) ——
        'doc_lupon_office_title' => 'OFFICE OF THE LUPONG TAGAPAMAYAPA',
        'doc_summon_signatory_role' => 'Punong Barangay/Lupon Chairman',
        'doc_blotter_summon_subtitle' => 'Katarungang Pambarangay Process',
        'doc_blotter_certification_subtitle' => 'Katarungang Pambarangay',
    ];

    /**
     * @return array<string, string>
     */
    public static function allForPublic(): array
    {
        $rows = static::query()->whereIn('key', self::HOME_PAGE_KEYS)->pluck('value', 'key');
        $out = [];
        foreach (self::HOME_PAGE_KEYS as $key) {
            $v = $rows[$key] ?? null;
            $out[$key] = ($v !== null && $v !== '') ? $v : (self::DEFAULTS[$key] ?? '');
        }

        return $out;
    }

    public static function getValue(string $key, ?string $default = null): string
    {
        if (! array_key_exists($key, self::DEFAULTS)) {
            return $default ?? '';
        }

        $v = static::query()->where('key', $key)->value('value');
        if ($v !== null && $v !== '') {
            return $v;
        }

        return self::DEFAULTS[$key] ?? $default ?? '';
    }

    /**
     * @param  array<string, string|null>  $pairs
     */
    public static function upsertMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            if (! array_key_exists($key, self::DEFAULTS)) {
                continue;
            }
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
