<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'status',
        'is_published',   // @deprecated — use status instead
        'published_at',
        'user_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::creating(function (Announcement $announcement) {
            if (empty($announcement->slug)) {
                $announcement->slug = static::generateUniqueSlug($announcement->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'announcement';
        $slug = $baseSlug;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected $casts = [
        'is_published' => 'boolean',  // @deprecated
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(AnnouncementLabel::class);
    }

    /**
     * Scope: only approved (visible to residents).
     * Replaces the old is_published check.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * @deprecated Use scopeApproved() instead. Kept for backward compatibility.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Check if announcement is visible to residents.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
