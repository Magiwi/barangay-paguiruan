<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationDecisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly string $decision
    ) {
        // Force notification delivery through async queue
        // to prevent bulk-approve SMTP timeouts.
        $this->onConnection('database');
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isApproved = $this->decision === User::STATUS_APPROVED;
        $subject = $isApproved
            ? 'Registration Approved - Barangay Paguiruan'
            : 'Registration Rejected - Barangay Paguiruan';

        $view = $isApproved
            ? 'emails.registration-approved'
            : 'emails.registration-rejected';

        return (new MailMessage)
            ->subject($subject)
            ->markdown($view, [
                'residentName' => $this->user->first_name ?: 'Resident',
                'loginUrl' => url('/login'),
                'reasonLabel' => $this->user->registrationRejectionReasonLabel(),
                'reasonDetails' => $this->user->rejection_reason_details,
            ]);
    }
}
