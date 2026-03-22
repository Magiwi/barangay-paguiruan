<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->userNotifications()
            ->latest()
            ->paginate(15);

        return view('resident.notifications.index', compact('notifications'));
    }

    public function markAsRead(UserNotification $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function open(UserNotification $notification): RedirectResponse
    {
        if ((int) $notification->user_id !== (int) auth()->id()) {
            abort(403);
        }

        if (! $notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return redirect()->to($this->resolveTargetUrl($notification));
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()
            ->userNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    private function resolveTargetUrl(UserNotification $notification): string
    {
        return match ($notification->type) {
            'certificate' => route('resident.certificates.index'),
            'permit' => route('resident.permits.index'),
            'complaint' => route('resident.issues.index'),
            'blotter' => route('resident.blotter-requests.index'),
            'announcement' => $notification->related_id
                ? route('resident.announcements.show', ['announcement' => $notification->related_id])
                : route('resident.announcements.index'),
            'household_transfer' => route('profile.show', ['tab' => 'family']),
            default => route('resident.notifications.index'),
        };
    }
}
