<?php

namespace App\Data;

/**
 * Council + SK rosters for certificate/permit PDFs (loaded together to avoid duplicate queries).
 */
final readonly class PdfOfficialRosters
{
    public function __construct(
        public BarangayCouncilRoster $council,
        public SkOfficialRoster $sk,
    ) {}
}
