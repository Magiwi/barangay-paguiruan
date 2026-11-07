<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\Purok;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ResidentRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistedRegistrationController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorizeRegistrations($request);

        $puroks = Purok::active()->orderBy('name')->get();

        return view('auth.register', [
            'puroks' => $puroks,
            'assisted' => true,
            'layout' => $this->layout($request),
            'routePrefix' => $this->routePrefix($request),
            'counts' => $this->registrationCounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRegistrations($request);

        $service = app(ResidentRegistrationService::class);
        $user = $service->register($request, true);

        return redirect()
            ->route($this->routePrefix($request) . '.pending-registrations.created')
            ->with('assisted_credentials', [
                'name' => $user->full_name,
                'code' => (string) $service->issuedClaimCode,
            ]);
    }

    public function reissue(Request $request, User $user): RedirectResponse
    {
        $this->authorizeRegistrations($request);

        if ($user->role !== User::ROLE_RESIDENT || ! $user->hasUnclaimedAccount()) {
            abort(403, 'This resident already activated an account.');
        }

        $code = app(ResidentRegistrationService::class)->issueClaimCode($user);
        AuditService::log('claim_code_reissued', $user, 'Issued a new claim code for ' . $user->full_name . '.');

        return redirect()
            ->route($this->routePrefix($request) . '.pending-registrations.created')
            ->with('assisted_credentials', [
                'name' => $user->full_name,
                'code' => $code,
            ]);
    }

    public function created(Request $request): View|RedirectResponse
    {
        $this->authorizeRegistrations($request);

        $credentials = $request->session()->get('assisted_credentials');
        if (! is_array($credentials) || ! isset($credentials['code'])) {
            return redirect()->route($this->routePrefix($request) . '.pending-registrations.index');
        }

        return view('admin.pending-registrations.created', [
            'credentials' => $credentials,
            'layout' => $this->layout($request),
            'routePrefix' => $this->routePrefix($request),
            'counts' => $this->registrationCounts(),
            'activeTab' => 'register',
        ]);
    }

    /**
     * @return array{pending: int, approved: int, rejected: int, suspended: int, registered: int}
     */
    private function registrationCounts(): array
    {
        return [
            'pending' => User::countable()->whereNull('registered_by')->where('status', User::STATUS_PENDING)->count(),
            'approved' => User::countable()->whereNull('registered_by')->where('status', User::STATUS_APPROVED)->count(),
            'rejected' => ApprovalLog::query()->where('action', ApprovalLog::ACTION_REJECTED)->count(),
            'suspended' => User::countable()->whereNull('registered_by')->where('status', User::STATUS_SUSPENDED)->count(),
            'registered' => User::countable()->awaitingWalkInSetup()->count(),
        ];
    }

    private function authorizeRegistrations(Request $request): void
    {
        if (! $request->user()?->canAccess('registrations')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('staff.*') ? 'staff' : 'admin';
    }

    private function layout(Request $request): string
    {
        return $this->routePrefix($request) === 'staff' ? 'layouts.staff' : 'layouts.admin';
    }
}
