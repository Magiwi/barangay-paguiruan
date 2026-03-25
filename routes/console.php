<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use App\Services\SmsService;
use App\Services\RegistrationEscalationConfig;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('officials:expire')->daily()->at('00:05');
Schedule::command('blotters:apply-retention')->daily()->at('01:15')->withoutOverlapping();
// Backups disabled for now (re-enable when admin backups UI is restored)
// Schedule::command('backup:run --only-db')->daily()->at('02:00')->withoutOverlapping();
// Schedule::command('backup:clean')->daily()->at('03:00')->withoutOverlapping();
Schedule::call(function () {
    $settings = RegistrationEscalationConfig::get();

    Artisan::call('registrations:notify-overdue', [
        '--hours' => (int) $settings['overdue_hours'],
        '--trigger-source' => 'scheduled',
    ]);
})->name('registrations:notify-overdue-dynamic')->daily()->at('08:00')->withoutOverlapping();

Artisan::command('channels:test {--email_to= : Email address to receive test email} {--sms_mobile= : PH mobile number (e.g. 09171234567 or +639171234567)} {--sms_template_key=test_sms : SMS template key} {--sms_name=Test User : Name token for SMS}', function () {
    $emailTo = (string) $this->option('email_to');
    $smsMobile = (string) $this->option('sms_mobile');
    $smsTemplateKey = (string) ($this->option('sms_template_key') ?: 'test_sms');
    $smsName = (string) ($this->option('sms_name') ?: 'Test User');

    $this->info('== Channel Test ==');

    $mailHost = (string) config('mail.mailers.smtp.host', '');
    $mailPort = (string) config('mail.mailers.smtp.port', '');
    $mailMailer = (string) config('mail.default', '');
    $mailEnabled = (bool) ($mailMailer !== 'log');
    $this->line('MAIL -> mailer=' . $mailMailer . ', host=' . ($mailHost ?: 'n/a') . ', port=' . ($mailPort ?: 'n/a'));

    $smsEnabled = (bool) config('services.sms.enabled', false);
    $smsApiKeyPresent = (string) config('services.sms.api_key', '');
    $this->line('SMS -> enabled=' . ($smsEnabled ? 'yes' : 'no') . ', api_key=' . ($smsApiKeyPresent !== '' ? 'set' : 'missing'));

    if ($emailTo !== '') {
        $this->line('Email -> ' . $emailTo);
        try {
            Mail::raw('Test email from Barangay system (' . now()->format('Y-m-d H:i:s') . ')', function ($m) use ($emailTo) {
                $m->to($emailTo)->subject('Barangay System Channel Test');
            });
            $this->info('Email: OK');
        } catch (\Throwable $e) {
            $this->error('Email: FAILED - ' . $e->getMessage());
        }
    } else {
        $this->line('Email -> skipped (provide --email_to)');
    }

    if ($smsMobile !== '') {
        $this->line('SMS -> ' . $smsMobile);
        try {
            $result = SmsService::sendTestMessage(
                $smsMobile,
                $smsTemplateKey,
                [
                    'name' => $smsName,
                ],
                'Test SMS gumagana'
            );

            $status = (string) ($result['status'] ?? 'unknown');
            $this->info('SMS: ' . strtoupper($status));
        } catch (\Throwable $e) {
            $this->error('SMS: FAILED - ' . $e->getMessage());
        }
    } else {
        $this->line('SMS -> skipped (provide --sms_mobile)');
    }

    $this->info('== Done ==');
})->purpose('Send synchronous channel test (SMTP + Semaphore SMS)');

