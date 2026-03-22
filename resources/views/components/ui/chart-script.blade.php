@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            window.reportCreateChart = window.reportCreateChart || function (canvasId, config) {
                if (typeof window.Chart === 'undefined') {
                    return null;
                }

                const canvas = document.getElementById(canvasId);
                if (!canvas) {
                    return null;
                }

                return new window.Chart(canvas, config);
            };
        </script>
    @endpush
@endonce

