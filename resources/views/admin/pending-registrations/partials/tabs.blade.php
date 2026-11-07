@php
    $rp = $routePrefix ?? 'admin';
    $active = $activeTab ?? ($status ?? 'pending');
    $tabs = [
        'pending' => ['label' => 'Pending', 'active' => 'bg-amber-600 text-white', 'badge' => 'bg-white/25 text-white'],
        'approved' => ['label' => 'Approved', 'active' => 'bg-green-600 text-white', 'badge' => 'bg-white/25 text-white'],
        'rejected' => ['label' => 'Rejected', 'active' => 'bg-red-600 text-white', 'badge' => 'bg-white/25 text-white'],
        'suspended' => ['label' => 'Suspended', 'active' => 'bg-gray-700 text-white', 'badge' => 'bg-white/25 text-white'],
    ];
@endphp
<div class="mb-4 flex flex-wrap gap-2">
    @foreach ($tabs as $key => $tab)
        <a href="{{ route($rp . '.pending-registrations.index', ['status' => $key]) }}"
           class="rounded-lg px-4 py-2 text-sm font-medium transition {{ $active === $key ? $tab['active'] : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
            {{ $tab['label'] }}
            <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full {{ $active === $key ? $tab['badge'] : 'bg-gray-300 text-gray-600' }} px-1.5 text-xs font-bold">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
    <a href="{{ route($rp . '.pending-registrations.index', ['status' => 'registered']) }}"
       class="rounded-lg px-4 py-2 text-sm font-medium transition {{ in_array($active, ['register', 'registered'], true) ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
        Register Resident
        <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full {{ in_array($active, ['register', 'registered'], true) ? 'bg-white/25 text-white' : 'bg-gray-300 text-gray-600' }} px-1.5 text-xs font-bold">{{ $counts['registered'] ?? 0 }}</span>
    </a>
</div>
