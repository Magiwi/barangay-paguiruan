<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class AboutPageMedia
{
    /**
     * Resolve a stored path to a public URL.
     * - `http(s)://...` — unchanged
     * - `storage:path/on/public/disk` — Storage::disk('public')->url('path/on/public/disk')
     * - anything else — asset() (e.g. `images/ui design/foo.jpg`)
     */
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        if (str_starts_with($path, 'storage:')) {
            $relative = substr($path, strlen('storage:'));

            return Storage::disk('public')->url(ltrim($relative, '/'));
        }

        return asset($path);
    }
}
