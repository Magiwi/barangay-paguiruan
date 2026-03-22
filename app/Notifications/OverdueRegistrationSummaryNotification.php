<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueRegistrationSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly array $summary,
        private readonly int $thresholdHours
    ) {
        $this->onQueue('mail');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $queueUrl = $notifiable->role === User::ROLE_STAFF
            ? url('/staff/pending-registrations?status=pending&sla=overdue')
            : url('/admin/pending-registrations?status=pending&sla=overdue');

        $mail = (new MailMessage)
            ->subject('Overdue Registration Queue Alert')
            ->greeting('Hello ' . ($notifiable->first_name ?: 'Team') . ',')
            ->line("There are pending registrations that exceeded {$this->thresholdHours} hours.")
            ->line('Overdue pending: ' . number_format((int) ($this->summary['overdue'] ?? 0)))
            ->line('Due soon (24h+): ' . number_format((int) ($this->summary['due_soon'] ?? 0)))
            ->line('Missing government ID: ' . number_format((int) ($this->summary['missing_id'] ?? 0)))
            ->action('Open Registration Queue', $queueUrl);

        if (! empty($this->summary['oldest_pending_at'])) {
            $mail->line('Oldest pending registration: ' . $this->summary['oldest_pending_at']);
        }

        return $mail->line('Please review and process the queue as soon as possible.');
    }
}
