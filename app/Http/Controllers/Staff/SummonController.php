<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Admin\SummonController as AdminSummonController;
use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\Summon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SummonController extends Controller
{
    public function __construct()
    {
        if (! auth()->check() || ! auth()->user()->canAccess('blotter')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function index(Blotter $blotter)
    {
        return app(AdminSummonController::class)->index($blotter);
    }

    public function store(Request $request, Blotter $blotter): RedirectResponse
    {
        return app(AdminSummonController::class)->store($request, $blotter);
    }

    public function updateStatus(Request $request, Blotter $blotter, Summon $summon): RedirectResponse
    {
        return app(AdminSummonController::class)->updateStatus($request, $blotter, $summon);
    }

    public function print(Blotter $blotter, Summon $summon)
    {
        return app(AdminSummonController::class)->print($blotter, $summon);
    }

    public function certificationToFileAction(Blotter $blotter)
    {
        return app(AdminSummonController::class)->certificationToFileAction($blotter);
    }
}
