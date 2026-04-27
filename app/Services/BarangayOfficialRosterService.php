<?php

namespace App\Services;

use App\Data\BarangayCouncilRoster;
use App\Data\PdfOfficialRosters;
use App\Data\SkOfficialRoster;
use App\Models\Official;
use Illuminate\Support\Collection;

class BarangayOfficialRosterService
{
    /**
     * Council + SK rosters for PDF templates (single DB round-trip).
     */
    public function pdfRosters(): PdfOfficialRosters
    {
        $all = $this->sortedCurrentlyServing();
        $councilOfficials = $all->filter(fn (Official $o) => ! str_starts_with($o->position->name, 'SK '));
        $skOfficials = $all->filter(fn (Official $o) => str_starts_with($o->position->name, 'SK '));

        return new PdfOfficialRosters(
            council: new BarangayCouncilRoster(
                chairman: $councilOfficials->first(fn (Official $o) => $o->position->name === 'Barangay Chairman'),
                secretary: $councilOfficials->first(fn (Official $o) => $o->position->name === 'Barangay Secretary'),
                treasurer: $councilOfficials->first(fn (Official $o) => $o->position->name === 'Barangay Treasurer'),
                investigator: $councilOfficials->first(fn (Official $o) => $o->position->name === 'Barangay Investigator'),
                kagawads: $councilOfficials->filter(fn (Official $o) => $o->position->name === 'Kagawad')->values(),
            ),
            sk: new SkOfficialRoster(
                skChairman: $skOfficials->first(fn (Official $o) => $o->position->name === 'SK Chairman'),
                skKagawads: $skOfficials->filter(fn (Official $o) => $o->position->name === 'SK Kagawad')->values(),
            ),
        );
    }

    /**
     * Active barangay council (chairman, executive officers, kagawads) — excludes SK positions.
     */
    public function councilRoster(): BarangayCouncilRoster
    {
        $officials = $this->sortedCurrentlyServing()
            ->filter(fn (Official $o) => ! str_starts_with($o->position->name, 'SK '));

        return new BarangayCouncilRoster(
            chairman: $officials->first(fn (Official $o) => $o->position->name === 'Barangay Chairman'),
            secretary: $officials->first(fn (Official $o) => $o->position->name === 'Barangay Secretary'),
            treasurer: $officials->first(fn (Official $o) => $o->position->name === 'Barangay Treasurer'),
            investigator: $officials->first(fn (Official $o) => $o->position->name === 'Barangay Investigator'),
            kagawads: $officials->filter(fn (Official $o) => $o->position->name === 'Kagawad')->values(),
        );
    }

    /**
     * Active SK chairman and SK kagawads.
     */
    public function skRoster(): SkOfficialRoster
    {
        $officials = $this->sortedCurrentlyServing()
            ->filter(fn (Official $o) => str_starts_with($o->position->name, 'SK '));

        return new SkOfficialRoster(
            skChairman: $officials->first(fn (Official $o) => $o->position->name === 'SK Chairman'),
            skKagawads: $officials->filter(fn (Official $o) => $o->position->name === 'SK Kagawad')->values(),
        );
    }

    /**
     * @return Collection<int, Official>
     */
    private function sortedCurrentlyServing(): Collection
    {
        return Official::query()
            ->with(['user', 'position'])
            ->currentlyServing()
            ->get()
            ->sortBy([
                fn (Official $o) => $o->position->sort_order,
                fn (Official $o) => $o->id,
            ])
            ->values();
    }
}
