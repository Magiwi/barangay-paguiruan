@extends('layouts.admin')

@section('title', 'Permit Applications - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">Permit Applications</h1>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Status Filter --}}
        <form method="GET" action="{{ route('admin.permits.index') }}" class="mb-4 flex items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Status:</label>
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">All</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="released" @selected(request('status') === 'released')>Released</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
            @if (request('status'))
                <a href="{{ route('admin.permits.index') }}" class="text-xs text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @if ($permits->isEmpty())
                <div class="p-8 text-center text-gray-600">
                    @if (request('status'))
                        No {{ request('status') }} permit applications found.
                    @else
                        No permit applications yet.
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permit Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Document</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($permits as $permit)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $permit->applicant->first_name }} {{ $permit->applicant->last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $permit->applicant->email }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $permit->permit_type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                        <p>{{ Str::limit($permit->purpose, 50) }}</p>
                                        @if (!empty($permit->extra_fields) && is_array($permit->extra_fields))
                                            @php
                                                $fieldLabels = [
                                                    'business_name' => 'Business Name',
                                                    'business_address' => 'Business Address',
                                                    'purpose_other' => 'Specified Purpose',
                                                    'previous_permit_number' => 'Previous Permit Number',
                                                    'last_permit_year' => 'Last Permit Year',
                                                    'old_business_address' => 'Old Business Address',
                                                    'new_business_address' => 'New Business Address',
                                                    'old_business_name' => 'Old Business Name',
                                                    'new_business_name' => 'New Business Name',
                                                    'previous_owner_name' => 'Previous Owner Name',
                                                    'new_owner_name' => 'New Owner Name',
                                                    'current_line_of_business' => 'Current Line of Business',
                                                    'additional_line_of_business' => 'Additional Line of Business',
                                                    'closure_effective_date' => 'Closure Effective Date',
                                                    'closure_reason' => 'Closure Reason',
                                                    'agency_name' => 'Agency / Office',
                                                    'reference_number' => 'Reference Number',
                                                    'financing_institution' => 'Institution / Bank Name',
                                                    'financing_reference_number' => 'Financing Reference Number',
                                                ];

                                                $extraFields = $permit->extra_fields;
                                                unset($extraFields['purpose']);

                                                $displayKeys = array_keys($extraFields);
                                                if (strtolower((string) $permit->permit_type) === 'business permit') {
                                                    $preferredOrder = [
                                                        'business_name',
                                                        'business_address',
                                                        'purpose_other',
                                                        'previous_permit_number',
                                                        'last_permit_year',
                                                        'old_business_address',
                                                        'new_business_address',
                                                        'old_business_name',
                                                        'new_business_name',
                                                        'previous_owner_name',
                                                        'new_owner_name',
                                                        'current_line_of_business',
                                                        'additional_line_of_business',
                                                        'closure_effective_date',
                                                        'closure_reason',
                                                        'agency_name',
                                                        'reference_number',
                                                        'financing_institution',
                                                        'financing_reference_number',
                                                    ];

                                                    $displayKeys = array_values(array_merge(
                                                        array_intersect($preferredOrder, $displayKeys),
                                                        array_diff($displayKeys, $preferredOrder)
                                                    ));
                                                }
                                            @endphp
                                            <div class="mt-1 space-y-0.5 text-xs text-gray-500">
                                                @foreach ($displayKeys as $key)
                                                    @php $value = $extraFields[$key] ?? null; @endphp
                                                    @if (!is_array($value))
                                                        <p><span class="font-medium text-gray-600">{{ $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($permit->document_path)
                                            <a href="{{ route('admin.permits.document', $permit) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($permit->status === 'pending')
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                        @elseif ($permit->status === 'approved')
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                        @elseif ($permit->status === 'released')
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800">Released</span>
                                        @else
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $permit->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        @if ($permit->status === 'pending')
                                            <div class="space-y-2">
                                                <form method="POST" action="{{ route('admin.permits.approve', $permit) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="remarks" placeholder="Remarks (optional)" class="rounded border border-gray-300 px-2 py-1 text-sm w-24">
                                                    <button type="submit" class="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.permits.reject', $permit) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="remarks" placeholder="Reason (required)" required class="rounded border border-gray-300 px-2 py-1 text-sm w-24">
                                                    <button type="submit" class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700">Reject</button>
                                                </form>
                                            </div>
                                        @elseif ($permit->status === 'approved')
                                            <div class="space-y-2">
                                                @if (strtolower(trim((string) $permit->permit_type)) === 'business permit')
                                                    <a href="{{ route('admin.permits.business-template.print', $permit) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (strtolower(trim((string) $permit->permit_type)) === 'event permit')
                                                    <a href="{{ route('admin.permits.event-template.print', $permit) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (strtolower(trim((string) $permit->permit_type)) === 'building permit')
                                                    <a href="{{ route('admin.permits.building-template.print', $permit) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                <form method="POST" action="{{ route('admin.permits.release', $permit) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm rounded">
                                                        Mark as Released
                                                    </button>
                                                </form>
                                                @if ($permit->remarks)
                                                    <p class="text-xs text-gray-600" title="{{ $permit->remarks }}">{{ Str::limit($permit->remarks, 30) }}</p>
                                                @endif
                                            </div>
                                        @elseif ($permit->status === 'released')
                                            <div class="space-y-1">
                                                <p class="text-xs text-blue-600 font-medium">Released: {{ $permit->released_at->format('M d, Y H:i') }}</p>
                                                @if ($permit->releasedBy)
                                                    <p class="text-xs text-gray-500">By: {{ $permit->releasedBy->first_name }} {{ $permit->releasedBy->last_name }}</p>
                                                @endif
                                                @if (strtolower(trim((string) $permit->permit_type)) === 'business permit')
                                                    <a href="{{ route('admin.permits.business-template.print', $permit) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (strtolower(trim((string) $permit->permit_type)) === 'event permit')
                                                    <a href="{{ route('admin.permits.event-template.print', $permit) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (strtolower(trim((string) $permit->permit_type)) === 'building permit')
                                                    <a href="{{ route('admin.permits.building-template.print', $permit) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if ($permit->remarks)
                                                    <p class="text-xs text-gray-600" title="{{ $permit->remarks }}">{{ Str::limit($permit->remarks, 30) }}</p>
                                                @endif
                                            </div>
                                        @else
                                            @if ($permit->remarks)
                                                <p class="text-xs text-gray-600" title="{{ $permit->remarks }}">{{ Str::limit($permit->remarks, 40) }}</p>
                                            @else
                                                <span class="text-xs text-gray-400">&mdash;</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($permits->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $permits->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>
@endsection
