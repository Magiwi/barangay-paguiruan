<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
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

