@php
    $message = trim((string) ($exception->getMessage() ?: ''));
    if ($message === '') {
        $message = 'You do not have permission to access this page or action.';
    }
    $homeUrl = auth()->check()
        ? match (auth()->user()->role) {
            \App\Models\User::ROLE_RESIDENT => route('resident.dashboard'),
            \App\Models\User::ROLE_STAFF => route('staff.dashboard'),
            \App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPER_ADMIN => route('admin.dashboard'),
            default => url('/'),
        }
        : route('login');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access not allowed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <main class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-6 py-12">
        <div class="rounded-2xl border border-amber-200 bg-white p-8 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z"/>
                </svg>
            </div>
            <h1 class="mt-4 text-lg font-semibold text-slate-900">You can’t continue here</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $message }}</p>
            <p class="mt-4 text-xs text-slate-500">
                If you need access to a module (registrations, blotter, announcements, etc.), ask an administrator to update your account under <strong>Residents</strong> → your staff user → <strong>User capabilities</strong>.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ $homeUrl }}" class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                    Go to dashboard
                </a>
                @if (auth()->check())
                    <button type="button" onclick="history.back()" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Go back
                    </button>
                @endif
            </div>
        </div>
        <p class="mt-6 text-center text-xs text-slate-400">Error 403 · Barangay Paguiruan e-Governance</p>
    </main>
</body>
</html>
