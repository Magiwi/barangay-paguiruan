<?php

namespace App\Support;

use App\Models\Official;
use App\Models\User;

/**
 * Labels and honorifics for public pages and PDF output (Phase A — single place for mapping).
 */
final class OfficialPrint
{
    /**
     * Full name with "Hon." prefix, as used on certificates and formal documents.
     */
    public static function honorificName(User $user): string
    {
        $name = trim($user->full_name);

        return $name === '' ? 'Hon. ________' : 'Hon. '.$name;
    }

    /**
     * Uppercase honorific for signature lines (e.g. HON. JUAN DELA CRUZ).
     */
    public static function honorificNameSignatureUpper(User $user): string
    {
        return mb_strtoupper(self::honorificName($user));
    }

    /**
     * Title shown on certificates/PDF for the barangay chair (DB: Barangay Chairman).
     */
    public static function punongBarangayTitle(): string
    {
        return 'Punong Barangay';
    }

    /**
     * Role line for print/PDF from position name (Barangay Chairman → Punong Barangay; others unchanged or shortened).
     */
    public static function positionPrintTitle(string $positionName): string
    {
        return match ($positionName) {
            'Barangay Chairman' => self::punongBarangayTitle(),
            default => $positionName,
        };
    }

    /**
     * Resident council page: short label after stripping a leading "Barangay " (e.g. "Secretary").
     */
    public static function executiveShortRole(string $positionName): string
    {
        return str_starts_with($positionName, 'Barangay ')
            ? substr($positionName, 9)
            : $positionName;
    }

    /**
     * Committee line for Kagawad / SK roles (null if none or unknown key).
     */
    public static function committeeLine(Official $official): ?string
    {
        return OfficialCommittees::label($official->position->name, $official->committee);
    }

    /**
     * Role text under a Kagawad name on PDFs (committee label, or generic "Kagawad").
     */
    public static function kagawadRoleLine(Official $official): string
    {
        return self::committeeLine($official) ?? 'Kagawad';
    }
}
