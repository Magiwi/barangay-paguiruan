@props([
    'pdfUrl' => null,
    'excelUrl' => null,
    'printUrl' => null,
    'filterLabel' => null,
    'filterValue' => null,
    'showPrint' => false,
    'printButtonClass' => 'ui-btn ui-btn-primary ui-btn-sm',
])

<div class="no-print" data-export-toolbar>
    <div class="export-toast hidden fixed right-6 top-6 z-50 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm text-gray-800 shadow-lg">
        Preparing export file...
    </div>

    <div class="flex flex-wrap items-center justify-end gap-2">
        @if ($pdfUrl)
            <a href="{{ $pdfUrl }}" data-export-link
               class="ui-focus-ring inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Export PDF
            </a>
        @endif

        @if ($excelUrl)
            <a href="{{ $excelUrl }}" data-export-link
               class="ui-focus-ring inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Export Excel
            </a>
        @endif

        @if ($printUrl)
            <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="{{ $printButtonClass }}">
                Print Preview
            </a>
        @elseif ($showPrint)
            <button type="button" onclick="window.print()" class="{{ $printButtonClass }}">
                Print
            </button>
        @endif

        @if ($filterLabel && $filterValue !== null)
            <p class="w-full text-right text-xs text-gray-500">
                {{ $filterLabel }}: <span class="font-medium text-gray-700">{{ $filterValue }}</span>
            </p>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toolbars = document.querySelectorAll('[data-export-toolbar]');

                toolbars.forEach(function (toolbar) {
                    const toast = toolbar.querySelector('.export-toast');
                    const links = toolbar.querySelectorAll('[data-export-link]');

                    links.forEach(function (link) {
                        link.addEventListener('click', function () {
                            if (toast) {
                                toast.classList.remove('hidden');
                                setTimeout(function () {
                                    toast.classList.add('hidden');
                                }, 4000);
                            }

                            link.classList.add('opacity-70', 'pointer-events-none');
                            const original = link.textContent;
                            link.textContent = 'Preparing...';

                            setTimeout(function () {
                                link.textContent = original;
                                link.classList.remove('opacity-70', 'pointer-events-none');
                            }, 4000);
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce

