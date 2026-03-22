<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\Hearing;
use App\Models\HearingReschedule;
use App\Models\Summon;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HearingController extends Controller
{
    public function index(Blotter $blotter): View
    {
        $summons = $blotter->summons()
            ->orderBy('summon_number')
            ->get();
        $luponAssignees = $this->luponAssignees();

        $hearings = $blotter->hearings()
            ->with(['summon', 'reschedules.updatedBy'])
            ->orderBy('hearing_date')
            ->orderBy('hearing_time')
            ->get();

        return view('admin.blotters.hearings', [
            'blotter' => $blotter,
            'summons' => $summons,
            'hearings' => $hearings,
            'luponAssignees' => $luponAssignees,
            'routePrefix' => $this->routePrefix(),
            'layout' => $this->routePrefix() === 'staff' ? 'layouts.staff' : 'layouts.admin',
        ]);
    }

    public function store(Request $request, Blotter $blotter): RedirectResponse
    {
        $luponAssignees = $this->luponAssignees();

        if (! $blotter->summons()->exists()) {
            return back()->with('error', 'Cannot create hearing without an existing summon.');
        }

        $validated = $request->validate([
            'summon_id' => ['required', 'integer', 'exists:summons,id'],
            'hearing_date' => ['required', 'date'],
            'hearing_time' => ['required', 'date_format:H:i'],
            'lupon_user_id' => ['required', 'integer', Rule::in(array_keys($luponAssignees))],
        ], [
            'lupon_user_id.required' => 'Please select an assigned officer.',
            'lupon_user_id.in' => 'Selected assigned officer is not allowed for blotter handling.',
        ]);

        $summon = Summon::where('id', $validated['summon_id'])
            ->where('blotter_id', $blotter->id)
            ->first();

        if (! $summon) {
            return back()->withErrors(['summon_id' => 'Selected summon is not linked to this blotter case.'])->withInput();
        }

        if ($blotter->hearings()->where('summon_id', $summon->id)->exists()) {
            return back()->withErrors(['summon_id' => 'A hearing already exists for this summon.'])->withInput();
        }

        $hearing = Hearing::create([
            'blotter_id' => $blotter->id,
            'summon_id' => $summon->id,
            'hearing_date' => $validated['hearing_date'],
            'hearing_time' => $validated['hearing_time'],
            'lupon_assigned' => $luponAssignees[(int) $validated['lupon_user_id']],
            'status' => Hearing::STATUS_SCHEDULED,
        ]);

        AuditService::log(
            'hearing_created',
            $blotter,
            "Created hearing for summon #{$summon->summon_number} on {$hearing->hearing_date->format('Y-m-d')} {$hearing->hearing_time}"
        );

        return redirect()->route($this->routePrefix() . '.blotters.hearings.index', $blotter)
            ->with('success', 'Hearing scheduled successfully.');
    }

    public function start(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        abort_unless($hearing->blotter_id === $blotter->id, 404);

        $hearing->status = Hearing::STATUS_ONGOING;
        $hearing->save();

        if ($hearing->summon && $hearing->summon->status === Summon::STATUS_PENDING) {
            $hearing->summon->status = Summon::STATUS_SERVED;
            $hearing->summon->save();
        }

        AuditService::log(
            'hearing_started',
            $blotter,
            "Started hearing for summon #{$hearing->summon?->summon_number}"
        );

        return redirect()->route($this->routePrefix() . '.blotters.hearings.index', $blotter)
            ->with('success', 'Hearing marked as ongoing.');
    }

    public function markNoShow(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        abort_unless($hearing->blotter_id === $blotter->id, 404);

        $validated = $request->validate([
            'complainant_attendance' => ['nullable', 'string', 'in:' . implode(',', Hearing::ATTENDANCE_OPTIONS)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($blotter, $hearing, $validated) {
            $hearing->complainant_attendance = $validated['complainant_attendance'] ?? Hearing::ATTENDANCE_PRESENT;
            $hearing->respondent_attendance = Hearing::ATTENDANCE_ABSENT;
            $hearing->status = Hearing::STATUS_NO_SHOW;
            $hearing->result = Hearing::RESULT_RESCHEDULE;
            if (array_key_exists('notes', $validated) && $validated['notes'] !== null) {
                $hearing->notes = $validated['notes'];
            }
            $hearing->save();

            if ($hearing->summon) {
                $hearing->summon->status = Summon::STATUS_NO_SHOW;
                $hearing->summon->save();
            }

            $noShowCount = $blotter->hearings()->where('status', Hearing::STATUS_NO_SHOW)->count();
            $blotter->is_uncooperative = $noShowCount >= 3;
            $blotter->save();
        });

        AuditService::log(
            'hearing_no_show',
            $blotter,
            "Marked hearing for summon #{$hearing->summon?->summon_number} as No Show"
        );

        $msg = 'Hearing marked as No Show. Next summon may now be generated (if below 3 summons).';
        if ($blotter->is_uncooperative) {
            $msg .= ' Case is now uncooperative and Certification to File Action is enabled.';
        }

        return redirect()->route($this->routePrefix() . '.blotters.hearings.index', $blotter)->with('success', $msg);
    }

    public function complete(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        abort_unless($hearing->blotter_id === $blotter->id, 404);

        $validated = $request->validate([
            'complainant_attendance' => ['required', 'string', 'in:' . implode(',', Hearing::ATTENDANCE_OPTIONS)],
            'respondent_attendance' => ['required', 'string', 'in:' . implode(',', Hearing::ATTENDANCE_OPTIONS)],
            'result' => ['required', 'string', 'in:' . implode(',', Hearing::RESULTS)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['respondent_attendance'] === Hearing::ATTENDANCE_ABSENT) {
            return back()->withErrors([
                'respondent_attendance' => 'Cannot mark as Done when respondent is absent. Use "Mark No Show" instead.',
            ])->withInput();
        }

        DB::transaction(function () use ($hearing, $validated) {
            $hearing->complainant_attendance = $validated['complainant_attendance'];
            $hearing->respondent_attendance = $validated['respondent_attendance'];
            $hearing->result = $validated['result'];
            $hearing->notes = $validated['notes'] ?? $hearing->notes;
            $hearing->status = Hearing::STATUS_DONE;
            $hearing->save();

            if ($hearing->summon) {
                $hearing->summon->status = Summon::STATUS_COMPLETED;
                $hearing->summon->save();
            }
        });

        AuditService::log(
            'hearing_done',
            $blotter,
            "Marked hearing for summon #{$hearing->summon?->summon_number} as Done ({$validated['result']})"
        );

        return redirect()->route($this->routePrefix() . '.blotters.hearings.index', $blotter)
            ->with('success', 'Hearing marked as done successfully.');
    }

    public function reschedule(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        abort_unless($hearing->blotter_id === $blotter->id, 404);

        $validated = $request->validate([
            'new_hearing_date' => ['required', 'date'],
            'new_hearing_time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $hearing, $validated) {
            HearingReschedule::create([
                'hearing_id' => $hearing->id,
                'old_hearing_date' => $hearing->hearing_date,
                'old_hearing_time' => $hearing->hearing_time,
                'new_hearing_date' => $validated['new_hearing_date'],
                'new_hearing_time' => $validated['new_hearing_time'],
                'reason' => $validated['reason'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            $hearing->hearing_date = $validated['new_hearing_date'];
            $hearing->hearing_time = $validated['new_hearing_time'];
            $hearing->status = Hearing::STATUS_SCHEDULED;
            $hearing->result = Hearing::RESULT_RESCHEDULE;
            $hearing->save();
        });

        AuditService::log(
            'hearing_rescheduled',
            $blotter,
            "Rescheduled hearing for summon #{$hearing->summon?->summon_number}"
        );

        return redirect()->route($this->routePrefix() . '.blotters.hearings.index', $blotter)
            ->with('success', 'Hearing rescheduled successfully.');
    }

    public function addNotes(Request $request, Blotter $blotter, Hearing $hearing): RedirectResponse
    {
        abort_unless($hearing->blotter_id === $blotter->id, 404);

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:5000'],
        ]);

        $hearing->notes = $validated['notes'];
        $hearing->save();

        AuditService::log(
            'hearing_notes_updated',
            $blotter,
            "Updated hearing notes for summon #{$hearing->summon?->summon_number}"
        );

        return redirect()->route($this->routePrefix() . '.blotters.hearings.index', $blotter)
            ->with('success', 'Hearing notes saved.');
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
