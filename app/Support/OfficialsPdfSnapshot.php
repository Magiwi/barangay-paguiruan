<?php

namespace App\Support;

use App\Data\PdfOfficialRosters;
use App\Models\Official;

/**
 * Frozen Punong Barangay / council / SK block for PDFs at release time (Phase D).
 *
 * @phpstan-type TKagawadRow array{honorific: string, role: string}
 * @phpstan-type TSkChair array{honorific: string, committee: string|null}
 * @phpstan-type TExec array{honorific: string, position: string}
 */
final class OfficialsPdfSnapshot
{
    public const VERSION = 1;

    /**
     * Build a storable snapshot from the current roster (call at release).
     *
     * @return array<string, mixed>
     */
    public static function fromPdfRosters(PdfOfficialRosters $pdf): array
    {
        $c = $pdf->council;
        $sk = $pdf->sk;

        $chairman = $c->chairman;

        $kagawads = $c->kagawads->map(function (Official $k): array {
            return [
                'honorific' => OfficialPrint::honorificName($k->user),
                'role' => OfficialPrint::kagawadRoleLine($k),
            ];
        })->values()->all();

        $skChair = $sk->skChairman;

        return [
            'v' => self::VERSION,
            'chairman' => $chairman ? [
                'honorific' => OfficialPrint::honorificName($chairman->user),
                'role' => OfficialPrint::punongBarangayTitle(),
            ] : null,
            'kagawads' => $kagawads,
            'sk_chairman' => $skChair ? [
                'honorific' => OfficialPrint::honorificName($skChair->user),
                'committee' => OfficialPrint::committeeLine($skChair),
            ] : null,
            'treasurer' => $c->treasurer ? [
                'honorific' => OfficialPrint::honorificName($c->treasurer->user),
                'position' => $c->treasurer->position->name,
            ] : null,
            'secretary' => $c->secretary ? [
                'honorific' => OfficialPrint::honorificName($c->secretary->user),
                'position' => $c->secretary->position->name,
            ] : null,
            'signature_name_upper' => $chairman
                ? OfficialPrint::honorificNameSignatureUpper($chairman->user)
                : '____________________',
            'signature_role' => OfficialPrint::punongBarangayTitle(),
        ];
    }

    /**
     * Normalized PDF payload for Blade: snapshot if valid, otherwise live roster.
     *
     * @return array<string, mixed>
     */
    public static function forPrint(?array $snapshot, PdfOfficialRosters $live): array
    {
        if (self::isValidSnapshot($snapshot)) {
            return self::normalizeForView($snapshot);
        }

        return self::normalizeForView(self::fromPdfRosters($live));
    }

    /**
     * @param  array<string, mixed>|null  $snapshot
     */
    public static function isValidSnapshot(?array $snapshot): bool
    {
        if ($snapshot === null || $snapshot === []) {
            return false;
        }

        return isset($snapshot['v'], $snapshot['signature_role'], $snapshot['signature_name_upper'])
            && (int) $snapshot['v'] === self::VERSION
            && is_array($snapshot['kagawads'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public static function normalizeForView(array $snapshot): array
    {
        return [
            'chairman' => $snapshot['chairman'] ?? null,
            'kagawads' => is_array($snapshot['kagawads'] ?? null) ? $snapshot['kagawads'] : [],
            'sk_chairman' => $snapshot['sk_chairman'] ?? null,
            'treasurer' => $snapshot['treasurer'] ?? null,
            'secretary' => $snapshot['secretary'] ?? null,
            'signature_name_upper' => (string) ($snapshot['signature_name_upper'] ?? '____________________'),
            'signature_role' => (string) ($snapshot['signature_role'] ?? OfficialPrint::punongBarangayTitle()),
        ];
    }
}
