<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    protected $fillable = [
        'user_id',
        'position_id',
        'term_start',
        'term_end',
        'is_active',
        'photo',
        'committee',
    ];

    protected function casts(): array
    {
        return [
            'term_start' => 'date',
            'term_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    // ── Accessors ──

    public function photoUrl(): string
    {
        return $this->photo
            ? asset('storage/'.$this->photo)
            : 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%239ca3af"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>');
    }

    // ── Relationships ──

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // ── Term Logic ──

    /**
     * Check if this official's term has expired.
     */
    public function isExpired(): bool
    {
        return $this->term_end && $this->term_end->isPast();
    }

    /**
     * Check if this official should have their access revoked.
     */
    public function shouldRevokeAccess(): bool
    {
        return ! $this->is_active || $this->isExpired();
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrentlyServing($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('term_end')
                    ->orWhere('term_end', '>=', now()->toDateString());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('term_end')
            ->where('term_end', '<', now()->toDateString());
    }
}
