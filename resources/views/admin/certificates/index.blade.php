@extends('layouts.admin')

@section('title', 'Certificate Requests - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">Certificate Requests</h1>

        @if (session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.certificates.index') }}" class="mb-4 flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Status:</label>
                <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="released" @selected(request('status') === 'released')>Released</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Type:</label>
                <select name="certificate_type" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                    <option value="">All Types</option>
                    <option value="Barangay Clearance" @selected(request('certificate_type') === 'Barangay Clearance')>Barangay Clearance</option>
                    <option value="Certificate of Indigency" @selected(request('certificate_type') === 'Certificate of Indigency')>Certificate of Indigency</option>
                    <option value="Residency Certificate" @selected(request('certificate_type') === 'Residency Certificate')>Residency Certificate</option>
                    <option value="Barangay Certificate" @selected(request('certificate_type') === 'Barangay Certificate')>Barangay Certificate</option>
                </select>
            </div>
            @if (request('status') || request('certificate_type'))
                <a href="{{ route('admin.certificates.index') }}" class="text-xs text-gray-500 hover:text-gray-700">Clear all</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @if ($requests->isEmpty())
                <div class="p-8 text-center text-gray-600">
                    @if (request('status'))
                        No {{ request('status') }} certificate requests found.
                    @else
                        No certificate requests yet.
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($requests as $req)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $req->user->first_name }} {{ $req->user->last_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $req->user->email }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $req->certificate_type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                        <p>{{ Str::limit($req->purpose, 50) }}</p>
                                        @if (!empty($req->extra_fields) && is_array($req->extra_fields))
                                            <div class="mt-1 space-y-0.5 text-xs text-gray-500">
                                                @foreach ($req->extra_fields as $key => $value)
                                                    @if ($key === 'valid_id_path')
                                                        <p><span class="font-medium text-gray-600">Valid Id:</span> Uploaded</p>
                                                    @elseif (!is_array($value))
                                                        <p><span class="font-medium text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($req->status === 'pending')
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-amber-100 text-amber-800">Pending</span>
                                        @elseif ($req->status === 'approved')
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                        @elseif ($req->status === 'released')
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800">Released</span>
                                        @else
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $req->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">
                                        @if ($req->status === 'pending')
                                            <div class="space-y-2">
                                                <a href="{{ route('admin.certificates.review.edit', $req) }}" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                    Review / Edit
                                                </a>
                                                @if ($req->reviewed_at)
                                                    <p class="text-xs text-indigo-700">
                                                        Reviewed: {{ $req->reviewed_at->format('M d, Y H:i') }}
                                                        @if ($req->reviewedBy)
                                                            by {{ $req->reviewedBy->first_name }} {{ $req->reviewedBy->last_name }}
                                                        @endif
                                                    </p>
                                                @endif
                                                <form method="POST" action="{{ route('admin.certificates.update', $req) }}" class="flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="status" value="approved">
                                                    <input type="text" name="remarks" placeholder="Remarks (optional)" class="min-w-[8rem] flex-1 rounded border border-gray-300 px-2 py-1 text-sm sm:max-w-xs">
                                                    <button type="submit" class="rounded bg-green-600 px-2 py-1 text-xs font-medium text-white hover:bg-green-700">Approve</button>
                                                </form>
                                                <button type="button"
                                                        class="rounded bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700"
                                                        onclick="document.getElementById('reject-cert-dialog-{{ $req->id }}')?.showModal()">
                                                    Reject
                                                </button>
                                                <dialog id="reject-cert-dialog-{{ $req->id }}" class="w-full max-w-md rounded-xl border border-gray-200 p-0 shadow-xl backdrop:bg-gray-900/40">
                                                    <form method="POST" action="{{ route('admin.certificates.update', $req) }}" class="flex flex-col">
                                                        @csrf
                                                        <input type="hidden" name="status" value="rejected">
                                                        <div class="border-b border-gray-100 px-5 py-4">
                                                            <h2 class="text-sm font-semibold text-gray-900">Reject certificate request</h2>
                                                            <p class="mt-1 text-xs text-gray-500">{{ $req->certificate_type }} — {{ Str::limit($req->user->first_name.' '.$req->user->last_name, 48) }}</p>
                                                        </div>
                                                        <div class="px-5 py-4">
                                                            <label for="reject-remarks-{{ $req->id }}" class="block text-xs font-medium text-gray-700">Reason / justification <span class="text-red-500">*</span></label>
                                                            <textarea id="reject-remarks-{{ $req->id }}" name="remarks" rows="4" required maxlength="1000" placeholder="Explain why this request is being rejected (required)."
                                                                      class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20"></textarea>
                                                            <p class="mt-1 text-xs text-gray-400">The resident will see this in their notifications and request list.</p>
                                                        </div>
                                                        <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
                                                            <button type="button" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                                                    onclick="document.getElementById('reject-cert-dialog-{{ $req->id }}')?.close()">Cancel</button>
                                                            <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">Confirm rejection</button>
                                                        </div>
                                                    </form>
                                                </dialog>
                                            </div>
                                        @elseif ($req->status === 'approved')
                                            <div class="space-y-2">
                                                @if ($req->reviewed_at)
                                                    <p class="text-xs text-indigo-700">
                                                        Reviewed: {{ $req->reviewed_at->format('M d, Y H:i') }}
                                                        @if ($req->reviewedBy)
                                                            by {{ $req->reviewedBy->first_name }} {{ $req->reviewedBy->last_name }}
                                                        @endif
                                                    </p>
                                                @endif
                                                @if (str_contains(strtolower((string) $req->certificate_type), 'residency'))
                                                    <a href="{{ route('admin.certificates.residency-template.edit', $req) }}" class="inline-flex rounded bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700">
                                                        Edit Template
                                                    </a>
                                                    <a href="{{ route('admin.certificates.residency-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (str_contains(strtolower((string) $req->certificate_type), 'indigency'))
                                                    <a href="{{ route('admin.certificates.indigency-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (str_contains(strtolower((string) $req->certificate_type), 'clearance'))
                                                    <a href="{{ route('admin.certificates.clearance-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (strtolower(trim((string) $req->certificate_type)) === 'barangay certificate')
                                                    <a href="{{ route('admin.certificates.barangay-certificate-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                <form method="POST" action="{{ route('admin.certificates.release', $req) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm rounded">
                                                        Mark as Released
                                                    </button>
                                                </form>
                                                @if ($req->remarks)
                                                    <p class="text-xs text-gray-600" title="{{ $req->remarks }}">{{ Str::limit($req->remarks, 30) }}</p>
                                                @endif
                                            </div>
                                        @elseif ($req->status === 'released')
                                            <div class="space-y-1">
                                                @if ($req->reviewed_at)
                                                    <p class="text-xs text-indigo-700">
                                                        Reviewed: {{ $req->reviewed_at->format('M d, Y H:i') }}
                                                        @if ($req->reviewedBy)
                                                            by {{ $req->reviewedBy->first_name }} {{ $req->reviewedBy->last_name }}
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="text-xs text-blue-600 font-medium">Released: {{ $req->released_at->format('M d, Y H:i') }}</p>
                                                @if ($req->releasedBy)
                                                    <p class="text-xs text-gray-500">By: {{ $req->releasedBy->first_name }} {{ $req->releasedBy->last_name }}</p>
                                                @endif
                                                @if (str_contains(strtolower((string) $req->certificate_type), 'residency'))
                                                    <a href="{{ route('admin.certificates.residency-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (str_contains(strtolower((string) $req->certificate_type), 'indigency'))
                                                    <a href="{{ route('admin.certificates.indigency-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (str_contains(strtolower((string) $req->certificate_type), 'clearance'))
                                                    <a href="{{ route('admin.certificates.clearance-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if (strtolower(trim((string) $req->certificate_type)) === 'barangay certificate')
                                                    <a href="{{ route('admin.certificates.barangay-certificate-template.print', $req) }}" target="_blank" class="inline-flex rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                                        Print Template
                                                    </a>
                                                @endif
                                                @if ($req->remarks)
                                                    <p class="text-xs text-gray-600" title="{{ $req->remarks }}">{{ Str::limit($req->remarks, 30) }}</p>
                                                @endif
                                            </div>
                                        @else
                                            @if ($req->remarks)
                                                <p class="text-xs text-gray-600" title="{{ $req->remarks }}">{{ Str::limit($req->remarks, 40) }}</p>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($requests->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>
@endsection
