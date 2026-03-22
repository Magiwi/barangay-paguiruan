<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use Illuminate\View\View;

class ApprovalHistoryController extends Controller
{
    /**
     * List approval/rejection history (read-only).
     */
    public function index(): View
    {
        $logs = ApprovalLog::with(['user', 'performer'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.approval-history.index', compact('logs'));
    }
}
