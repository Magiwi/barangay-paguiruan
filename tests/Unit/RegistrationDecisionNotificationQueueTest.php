<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\RegistrationDecisionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class RegistrationDecisionNotificationQueueTest extends TestCase
{
    public function test_registration_decision_notification_is_queued_on_database_connection(): void
    {
        $user = new User([
            'first_name' => 'Test',
            'status' => User::STATUS_APPROVED,
        ]);

        $notification = new RegistrationDecisionNotification($user, User::STATUS_APPROVED);

        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame('database', $notification->connection);
        $this->assertSame('notifications', $notification->queue);
    }
}
