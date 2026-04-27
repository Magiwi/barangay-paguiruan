@extends('layouts.admin')

@section('title', 'About Page Builder')

@push('styles')
<style>
    #about-preview-frame { width: 100%; min-height: 720px; border: 1px solid #e5e7eb; border-radius: 0.75rem; background: #fff; }
    .preview-shell-desktop { max-width: 100%; }
    .preview-shell-tablet { max-width: 768px; margin-left: auto; margin-right: auto; }
    .preview-shell-mobile { max-width: 390px; margin-left: auto; margin-right: auto; }
    .section-card.dragging { opacity: 0.85; }
</style>
@endpush

@section('content')
<div class="max-w-[1600px] mx-auto px-4 py-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">About Page Builder</h1>
            <p class="mt-1 text-sm text-gray-600">Reorder sections, toggle visibility, and edit copy. Residents see the published version only.</p>
            @if($layout->published_at)
                <p class="mt-2 text-xs text-gray-500">Last published: {{ $layout->published_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
            @else
                <p class="mt-2 text-sm text-amber-700">No published version yet — residents still see built-in defaults until you publish.</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" id="btn-save-draft" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50">Save draft</button>
            <form method="post" action="{{ route('admin.about-page.publish') }}" class="inline" onsubmit="return confirm('Publish these changes to the live About page?');">
                @csrf
                <button type="submit" class="ui-btn ui-btn-primary rounded-lg">Publish</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Sections</h2>
                <span class="text-xs text-gray-400">Drag ⋮⋮ to reorder</span>
            </div>
            <div id="section-editors" class="space-y-3">
                @foreach($draftSections as $section)
                    <div class="section-card rounded-xl border border-gray-200 bg-white p-4 shadow-sm" data-section-id="{{ $section['id'] }}" data-section-type="{{ $section['type'] }}">
                        <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 pb-3 mb-3">
                            <span class="drag-handle cursor-grab select-none text-gray-400" title="Drag">⋮⋮</span>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" class="js-section-visible rounded border-gray-300" {{ !empty($section['visible']) ? 'checked' : '' }}>
                                Visible
                            </label>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-mono uppercase text-gray-600">{{ $section['type'] }}</span>
                        </div>
                        @include('admin.about-page.editors.'.$section['type'], ['data' => $section['data']])
                    </div>
                @endforeach
            </div>
        </div>

        <div class="xl:sticky xl:top-4 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-gray-700">Preview</span>
                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5 text-xs">
                    <button type="button" class="preview-bp rounded-md px-3 py-1.5 font-medium bg-blue-50 text-blue-800" data-bp="desktop">Desktop</button>
                    <button type="button" class="preview-bp rounded-md px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-50" data-bp="tablet">Tablet</button>
                    <button type="button" class="preview-bp rounded-md px-3 py-1.5 font-medium text-gray-600 hover:bg-gray-50" data-bp="mobile">Mobile</button>
                </div>
            </div>
            <div id="preview-shell" class="preview-shell-desktop transition-all duration-200">
                <iframe id="about-preview-frame" title="About preview"></iframe>
            </div>
        </div>
    </div>

    <div class="mt-10 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Publication history</h2>
        <p class="mt-1 text-xs text-gray-500">Each successful publish is stored here. Restoring replaces the live page and updates your draft to match. Actions are recorded in the audit log.</p>
        @if($revisions->isEmpty())
            <p class="mt-4 text-sm text-gray-600">No published snapshots yet. Publish once to start history.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm text-gray-700">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="py-2 pr-4">ID</th>
                            <th class="py-2 pr-4">Saved</th>
                            <th class="py-2 pr-4">Admin</th>
                            <th class="py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revisions as $rev)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4 font-mono text-xs">#{{ $rev->id }}</td>
                                <td class="py-2 pr-4">{{ $rev->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</td>
                                <td class="py-2 pr-4">{{ $rev->user?->email ?? '—' }}</td>
                                <td class="py-2">
                                    <form method="post" action="{{ route('admin.about-page.restore-revision', $rev) }}" class="inline" onsubmit="return confirm('Restore this snapshot as the live About page? Your draft will match it.');">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-blue-700 hover:text-blue-900">Restore</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function() {
    var token = document.querySelector('meta[name="csrf-token"]');
    var csrf = token ? token.getAttribute('content') : '';

    function setNested(root, path, value) {
        var parts = path.split('.');
        var cur = root;
        for (var i = 0; i < parts.length - 1; i++) {
            var p = parts[i];
            var next = parts[i + 1];
            var numericNext = /^\d+$/.test(next);
            if (cur[p] === undefined) {
                cur[p] = numericNext ? [] : {};
            }
            cur = cur[p];
        }
        var last = parts[parts.length - 1];
        cur[last] = value;
    }

    function collectDataFromCard(card) {
        var data = {};
        var fields = card.querySelectorAll('.js-data');
        fields.forEach(function (el) {
            var path = el.getAttribute('data-field');
            if (!path) return;
            if (el.classList.contains('js-boolean')) {
                setNested(data, path, !!el.checked);
                return;
            }
            if (el.type === 'checkbox' && !el.classList.contains('js-boolean')) {
                return;
            }
            setNested(data, path, el.value);
        });
        return data;
    }

    function collectSections() {
        var cards = document.querySelectorAll('#section-editors .section-card');
        return Array.prototype.map.call(cards, function (card) {
            return {
                id: card.getAttribute('data-section-id'),
                type: card.getAttribute('data-section-type'),
                visible: !!card.querySelector('.js-section-visible').checked,
                data: collectDataFromCard(card)
            };
        });
    }

    var previewTimer = null;
    function schedulePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(refreshPreview, 450);
    }

    function refreshPreview() {
        var iframe = document.getElementById('about-preview-frame');
        if (!iframe) return;
        fetch('{{ route('admin.about-page.preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'text/html'
            },
            body: JSON.stringify({ sections: collectSections() })
        }).then(function (r) { return r.text(); }).then(function (html) {
            iframe.srcdoc = html;
        }).catch(function () {
            iframe.srcdoc = '<body style="font-family:sans-serif;padding:1rem;color:#b91c1c">Preview failed.</body>';
        });
    }

    function saveDraft() {
        var btn = document.getElementById('btn-save-draft');
        btn.disabled = true;
        fetch('{{ route('admin.about-page.draft') }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ sections: collectSections() })
        }).then(function (r) { return r.json(); }).then(function () {
            btn.disabled = false;
            alert('Draft saved.');
        }).catch(function () {
            btn.disabled = false;
            alert('Could not save draft.');
        });
    }

    document.getElementById('btn-save-draft').addEventListener('click', saveDraft);

    document.getElementById('section-editors').addEventListener('input', schedulePreview);
    document.getElementById('section-editors').addEventListener('change', schedulePreview);

    document.querySelectorAll('.preview-bp').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var shell = document.getElementById('preview-shell');
            document.querySelectorAll('.preview-bp').forEach(function (b) {
                b.classList.remove('bg-blue-50', 'text-blue-800');
                b.classList.add('text-gray-600');
            });
            btn.classList.add('bg-blue-50', 'text-blue-800');
            btn.classList.remove('text-gray-600');
            shell.classList.remove('preview-shell-desktop', 'preview-shell-tablet', 'preview-shell-mobile');
            var bp = btn.getAttribute('data-bp');
            if (bp === 'tablet') shell.classList.add('preview-shell-tablet');
            else if (bp === 'mobile') shell.classList.add('preview-shell-mobile');
            else shell.classList.add('preview-shell-desktop');
        });
    });

    if (typeof Sortable !== 'undefined') {
        new Sortable(document.getElementById('section-editors'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () { schedulePreview(); }
        });
    }

    function uploadFile(file, onDone) {
        var fd = new FormData();
        fd.append('file', file);
        fetch('{{ route('admin.about-page.upload-image') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: fd
        }).then(function (r) { return r.json(); }).then(onDone).catch(function () { alert('Upload failed.'); });
    }

    document.getElementById('section-editors').addEventListener('change', function (e) {
        var t = e.target;
        if (t.classList.contains('js-hero-upload') && t.files && t.files[0]) {
            uploadFile(t.files[0], function (res) {
                var card = t.closest('.section-card');
                var inp = card.querySelector('[data-field="hero_image_path"]');
                if (inp && res.path) { inp.value = res.path; inp.dispatchEvent(new Event('input', { bubbles: true })); }
                t.value = '';
            });
        }
        if (t.classList.contains('js-gallery-upload') && t.files && t.files[0]) {
            var idx = t.getAttribute('data-slide-index');
            uploadFile(t.files[0], function (res) {
                var card = t.closest('.section-card');
                var inp = card.querySelector('[data-field="slides.' + idx + '.path"]');
                if (inp && res.path) { inp.value = res.path; inp.dispatchEvent(new Event('input', { bubbles: true })); }
                t.value = '';
            });
        }
    });

    refreshPreview();
})();
</script>
@endpush
