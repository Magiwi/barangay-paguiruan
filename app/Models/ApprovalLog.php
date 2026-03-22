<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    public const ACTION_APPROVED = 'approved';

    public const ACTION_REJECTED = 'rejected';

    public const ACTION_SUSPENDED = 'suspended';

    public const ACTION_UNSUSPENDED = 'unsuspended';

    protected $fillable = [
        'user_id',
        'action',
        'performed_by',
        'remarks',
    ];

    /**
     * The resident user that was approved or rejected.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The admin/staff user who performed the action.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
