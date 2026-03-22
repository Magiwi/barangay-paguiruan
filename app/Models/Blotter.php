<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blotter extends Model
{
    use SoftDeletes;

    private const RESPONDENT_EMPTY_VALUES = ['—', '-', ''];

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    /**
     * Only content fields are mass-assignable.
     *
     * Guarded (set via forceFill only):
     *   blotter_number, uploaded_by, status
     */
    protected $fillable = [
        'complainant_name',
        'complainant_user_id',
        'incident_date',
        'file_path',
        'handwritten_salaysay_path',
        'remarks',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'is_uncooperative' => 'boolean',
        ];
    }

    // ── Helpers ──

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Resolve respondent name from direct column or structured remarks fallback.
     */
    public function getRespondentNameAttribute(?string $value): string
    {
        $normalized = trim((string) $value);
        if (! in_array($normalized, self::RESPONDENT_EMPTY_VALUES, true)) {
            return $normalized;
        }

        $parsed = $this->extractRespondentNameFromRemarks();

        return $parsed ?: 'N/A';
    }

    public function getRespondentDisplayNameAttribute(): string
    {
        return $this->respondent_name ?: 'N/A';
    }

    private function extractRespondentNameFromRemarks(): ?string
    {
        $remarks = trim((string) ($this->attributes['remarks'] ?? $this->remarks ?? ''));
        if ($remarks === '') {
            return null;
        }

        if (preg_match('/^\s*(?:[-*]\s*)?Respondent(?:\/s|\s+Name)?\s*:\s*(.+)\s*$/mi', $remarks, $matches) === 1) {
            $name = trim($matches[1]);
            if (! in_array($name, self::RESPONDENT_EMPTY_VALUES, true)) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Generate the next blotter number: BLT-YYYY-XXXXX
     */
    public static function generateBlotterNumber(): string
    {
        $year = now()->year;
        $prefix = "BLT-{$year}-";

        $last = static::withTrashed()
            ->where('blotter_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('blotter_number')
            ->value('blotter_number');

        if ($last) {
            $sequence = (int) substr($last, strlen($prefix)) + 1;
        } else {
            $sequence = 1;
        }

        return $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function complainantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complainant_user_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(BlotterRequest::class);
    }

    public function summons(): HasMany
    {
        return $this->hasMany(Summon::class);
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(Hearing::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BlotterRevision::class);
    }
}
