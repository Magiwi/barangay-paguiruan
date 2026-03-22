<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\View\View;

class OfficialController extends Controller
{
    public function council(): View
    {
        $officials = Official::with(['user', 'position'])
            ->currentlyServing()
            ->whereHas('position', fn ($q) => $q->where('name', 'not like', 'SK %'))
            ->get()
            ->sortBy('position.sort_order');

        $chairman = $officials->first(fn ($o) => $o->position->name === 'Barangay Chairman');
        $secretary = $officials->first(fn ($o) => $o->position->name === 'Barangay Secretary');
        $treasurer = $officials->first(fn ($o) => $o->position->name === 'Barangay Treasurer');
        $investigator = $officials->first(fn ($o) => $o->position->name === 'Barangay Investigator');
        $kagawads = $officials->filter(fn ($o) => $o->position->name === 'Kagawad')->values();

        return view('resident.officials.council', compact('chairman', 'secretary', 'treasurer', 'investigator', 'kagawads'));
    }

    public function sk(): View
    {
        $officials = Official::with(['user', 'position'])
            ->currentlyServing()
            ->whereHas('position', fn ($q) => $q->where('name', 'like', 'SK %'))
            ->get()
            ->sortBy('position.sort_order');

        $skChairman = $officials->first(fn ($o) => $o->position->name === 'SK Chairman');
        $skSecretary = $officials->first(fn ($o) => $o->position->name === 'SK Secretary');
        $skTreasurer = $officials->first(fn ($o) => $o->position->name === 'SK Treasurer');
        $skKagawads = $officials->filter(fn ($o) => $o->position->name === 'SK Kagawad')->values();

        return view('resident.officials.sk', compact('skChairman', 'skSecretary', 'skTreasurer', 'skKagawads'));
    }
}
