<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationAlertRun extends Model
{
    protected $fillable = [
        'command_name',
        'trigger_source',
        'triggered_by_user_id',
        'trigger_reason',
        'threshold_hours',
        'overdue_count',
        'due_soon_count',
        'missing_id_count',
        'recipients_targeted',
        'recipients_sent',
        'status',
        'notes',
        'ran_at',
    ];

    protected function casts(): array
    {
        return [
            'ran_at' => 'datetime',
        ];
    }
}
