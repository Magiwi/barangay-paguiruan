<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

class RegistrationEscalationConfig
{
    public const KEY_OVERDUE_HOURS = 'registration_escalation_overdue_hours';
    public const KEY_DUE_SOON_HOURS = 'registration_escalation_due_soon_hours';
    public const KEY_COOLDOWN_MINUTES = 'registration_escalation_cooldown_minutes';
    public const KEY_RETRY_BATCH_LIMIT = 'registration_escalation_retry_batch_limit';

    public static function defaults(): array
    {
        return [
            'overdue_hours' => 48,
            'due_soon_hours' => 24,
            'cooldown_minutes' => 15,
            'retry_batch_limit' => 25,
        ];
    }

    public static function get(): array
    {
        $defaults = self::defaults();

        if (! Schema::hasTable('system_settings')) {
            return $defaults;
        }

        $map = SystemSetting::query()
            ->whereIn('key', [
                self::KEY_OVERDUE_HOURS,
                self::KEY_DUE_SOON_HOURS,
                self::KEY_COOLDOWN_MINUTES,
                self::KEY_RETRY_BATCH_LIMIT,
            ])
            ->pluck('value', 'key');

        return [
            'overdue_hours' => max(1, (int) ($map[self::KEY_OVERDUE_HOURS] ?? $defaults['overdue_hours'])),
            'due_soon_hours' => max(1, (int) ($map[self::KEY_DUE_SOON_HOURS] ?? $defaults['due_soon_hours'])),
            'cooldown_minutes' => max(1, (int) ($map[self::KEY_COOLDOWN_MINUTES] ?? $defaults['cooldown_minutes'])),
            'retry_batch_limit' => max(1, (int) ($map[self::KEY_RETRY_BATCH_LIMIT] ?? $defaults['retry_batch_limit'])),
        ];
    }

    public static function update(array $settings, ?int $updatedBy = null): void
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $rows = [
            [
                'key' => self::KEY_OVERDUE_HOURS,
                'value' => (string) $settings['overdue_hours'],
                'updated_by' => $updatedBy,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'key' => self::KEY_DUE_SOON_HOURS,
                'value' => (string) $settings['due_soon_hours'],
                'updated_by' => $updatedBy,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'key' => self::KEY_COOLDOWN_MINUTES,
                'value' => (string) $settings['cooldown_minutes'],
                'updated_by' => $updatedBy,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'key' => self::KEY_RETRY_BATCH_LIMIT,
                'value' => (string) $settings['retry_batch_limit'],
                'updated_by' => $updatedBy,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ];

        SystemSetting::query()->upsert($rows, ['key'], ['value', 'updated_by', 'updated_at']);
    }
}
