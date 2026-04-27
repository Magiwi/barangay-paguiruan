<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Services\BarangayOfficialRosterService;
use Illuminate\View\View;

class OfficialController extends Controller
{
    public function __construct(
        private readonly BarangayOfficialRosterService $rosterService,
    ) {}

    public function council(): View
    {
        $roster = $this->rosterService->councilRoster();

        return view('resident.officials.council', [
            'chairman' => $roster->chairman,
            'secretary' => $roster->secretary,
            'treasurer' => $roster->treasurer,
            'investigator' => $roster->investigator,
            'kagawads' => $roster->kagawads,
        ]);
    }

    public function sk(): View
    {
        $roster = $this->rosterService->skRoster();

        return view('resident.officials.sk', [
            'skChairman' => $roster->skChairman,
            'skKagawads' => $roster->skKagawads,
        ]);
    }
}
