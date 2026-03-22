<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Admin\HearingController as AdminHearingController;
use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\Hearing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HearingController extends Controller
{
    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('blotter')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(Blotter $blotter)
    {
        return app(AdminHearingController::class)->index($blotter);
    }

    public function store(Request $request, Blotter $blotter): RedirectResponse
    {
        return app(AdminHearingController::class)->store($request, $blotter);
    }

    public function start(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        return app(AdminHearingController::class)->start($request, $blotter, $hearing);
    }

    public function markNoShow(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        return app(AdminHearingController::class)->markNoShow($request, $blotter, $hearing);
    }

    public function complete(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        return app(AdminHearingController::class)->complete($request, $blotter, $hearing);
    }

    public function reschedule(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        return app(AdminHearingController::class)->reschedule($request, $blotter, $hearing);
    }

    public function addNotes(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        return app(AdminHearingController::class)->addNotes($request, $blotter, $hearing);
    }
}
