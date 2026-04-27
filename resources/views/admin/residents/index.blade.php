@extends('layouts.admin')

@section('title', 'Residents - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">Residents</h1>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->has('role'))
            <x-ui.alert type="error">
                {{ $errors->first('role') }}
            </x-ui.alert>
        @endif
        @if ($errors->has('suspend'))
            <x-ui.alert type="error">
                {{ $errors->first('suspend') }}
            </x-ui.alert>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.residents.index') }}" class="mb-4 rounded-2xl border border-gray-200 bg-white shadow-sm px-4 py-4 text-sm">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                {{-- Row 1: Basic filters --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, email, or contact"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Purok</label>
                    <select name="purok_id" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        @foreach ($puroks as $purok)
                            <option value="{{ $purok->id }}" @selected(request('purok_id') == $purok->id)>{{ $purok->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="role" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        @foreach (['resident','staff','admin'] as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Account Status</label>
                    <select name="status" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        @foreach (['pending','approved','rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Resident type</label>
                    <select name="resident_type" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        @foreach (['permanent' => 'Permanent', 'non-permanent' => 'Non-permanent'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('resident_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Row 2: Head of Family + Classification filters --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Head of Family</label>
                    <select name="head_of_family" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        <option value="yes" @selected(request('head_of_family') === 'yes')>Yes</option>
                        <option value="no" @selected(request('head_of_family') === 'no')>No</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">PWD</label>
                    <select name="is_pwd" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        <option value="yes" @selected(request('is_pwd') === 'yes')>Yes</option>
                        <option value="no" @selected(request('is_pwd') === 'no')>No</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Senior Citizen</label>
                    <select name="is_senior" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        <option value="yes" @selected(request('is_senior') === 'yes')>Yes</option>
                        <option value="no" @selected(request('is_senior') === 'no')>No</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Verification Status</label>
                    <select name="verification_status" class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                        <option value="">All</option>
                        <option value="pending" @selected(request('verification_status') === 'pending')>Pending</option>
                        <option value="verified" @selected(request('verification_status') === 'verified')>Verified</option>
                        <option value="rejected" @selected(request('verification_status') === 'rejected')>Rejected</option>
                    </select>
                </div>
                <div class="flex items-end justify-end gap-2">
                    <a href="{{ route('admin.residents.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-700">
                        Reset
                    </a>
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm inline-flex rounded-lg">
                        Apply
                    </button>
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.residents.show', $user) }}"
                                       class="font-medium text-gray-900 hover:text-blue-700 hover:underline">
                                        {{ $user->last_name }}, {{ $user->first_name }}
                                        @if ($user->middle_name)
                                            <span class="font-normal text-gray-500">{{ $user->middle_name }}</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="flex max-w-xs flex-col gap-1.5">
                                        @if (trim((string) $user->email) !== '')
                                            <a href="mailto:{{ $user->email }}"
                                               class="break-all text-blue-600 hover:text-blue-800 hover:underline"
                                               title="Open in your email app">{{ $user->email }}</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                        <a href="{{ route('admin.residents.show', $user) }}"
                                           class="inline-flex w-fit items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800">
                                            View profile
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $roleClasses = [
                                            'resident' => 'bg-blue-100 text-blue-800',
                                            'staff' => 'bg-amber-100 text-amber-800',
                                            'admin' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $roleClasses[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    @if (auth()->id() === $user->id)
                                        <span class="text-xs text-gray-500">You cannot change your own role or suspension.</span>
                                    @else
                                        <div class="flex flex-col gap-2">
                                            <form method="POST" action="{{ route('admin.residents.updateRole', $user) }}" class="flex items-center gap-2">
                                                @csrf
                                                <select name="role" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                                    <option value="resident" @selected($user->role === 'resident')>Resident</option>
                                                    <option value="staff" @selected($user->role === 'staff')>Staff</option>
                                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                                </select>
                                                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm inline-flex rounded-lg">
                                                    Update Role
                                                </button>
                                            </form>
                                            <div class="flex items-center gap-3">
                                                @if ($user->is_suspended)
                                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium bg-red-100 text-red-700">
                                                        Suspended
                                                    </span>
                                                    <form method="POST" action="{{ route('admin.residents.unsuspend', $user) }}">
                                                        @csrf
                                                        <button type="submit" class="text-xs font-medium text-green-600 hover:text-green-700">
                                                            Unsuspend
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium bg-green-50 text-green-700">
                                                        Active
                                                    </span>
                                                    <form method="POST" action="{{ route('admin.residents.suspend', $user) }}">
                                                        @csrf
                                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">
                                                            Suspend
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($users instanceof \Illuminate\Contracts\Pagination\Paginator && $users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</section>
@endsection

