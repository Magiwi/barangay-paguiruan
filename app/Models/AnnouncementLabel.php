<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AnnouncementLabel extends Model
{
    use HasFactory;

    const DEFAULT_COLORS = [
        'emergency' => 'bg-red-100 text-red-800',
        'health'    => 'bg-green-100 text-green-800',
        'ayuda'     => 'bg-yellow-100 text-yellow-800',
        'advisory'  => 'bg-blue-100 text-blue-800',
        'event'     => 'bg-purple-100 text-purple-800',
    ];

    const DEFAULT_COLOR = 'bg-gray-100 text-gray-800';

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class);
    }

    public static function colorForSlug(string $slug): string
    {
        return self::DEFAULT_COLORS[$slug] ?? self::DEFAULT_COLOR;
    }
}
