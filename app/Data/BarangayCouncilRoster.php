<?php

namespace App\Data;

use App\Models\Official;
use Illuminate\Support\Collection;

/**
 * Currently serving barangay council officials (excludes SK positions).
 *
 * @phpstan-type TKagawads Collection<int, Official>
 */
final readonly class BarangayCouncilRoster
{
    /**
     * @param  TKagawads  $kagawads
     */
    public function __construct(
        public ?Official $chairman,
        public ?Official $secretary,
        public ?Official $treasurer,
        public ?Official $investigator,
        public Collection $kagawads,
    ) {}
}
