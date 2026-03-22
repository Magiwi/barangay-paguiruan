<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginActivityController extends Controller
{
    public function index(Request $request): View
    {
        $activities = LoginActivity::query()
            ->with('user')
            ->when($request->filled('user_id'), fn ($q) =>
                $q->where('user_id', $request->user_id)
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->filled('ip_address'), fn ($q) =>
                $q->where('ip_address', $request->ip_address)
            )
            ->when($request->filled('date_from'), fn ($q) =>
                $q->whereDate('created_at', '>=', $request->date_from)
            )
            ->when($request->filled('date_to'), fn ($q) =>
                $q->whereDate('created_at', '<=', $request->date_to)
            )
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        $users = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $stats = [
            'total' => LoginActivity::count(),
            'success' => LoginActivity::where('status', LoginActivity::STATUS_SUCCESS)->count(),
            'failed' => LoginActivity::where('status', LoginActivity::STATUS_FAILED)->count(),
            'blocked' => LoginActivity::where('status', LoginActivity::STATUS_BLOCKED)->count(),
        ];

        return view('admin.login-activities.index', compact('activities', 'users', 'stats'));
    }
}
