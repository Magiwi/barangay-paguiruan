<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public static function notify(
        User $user,
        string $title,
        string $message,
        string $type,
        ?int $relatedId = null
    ): UserNotification {
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_id' => $relatedId,
        ]);
    }

    /**
     * Send the same notification to multiple users at once.
     */
    public static function notifyMany(
        iterable $users,
        string $title,
        string $message,
        string $type,
        ?int $relatedId = null
    ): void {
        $now = now();
        $records = [];

        foreach ($users as $user) {
            $records[] = [
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'related_id' => $relatedId,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($records)) {
            UserNotification::insert($records);
        }
    }
}
