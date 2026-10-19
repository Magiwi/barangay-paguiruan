<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(
        #[\SensitiveParameter] public readonly string $token
    ) {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset Password - Barangay Paguiruan')
            ->greeting('Hello ' . ($notifiable->first_name ?: 'Resident') . ',')
            ->line('You are receiving this email because we received a password reset request for your Barangay Paguiruan account.')
            ->action('Reset Password', $resetUrl)
            ->line("This password reset link will expire in {$minutes} minutes.")
            ->line('If you did not request a password reset, no further action is required.');
    }
}
