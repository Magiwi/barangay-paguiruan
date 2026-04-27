<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SitePageLayout extends Model
{
    public const PAGE_ABOUT = 'about';

    protected $fillable = [
        'page_key',
        'draft_sections',
        'published_sections',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'draft_sections' => 'array',
            'published_sections' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function hasPublishedSnapshot(): bool
    {
        return is_array($this->published_sections)
            && $this->published_sections !== []
            && $this->published_at !== null;
    }
}
