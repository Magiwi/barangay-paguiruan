@extends('layouts.admin')

@section('title', 'Resident Duplicates - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">Resident Duplicate Resolver</h1>
                <p class="text-sm text-gray-500">Review probable duplicate resident records and merge secondary accounts into a primary account.</p>
            </div>
            <a href="{{ route('admin.residents.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                Back to Residents
            </a>
        </div>

        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->has('primary_user_id') || $errors->has('secondary_user_id') || $errors->has('confirm_same_person') || $errors->has('undo'))
            <x-ui.alert type="error">
                {{ $errors->first('primary_user_id')
                    ?: ($errors->first('secondary_user_id')
                    ?: ($errors->first('confirm_same_person') ?: $errors->first('undo'))) }}
            </x-ui.alert>
        @endif

        @if (isset($recentMerges) && $recentMerges->count())
            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-900">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <p class="font-semibold">Recent duplicate merges</p>
                    <p class="text-[11px] text-blue-800">You can undo a merge while the duplicate remains suspended.</p>
                </div>
                <div class="-mx-2 overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-xs">
                        <thead class="bg-blue-100/60">
                            <tr>
                                <th class="px-2 py-1 text-left font-medium text-blue-900">When</th>
                                <th class="px-2 py-1 text-left font-medium text-blue-900">Primary</th>
                                <th class="px-2 py-1 text-left font-medium text-blue-900">Duplicate</th>
                                <th class="px-2 py-1 text-left font-medium text-blue-900">By</th>
                                <th class="px-2 py-1 text-right font-medium text-blue-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-100">
                            @foreach ($recentMerges as $merge)
                                <tr>
                                    <td class="px-2 py-1 text-blue-900">
                                        {{ optional($merge->created_at)->format('M d, Y h:i A') }}
                                        @if ($merge->undone_at)
                                            <span class="ml-1 inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-800">Undone</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1 text-blue-900">
                                        {{ $merge->primary?->full_name ?? 'N/A' }} (ID: {{ $merge->primary_user_id }})
                                    </td>
                                    <td class="px-2 py-1 text-blue-900">
                                        {{ $merge->secondary?->full_name ?? 'N/A' }} (ID: {{ $merge->secondary_user_id }})
                                    </td>
                                    <td class="px-2 py-1 text-blue-900">
                                        {{ $merge->performedBy?->full_name ?? 'System' }}
                                    </td>
                                    <td class="px-2 py-1 text-right">
                                        @if (! $merge->undone_at)
                                            <form method="POST" action="{{ route('admin.residents.duplicates.undo') }}" onsubmit="return confirm('Undo this merge and restore the duplicate account links?');" class="inline">
                                                @csrf
                                                <input type="hidden" name="merge_log_id" value="{{ $merge->id }}">
                                                <button type="submit" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-medium text-blue-800 shadow-sm hover:bg-blue-50">
                                                    Undo Merge
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-green-800">Already undone</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Merge action will transfer linked service records (requests/logs) to the selected primary account and suspend the duplicate account.
            Do this only after confirming both records are the same person.
        </div>

        @forelse ($groups as $group)
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-800">{{ $group['display_name'] }}</h2>
                    <p class="text-xs text-gray-500">
                        Birthdate: {{ \Illuminate\Support\Carbon::parse($group['birthdate'])->format('M d, Y') }}
                        &middot;
                        {{ $group['duplicate_count'] }} matching records
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Contact</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Family</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($group['members'] as $member)
                                <tr>
                                    <td class="px-4 py-2 text-gray-700">{{ $member->id }}</td>
                                    <td class="px-4 py-2 text-gray-900">{{ $member->full_name }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $member->email ?: '—' }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $member->contact_number ?: '—' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($member->status) }}
                                        </span>
                                        @if ($member->is_suspended)
                                            <span class="ml-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-medium bg-red-100 text-red-700">Suspended</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-gray-700">
                                        @if ($member->head_of_family === 'yes')
                                            Head
                                        @elseif ($member->head_of_family_id)
                                            Linked (Head ID: {{ $member->head_of_family_id }})
                                        @else
                                            Unlinked
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.residents.duplicates.merge') }}" class="duplicate-merge-form grid grid-cols-1 gap-3 border-t border-gray-200 px-4 py-4 md:grid-cols-3 md:items-end">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Primary Account (keep)</label>
                        <select name="primary_user_id" required class="primary-user-select block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach ($group['members'] as $member)
                                <option value="{{ $member->id }}"
                                        data-transfer-total="{{ (int) $member->transfer_total }}"
                                        data-transfer-breakdown='@json($member->transfer_breakdown)'>
                                    {{ $member->full_name }} (ID: {{ $member->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600">Duplicate Account (suspend)</label>
                        <select name="secondary_user_id" required class="secondary-user-select block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach ($group['members'] as $member)
                                <option value="{{ $member->id }}"
                                        data-transfer-total="{{ (int) $member->transfer_total }}"
                                        data-transfer-breakdown='@json($member->transfer_breakdown)'>
                                    {{ $member->full_name }} (ID: {{ $member->id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-start md:justify-end">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">
                            Merge Duplicate
                        </button>
                    </div>

                    <div class="md:col-span-3 rounded-xl border border-blue-100 bg-blue-50 px-3 py-3 text-xs text-blue-900">
                        <p class="font-semibold">Impact preview</p>
                        <p class="mt-1">
                            Estimated records to transfer from duplicate account:
                            <span class="transfer-total font-semibold">0</span>
                        </p>
                        <div class="transfer-breakdown mt-2 text-blue-800"></div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="inline-flex items-start gap-2 text-xs text-gray-700">
                            <input type="checkbox" name="confirm_same_person" value="1" required class="mt-0.5 rounded border-gray-300 text-red-700 focus:ring-red-600">
                            <span>I confirm these two records are the same resident and I understand this action will suspend the duplicate account.</span>
                        </label>
                    </div>
                </form>
            </div>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-sm text-gray-500 shadow-sm">
                No probable duplicate resident groups found.
            </div>
        @endforelse
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.duplicate-merge-form');

    forms.forEach(function (form) {
        const secondary = form.querySelector('.secondary-user-select');
        const totalNode = form.querySelector('.transfer-total');
        const breakdownNode = form.querySelector('.transfer-breakdown');

        const renderPreview = function () {
            if (!secondary || !totalNode || !breakdownNode) return;

            const selected = secondary.options[secondary.selectedIndex];
            if (!selected) return;

            const total = Number(selected.dataset.transferTotal || 0);
            totalNode.textContent = String(total);

            let breakdown = {};
            try {
                breakdown = JSON.parse(selected.dataset.transferBreakdown || '{}');
            } catch (e) {
                breakdown = {};
            }

            const nonZero = Object.entries(breakdown).filter(function (entry) {
                return Number(entry[1] || 0) > 0;
            });

            if (nonZero.length === 0) {
                breakdownNode.textContent = 'No linked records found under this duplicate account.';
                return;
            }

            breakdownNode.textContent = nonZero
                .map(function (entry) { return entry[0] + ': ' + entry[1]; })
                .join(' | ');
        };

        if (secondary) {
            secondary.addEventListener('change', renderPreview);
            renderPreview();
        }
    });
});
</script>
@endpush

