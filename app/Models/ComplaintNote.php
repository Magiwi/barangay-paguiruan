<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintNote extends Model
{
    /**
     * Only the note text is mass-assignable.
     *
     * Guarded (set via forceFill only):
     *   issue_report_id, user_id
     */
    protected $fillable = [
        'note',
    ];

    // ── Relationships ──

    public function issue(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
