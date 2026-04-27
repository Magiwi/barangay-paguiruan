<?php

namespace App\Data;

use App\Models\Official;
use Illuminate\Support\Collection;

/**
 * Currently serving SK officials.
 *
 * @phpstan-type TSkKagawads Collection<int, Official>
 */
final readonly class SkOfficialRoster
{
    /**
     * @param  TSkKagawads  $skKagawads
     */
    public function __construct(
        public ?Official $skChairman,
        public Collection $skKagawads,
    ) {}
}
