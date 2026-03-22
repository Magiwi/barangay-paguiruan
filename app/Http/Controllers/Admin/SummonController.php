<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\Summon;
use App\Models\User;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SummonController extends Controller
{
    public function index(Blotter $blotter): View
    {
        $summons = $blotter->summons()->orderBy('summon_number')->get();
        $luponAssignees = $this->luponAssignees();
        $summonCount = $summons->count();
        $lastSummon = $summons->last();
        $hasReachedLimit = $summonCount >= 3;
        $canGenerateNext = $summonCount === 0
            || (! $hasReachedLimit && $lastSummon && $lastSummon->status === Summon::STATUS_NO_SHOW);

        $canGenerateCertification = (bool) $blotter->is_uncooperative;

        return view('admin.blotters.summons', [
            'blotter' => $blotter,
            'summons' => $summons,
            'summonCount' => $summonCount,
            'canGenerateNext' => $canGenerateNext,
            'canGenerateCertification' => $canGenerateCertification,
            'luponAssignees' => $luponAssignees,
            'routePrefix' => $this->routePrefix(),
            'layout' => $this->routePrefix() === 'staff' ? 'layouts.staff' : 'layouts.admin',
        ]);
    }

    public function store(Request $request, Blotter $blotter): RedirectResponse
    {
        $luponAssignees = $this->luponAssignees();

        $validated = $request->validate([
            'hearing_date' => ['required', 'date'],
            'hearing_time' => ['required', 'date_format:H:i'],
            'lupon_user_id' => ['required', 'integer', Rule::in(array_keys($luponAssignees))],
        ], [
            'lupon_user_id.required' => 'Please select an assigned officer.',
            'lupon_user_id.in' => 'Selected assigned officer is not allowed for blotter handling.',
        ]);

        $created = DB::transaction(function () use ($blotter, $validated, $luponAssignees) {
            $existing = Summon::where('blotter_id', $blotter->id)
                ->orderBy('summon_number')
                ->lockForUpdate()
                ->get();

            $count = $existing->count();
            if ($count >= 3) {
                return null;
            }

            $last = $existing->last();
            if ($count > 0 && (! $last || $last->status !== Summon::STATUS_NO_SHOW)) {
                return false;
            }

            return Summon::create([
                'blotter_id' => $blotter->id,
                'summon_number' => $count + 1,
                'hearing_date' => $validated['hearing_date'],
                'hearing_time' => $validated['hearing_time'],
                'lupon_assigned' => $luponAssignees[(int) $validated['lupon_user_id']],
                'status' => Summon::STATUS_PENDING,
            ]);
        });

        if ($created === null) {
            return back()->with('error', 'Only up to 3 summons are allowed per blotter case.');
        }

        if ($created === false) {
            return back()->with('error', 'You can only generate the next summon if the previous summon is marked as No Show.');
        }

        AuditService::log(
            'summon_created',
            $blotter,
            "Generated summon #{$created->summon_number} for blotter {$blotter->blotter_number}"
        );

        return redirect()->route($this->routePrefix() . '.blotters.summons.index', $blotter)
            ->with('success', "Summon #{$created->summon_number} created successfully.");
    }

    public function updateStatus(Request $request, Blotter $blotter, Summon $summon): RedirectResponse
    {
        abort_unless($summon->blotter_id === $blotter->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Summon::STATUSES)],
        ]);

        $summon->status = $validated['status'];
        $summon->save();

        if ($summon->summon_number === 3) {
            $blotter->is_uncooperative = $summon->status === Summon::STATUS_NO_SHOW;
            $blotter->save();
        }

        AuditService::log(
            'summon_status_updated',
            $blotter,
            "Updated summon #{$summon->summon_number} status to {$summon->status} for blotter {$blotter->blotter_number}"
        );

        $message = 'Summon status updated successfully.';
        if ($summon->summon_number === 3 && $summon->status === Summon::STATUS_NO_SHOW) {
            $message .= ' Case is now tagged as uncooperative; Certification to File Action is available.';
        }

        return redirect()->route($this->routePrefix() . '.blotters.summons.index', $blotter)
            ->with('success', $message);
    }

    public function print(Blotter $blotter, Summon $summon)
    {
        abort_unless($summon->blotter_id === $blotter->id, 404);

        AuditService::log(
            'summon_printed',
            $blotter,
            "Printed summon #{$summon->summon_number} for blotter {$blotter->blotter_number}"
        );

        $pdf = Pdf::loadView('summons.print', [
            'blotter' => $blotter,
            'summon' => $summon,
        ])->setPaper('a4');

        return $pdf->stream("summon_{$blotter->blotter_number}_{$summon->summon_number}.pdf");
    }

    public function certificationToFileAction(Blotter $blotter)
    {
        $summons = $blotter->summons()->orderBy('summon_number')->get();
        $last = $summons->last();

        abort_unless(
            (bool) $blotter->is_uncooperative &&
            $summons->count() === 3 &&
            $last &&
            $last->summon_number === 3 &&
            $last->status === Summon::STATUS_NO_SHOW,
            422,
            'Certification to File Action is only available after the 3rd summon is marked No Show.'
        );

        AuditService::log(
            'certification_to_file_action_printed',
            $blotter,
            "Printed Certification to File Action for blotter {$blotter->blotter_number}"
        );

        $pdf = Pdf::loadView('admin.blotters.certification-pdf', [
            'blotter' => $blotter,
            'summons' => $summons,
        ])->setPaper('a4');

        return $pdf->stream("certification_{$blotter->blotter_number}.pdf");
    }

    private function routePrefix(): string
    {
        $routeName = request()->route()?->getName() ?? '';

        return str_starts_with($routeName, 'staff.') ? 'staff' : 'admin';
    }

    private function luponAssignees(): array
    {
        return User::query()
            ->with('position')
            ->where('status', 'approved')
            ->where('is_suspended', false)
            ->whereHas('position')
            ->where(function ($query): void {
                $query->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
                    ->orWhere(function ($staffQuery): void {
                        $staffQuery->where('role', User::ROLE_STAFF)
                            ->whereHas('staffPermission', function ($permissionQuery): void {
                                $permissionQuery->where('can_manage_blotter', true);
                            });
                    });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(function (User $user): array {
                return [(int) $user->id => trim($user->full_name . ' - ' . $user->position->name)];
            })
            ->all();
    }
}
