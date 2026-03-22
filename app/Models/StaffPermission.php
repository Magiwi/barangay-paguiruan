<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPermission extends Model
{
    protected $fillable = [
        'user_id',
        'can_manage_registrations',
        'can_manage_blotter',
        'can_manage_announcements',
        'can_manage_complaints',
        'can_manage_reports',
    ];

    protected function casts(): array
    {
        return [
            'can_manage_registrations' => 'boolean',
            'can_manage_blotter' => 'boolean',
            'can_manage_announcements' => 'boolean',
            'can_manage_complaints' => 'boolean',
            'can_manage_reports' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
