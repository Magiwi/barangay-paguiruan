<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Display residents with pending PWD or Senior verifications.
     */
    public function index(Request $request): View
    {
        $verificationType = strtolower(trim((string) $request->query('type', 'all')));
        if (! in_array($verificationType, ['all', 'pwd', 'senior'], true)) {
            $verificationType = 'all';
        }

        $baseQuery = User::where(function ($query) {
            $query->where(function ($q) {
                $q->where('is_pwd', true)->where('pwd_status', 'pending');
            })->orWhere(function ($q) {
                $q->where('is_senior', true)->where('senior_status', 'pending');
            });
        });

        $verificationCounts = [
            'all' => (clone $baseQuery)->count(),
            'pwd' => User::where('is_pwd', true)->where('pwd_status', 'pending')->count(),
            'senior' => User::where('is_senior', true)->where('senior_status', 'pending')->count(),
        ];

        if ($verificationType === 'pwd') {
            $baseQuery->where('is_pwd', true)->where('pwd_status', 'pending');
        } elseif ($verificationType === 'senior') {
            $baseQuery->where('is_senior', true)->where('senior_status', 'pending');
        }

        $residents = $baseQuery
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.verifications.index', compact('residents', 'verificationType', 'verificationCounts'));
    }

    /**
     * Approve PWD classification for a resident.
     */
    public function approvePwd(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_pwd || $user->pwd_status !== 'pending') {
            return back()->with('error', 'Invalid verification request.');
        }

        $user->update(['pwd_status' => 'verified']);
        AuditService::log('verification_pwd_approved', $user, "Approved PWD verification for {$user->full_name}");

        return back()->with('success', 'PWD classification verified for ' . $user->first_name . ' ' . $user->last_name . '.');
    }

    /**
     * Reject PWD classification for a resident.
     */
    public function rejectPwd(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_pwd || $user->pwd_status !== 'pending') {
            return back()->with('error', 'Invalid verification request.');
        }

        $user->update(['pwd_status' => 'rejected']);
        AuditService::log('verification_pwd_rejected', $user, "Rejected PWD verification for {$user->full_name}");

        return back()->with('success', 'PWD classification rejected for ' . $user->first_name . ' ' . $user->last_name . '.');
    }

    /**
     * Approve Senior Citizen classification for a resident.
     */
    public function approveSenior(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_senior || $user->senior_status !== 'pending') {
            return back()->with('error', 'Invalid verification request.');
        }

        $user->update(['senior_status' => 'verified']);
        AuditService::log('verification_senior_approved', $user, "Approved Senior verification for {$user->full_name}");

        return back()->with('success', 'Senior Citizen classification verified for ' . $user->first_name . ' ' . $user->last_name . '.');
    }

    /**
     * Reject Senior Citizen classification for a resident.
     */
    public function rejectSenior(Request $request, User $user): RedirectResponse
    {
        if (! $user->is_senior || $user->senior_status !== 'pending') {
            return back()->with('error', 'Invalid verification request.');
        }

        $user->update(['senior_status' => 'rejected']);
        AuditService::log('verification_senior_rejected', $user, "Rejected Senior verification for {$user->full_name}");

        return back()->with('success', 'Senior Citizen classification rejected for ' . $user->first_name . ' ' . $user->last_name . '.');
    }

}
