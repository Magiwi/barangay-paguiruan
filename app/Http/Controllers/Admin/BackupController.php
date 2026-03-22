<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        $disk = Storage::disk('local');
        $appName = config('backup.backup.name');
        $backupPath = $appName;

        $files = [];

        if ($disk->exists($backupPath)) {
            foreach ($disk->files($backupPath) as $file) {
                if (str_ends_with($file, '.zip')) {
                    $files[] = [
                        'path' => $file,
                        'name' => basename($file),
                        'size' => $disk->size($file),
                        'last_modified' => $disk->lastModified($file),
                    ];
                }
            }
        }

        usort($files, fn ($a, $b) => $b['last_modified'] <=> $a['last_modified']);

        return view('admin.backups.index', compact('files'));
    }

    public function run(): RedirectResponse
    {
        try {
            Artisan::call('backup:run', ['--only-db' => true]);
            $output = Artisan::output();

            AuditService::log('backup_created', null, 'Manual database backup triggered by admin');

            if (str_contains($output, 'Backup failed')) {
                return back()->with('error', 'Backup process encountered an error. Check logs for details.');
            }

            return back()->with('success', 'Database backup created successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function runFull(): RedirectResponse
    {
        try {
            Artisan::call('backup:run');
            $output = Artisan::output();

            AuditService::log('backup_full_created', null, 'Manual full backup (DB + files) triggered by admin');

            if (str_contains($output, 'Backup failed')) {
                return back()->with('error', 'Backup process encountered an error. Check logs for details.');
            }

            return back()->with('success', 'Full backup (database + files) created successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(string $file): BinaryFileResponse
    {
        $appName = config('backup.backup.name');
        $path = $appName . '/' . $file;

        if (! preg_match('/^[\w\-. ]+\.zip$/', $file)) {
            abort(403, 'Invalid file name.');
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($disk->path($path));
    }

    public function destroy(string $file): RedirectResponse
    {
        $appName = config('backup.backup.name');
        $path = $appName . '/' . $file;

        if (! preg_match('/^[\w\-. ]+\.zip$/', $file)) {
            abort(403, 'Invalid file name.');
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            abort(404, 'Backup file not found.');
        }

        $disk->delete($path);

        AuditService::log('backup_deleted', null, "Deleted backup file: {$file}");

        return back()->with('success', "Backup \"{$file}\" deleted.");
    }
}
