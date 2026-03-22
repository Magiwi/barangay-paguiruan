<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Record an audit log entry.
     *
     * @param  string       $action       Short action key (e.g. 'role_updated').
     * @param  Model|null   $target       The Eloquent model being acted upon.
     * @param  string|null  $description  Human-readable description of the action.
     */
    public static function log(string $action, ?Model $target = null, ?string $description = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
