@extends($layout ?? 'layouts.admin')

@section('title', 'e-Blotter Records - e-Governance Staff Panel')

@section('content')
@php $rp = $routePrefix ?? 'admin'; @endphp
<section class="px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        {{-- Page header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">e-Blotter Records</h1>
                <p class="mt-1 text-sm text-gray-600">Internal blotter document management</p>
            </div>
            <a href="{{ route($rp . '.blotters.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Blotter Entry
            </a>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert type="success">
                {{ session('success') }}
            </x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error">
                {{ session('error') }}
            </x-ui.alert>
        @endif

        <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            Retention Policy: Active blotter records older than
            <span class="font-semibold">{{ (int) ($retentionDays ?? 365) }} days</span>
            are automatically archived daily if they have no open request and no scheduled/ongoing hearing.
            Archived records remain restorable by admin.
        </div>

        {{-- Statistics cards --}}
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats->total ?? 0) }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1">Total Records</p>
            </div>
            <div class="rounded-2xl border border-green-200 bg-green-50 shadow-sm p-5 text-center">
                <p class="text-2xl font-bold text-green-700">{{ number_format($stats->active_count ?? 0) }}</p>
                <p class="text-xs font-medium text-green-600 mt-1">Active</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-gray-50 shadow-sm p-5 text-center">
                <p class="text-2xl font-bold text-gray-600">{{ number_format($stats->archived_count ?? 0) }}</p>
                <p class="text-xs font-medium text-gray-500 mt-1">Archived</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route($rp . '.blotters.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Blotter # or complainant name"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
            </div>
            {{-- Status --}}
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                </select>
            </div>
            {{-- Archived toggle --}}
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                    <input type="checkbox" name="archived" value="1" @checked(request('archived') === '1')
                           class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    Show Archived
                </label>
            </div>
            {{-- Buttons --}}
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                Filter
            </button>
            @if (request()->hasAny(['search', 'status', 'archived']))
                <a href="{{ route($rp . '.blotters.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Clear
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            @if ($blotters->isEmpty())
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-600">No blotter records found.</p>
                    <p class="mt-1 text-xs text-gray-400">
                        @if (request()->hasAny(['search', 'status', 'archived']))
                            Try adjusting your filters.
                        @else
                            Upload a new blotter entry to get started.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blotter #</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complainant</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incident Date</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($blotters as $blotter)
                                <tr class="hover:bg-gray-50 transition {{ $blotter->trashed() ? 'bg-gray-50/60 opacity-75' : '' }}">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900 font-mono">{{ $blotter->blotter_number }}</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <p class="text-sm font-medium text-gray-900">{{ $blotter->complainant_name }}</p>
                                        @if ($blotter->remarks)
                                            <p class="text-xs text-gray-500 mt-0.5 max-w-xs truncate" title="{{ $blotter->remarks }}">{{ Str::limit($blotter->remarks, 40) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $blotter->incident_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if ($blotter->uploadedBy)
                                            <p class="text-sm text-gray-900">{{ $blotter->uploadedBy->first_name }} {{ $blotter->uploadedBy->last_name }}</p>
                                        @else
                                            <span class="text-sm text-gray-400">--</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        @if ($blotter->status === 'active' && ! $blotter->trashed())
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-600">Archived</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if ($blotter->trashed())
                                                {{-- Restore button (admin only) --}}
                                                @if (in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                                    <form method="POST" action="{{ route($rp . '.blotters.restore', $blotter->id) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition"
                                                                onclick="return confirm('Restore this blotter record?')">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                            </svg>
                                                            Restore
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                @php
                                                    $evidenceExt = strtolower((string) pathinfo((string) $blotter->file_path, PATHINFO_EXTENSION));
                                                    $evidenceKind = in_array($evidenceExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
                                                        ? 'image'
                                                        : ($evidenceExt === 'pdf' ? 'pdf' : 'file');
                                                @endphp
                                                <a href="{{ route($rp . '.blotters.edit', $blotter) }}"
                                                   class="inline-flex items-center gap-1 rounded-lg bg-violet-100 px-3 py-1.5 text-xs font-medium text-violet-800 hover:bg-violet-200 transition"
                                                   title="Edit blotter">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Edit
                                                </a>
                                                {{-- Summons --}}
                                                <a href="{{ route($rp . '.blotters.summons.index', $blotter) }}"
                                                   class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-800 hover:bg-amber-200 transition"
                                                   title="Manage summons">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6.75a3 3 0 00-6 0v.75h6v-.75zM4.5 7.5h15m-13.5 0V18A2.25 2.25 0 008.25 20.25h7.5A2.25 2.25 0 0018 18V7.5M9 11.25h6m-6 3h6"/>
                                                    </svg>
                                                    Summons
                                                </a>
                                                <a href="{{ route($rp . '.blotters.hearings.index', $blotter) }}"
                                                   class="inline-flex items-center gap-1 rounded-lg bg-indigo-100 px-3 py-1.5 text-xs font-medium text-indigo-800 hover:bg-indigo-200 transition"
                                                   title="Manage hearings">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                                                    </svg>
                                                    Hearings
                                                </a>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-800 hover:bg-emerald-200 transition"
                                                    data-open-evidence
                                                    data-blotter-number="{{ $blotter->blotter_number }}"
                                                    data-handwritten-url="{{ route($rp . '.blotters.evidence.preview', ['blotter' => $blotter, 'type' => 'handwritten']) }}"
                                                    data-evidence-url="{{ $blotter->file_path ? route($rp . '.blotters.evidence.preview', ['blotter' => $blotter, 'type' => 'evidence']) : '' }}"
                                                    data-evidence-kind="{{ $blotter->file_path ? $evidenceKind : 'none' }}"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                                                    </svg>
                                                    View
                                                </button>
                                                {{-- Download --}}
                                                @if ($blotter->file_path)
                                                    <a href="{{ route($rp . '.blotters.download', $blotter) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 transition" title="Download file">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        Download
                                                    </a>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500" title="No optional evidence uploaded">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/>
                                                        </svg>
                                                        No File
                                                    </span>
                                                @endif
                                                {{-- Archive (admin only) --}}
                                                @if (in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                                    <form method="POST" action="{{ route($rp . '.blotters.archive', $blotter) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-gray-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700 transition"
                                                                onclick="return confirm('Archive this blotter record? It can be restored later.')">
                                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                            </svg>
                                                            Archive
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($blotters->hasPages())
                    <div class="border-t border-gray-200 px-5 py-3">
                        {{ $blotters->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</section>

<div id="evidenceModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4">
    <div class="w-full max-w-4xl rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Blotter Evidence Viewer</h2>
                <p id="evidenceModalSubtitle" class="text-xs text-gray-500">Quick view</p>
            </div>
            <button type="button" id="closeEvidenceModal" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                Close
            </button>
        </div>

        <div class="flex flex-wrap gap-2 border-b border-gray-100 px-5 py-3">
            <button type="button" id="tabHandwritten" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white">Sinumpaang Salaysay</button>
            <button type="button" id="tabEvidence" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700">Evidence / Proof</button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto p-5">
            <div id="panelHandwritten" class="space-y-3">
                <img id="handwrittenViewerImage" src="" alt="Sinumpaang Salaysay" class="max-h-[60vh] w-full rounded-lg border border-gray-200 object-contain">
                <a id="handwrittenOpenLink" href="#" target="_blank" rel="noopener" class="inline-flex items-center rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-800 hover:bg-emerald-200">Open in New Tab</a>
            </div>

            <div id="panelEvidence" class="hidden space-y-3">
                <img id="evidenceViewerImage" src="" alt="Evidence image" class="hidden max-h-[60vh] w-full rounded-lg border border-gray-200 object-contain">
                <iframe id="evidenceViewerPdf" src="" class="hidden h-[60vh] w-full rounded-lg border border-gray-200"></iframe>
                <p id="evidenceNoPreview" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    No preview available for this file type. Use "Open in New Tab" to inspect the file.
                </p>
                <p id="evidenceMissing" class="hidden rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                    No optional evidence uploaded for this blotter record.
                </p>
                <a id="evidenceOpenLink" href="#" target="_blank" rel="noopener" class="hidden inline-flex items-center rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-800 hover:bg-blue-200">Open in New Tab</a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('evidenceModal');
    var closeBtn = document.getElementById('closeEvidenceModal');
    var subtitle = document.getElementById('evidenceModalSubtitle');
    var tabHandwritten = document.getElementById('tabHandwritten');
    var tabEvidence = document.getElementById('tabEvidence');
    var panelHandwritten = document.getElementById('panelHandwritten');
    var panelEvidence = document.getElementById('panelEvidence');
    var handwrittenImage = document.getElementById('handwrittenViewerImage');
    var handwrittenLink = document.getElementById('handwrittenOpenLink');
    var evidenceImage = document.getElementById('evidenceViewerImage');
    var evidencePdf = document.getElementById('evidenceViewerPdf');
    var evidenceNoPreview = document.getElementById('evidenceNoPreview');
    var evidenceMissing = document.getElementById('evidenceMissing');
    var evidenceLink = document.getElementById('evidenceOpenLink');

    function switchTab(tab) {
        var handwrittenActive = tab === 'handwritten';
        tabHandwritten.className = handwrittenActive
            ? 'rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white'
            : 'rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700';
        tabEvidence.className = handwrittenActive
            ? 'rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700'
            : 'rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white';
        panelHandwritten.classList.toggle('hidden', !handwrittenActive);
        panelEvidence.classList.toggle('hidden', handwrittenActive);
    }

    function resetEvidencePanel() {
        evidenceImage.classList.add('hidden');
        evidencePdf.classList.add('hidden');
        evidenceNoPreview.classList.add('hidden');
        evidenceMissing.classList.add('hidden');
        evidenceLink.classList.add('hidden');
        evidenceImage.src = '';
        evidencePdf.src = '';
        evidenceLink.href = '#';
    }

    document.querySelectorAll('[data-open-evidence]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var blotterNumber = trigger.getAttribute('data-blotter-number') || 'N/A';
            var handwrittenUrl = trigger.getAttribute('data-handwritten-url') || '';
            var evidenceUrl = trigger.getAttribute('data-evidence-url') || '';
            var evidenceKind = trigger.getAttribute('data-evidence-kind') || 'none';

            subtitle.textContent = 'Blotter #: ' + blotterNumber;
            handwrittenImage.src = handwrittenUrl;
            handwrittenLink.href = handwrittenUrl;

            resetEvidencePanel();
            if (!evidenceUrl) {
                evidenceMissing.classList.remove('hidden');
            } else {
                evidenceLink.href = evidenceUrl;
                evidenceLink.classList.remove('hidden');
                if (evidenceKind === 'image') {
                    evidenceImage.src = evidenceUrl;
                    evidenceImage.classList.remove('hidden');
                } else if (evidenceKind === 'pdf') {
                    evidencePdf.src = evidenceUrl;
                    evidencePdf.classList.remove('hidden');
                } else {
                    evidenceNoPreview.classList.remove('hidden');
                }
            }

            switchTab('handwritten');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        });
    });

    tabHandwritten.addEventListener('click', function () { switchTab('handwritten'); });
    tabEvidence.addEventListener('click', function () { switchTab('evidence'); });

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        handwrittenImage.src = '';
        handwrittenLink.href = '#';
        resetEvidencePanel();
    }

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>
@endsection
