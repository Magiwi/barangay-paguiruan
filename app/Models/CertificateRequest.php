<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateRequest extends Model
{
    protected $fillable = [
        'user_id',
        'certificate_type',
        'purpose',
        'extra_fields',
        'residency_years_text',
        'certificate_name_override',
        'certificate_address_override',
        'certificate_issued_on',
        'status',
        'remarks',
        'reviewed_at',
        'reviewed_by',
        'released_at',
        'released_by',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'released_at' => 'datetime',
            'certificate_issued_on' => 'date',
            'extra_fields' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin/staff who released this certificate.
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * The admin/staff who last reviewed this request before approval.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
