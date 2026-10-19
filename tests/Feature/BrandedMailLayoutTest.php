<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RegistrationDecisionNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BrandedMailLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_registration_mail_uses_official_header_and_login_route(): void
    {
        $user = new User([
            'first_name' => 'Juan',
            'status' => User::STATUS_APPROVED,
        ]);

        $html = (string) (new RegistrationDecisionNotification($user, User::STATUS_APPROVED))
            ->toMail($user)
            ->render();

        $this->assertStringContainsString('BARANGAY PAGUIRUAN', $html);
        $this->assertStringContainsString('Republic of the Philippines', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString('Please do not reply', $html);
    }

    public function test_rejected_registration_mail_links_to_register_not_login(): void
    {
        $user = new User([
            'first_name' => 'Juan',
            'status' => User::STATUS_REJECTED,
            'rejection_reason_code' => 'incomplete_profile',
        ]);

        $html = (string) (new RegistrationDecisionNotification($user, User::STATUS_REJECTED))
            ->toMail($user)
            ->render();

        $this->assertStringContainsString(route('register'), $html);
        $this->assertStringContainsString('Register again', $html);
        $this->assertStringNotContainsString(route('login'), $html);
        $this->assertStringContainsString('BARANGAY PAGUIRUAN', $html);
    }

    public function test_password_reset_uses_branded_notification(): void
    {
        Notification::fake();

        $user = new User([
            'first_name' => 'Juan',
            'email' => 'juan@example.com',
        ]);

        $user->sendPasswordResetNotification('test-token');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $html = (string) $notification->toMail($user)->render();

            return str_contains($html, 'BARANGAY PAGUIRUAN')
                && str_contains($html, 'Reset Password')
                && str_contains($html, 'Please do not reply');
        });
    }
}
