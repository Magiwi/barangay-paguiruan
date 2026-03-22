<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(20)->withQueryString();

        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $performers = AuditLog::with('user')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->distinct()
            ->get()
            ->pluck('user')
            ->filter()
            ->sortBy('full_name');

        return view('admin.audit.index', compact('logs', 'actions', 'performers'));
    }
}
