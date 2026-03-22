@extends($layout ?? 'layouts.admin')

@section('title', 'Approval History - e-Governance Admin Panel')

@section('content')
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">Approval History</h1>
        <p class="text-sm text-gray-600 mb-6">Read-only log of who approved or rejected registrations and when.</p>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Performed by</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $log->user ? trim($log->user->last_name . ', ' . $log->user->first_name . ($log->user->middle_name ? ' ' . $log->user->middle_name : '')) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($log->action === 'approved')
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                    @else
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $log->performer ? trim($log->performer->first_name . ' ' . ($log->performer->middle_name ?? '') . ' ' . $log->performer->last_name) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $log->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $log->remarks ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No approval history yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
