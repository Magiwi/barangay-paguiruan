<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'related_id',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public const TYPE_CERTIFICATE = 'certificate';

    public const TYPE_PERMIT = 'permit';

    public const TYPE_COMPLAINT = 'complaint';

    public const TYPE_BLOTTER = 'blotter';

    public const TYPE_ANNOUNCEMENT = 'announcement';

    public const TYPE_HOUSEHOLD_TRANSFER = 'household_transfer';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
