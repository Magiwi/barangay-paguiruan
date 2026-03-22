<style>
    :root {
        --ui-bg: #f8fafc;
        --ui-surface: #ffffff;
        --ui-border: #e5e7eb;
        --ui-text: #111827;
        --ui-muted: #6b7280;
        --ui-primary: #2563eb;
        --ui-primary-soft: #dbeafe;
        --ui-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        --ui-shadow-hover: 0 10px 15px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -4px rgba(15, 23, 42, 0.08);
        --ui-radius: 1rem;
    }

    [x-cloak] { display: none !important; }

    body {
        color: var(--ui-text);
        background-color: var(--ui-bg);
    }

    ::selection {
        background: var(--ui-primary-soft);
        color: #1e3a8a;
    }

    a, button {
        transition: color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .ui-card {
        border: 1px solid var(--ui-border);
        border-radius: var(--ui-radius);
        background: var(--ui-surface);
        box-shadow: var(--ui-shadow);
    }

    .ui-card-hover:hover {
        box-shadow: var(--ui-shadow-hover);
        transform: translateY(-1px);
    }

    .ui-muted-title {
        font-size: 0.75rem;
        line-height: 1rem;
        letter-spacing: 0.08em;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--ui-muted);
    }

    .ui-focus-ring:focus-visible,
    a:focus-visible,
    button:focus-visible,
    summary:focus-visible,
    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible {
        outline: 2px solid transparent;
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px rgba(37, 99, 235, 0.35);
        border-radius: 0.5rem;
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
