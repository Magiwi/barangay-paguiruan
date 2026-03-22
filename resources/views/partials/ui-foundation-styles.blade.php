<style>
    /* ========================================================
       DESIGN TOKENS — Barangay Paguiruan e-Governance System
       Government-grade unified design system.
       ======================================================== */
    :root {
        /* Brand (Green — Government identity) */
        --brand-700: #2E7D32;
        --brand-600: #388E3C;
        --brand-400: #66BB6A;
        --brand-100: #E8F5E9;

        /* Accent (Yellow — Highlight / Pending) */
        --accent-600: #FBC02D;
        --accent-500: #FDD835;
        --accent-300: #FFF176;
        --accent-100: #FFF8E1;

        /* Info (Blue — Informational / Secondary) */
        --info-600: #29B6F6;
        --info-500: #4FC3F7;
        --info-300: #81D4FA;
        --info-100: #E1F5FE;

        /* Neutrals */
        --text-900: #212121;
        --text-700: #424242;
        --text-600: #616161;
        --bg-app: #F5F5F5;
        --bg-surface: #FFFFFF;
        --border: #E0E0E0;
        --border-strong: #BDBDBD;

        /* Status */
        --success-600: #43A047;
        --success-100: #E8F5E9;
        --warning-600: #FBC02D;
        --warning-100: #FFF8E1;
        --danger-600: #E53935;
        --danger-100: #FFEBEE;
        --muted-600: #757575;

        /* UI Shadows & Radius */
        --shadow-card: 0 1px 2px rgba(0, 0, 0, 0.08);
        --shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.10);
        --radius-sm: 8px;
        --radius-md: 10px;
        --radius-lg: 12px;

        /* Semantic Aliases (used by utility classes) */
        --ui-bg: var(--bg-app);
        --ui-surface: var(--bg-surface);
        --ui-border: var(--border);
        --ui-border-strong: var(--border-strong);
        --ui-text: var(--text-900);
        --ui-text-muted: var(--text-600);
        --ui-primary: var(--brand-700);
        --ui-primary-hover: var(--brand-600);
        --ui-primary-soft: var(--brand-100);
        --ui-accent: var(--accent-600);
        --ui-info: var(--info-600);
        --ui-info-soft: var(--info-100);
    }

    /* ========================================================
       UTILITY CLASSES — Cards
       ======================================================== */
    .ui-surface-card {
        border: 1px solid var(--ui-border);
        background: var(--ui-surface);
        border-radius: 1rem;
        box-shadow: var(--shadow-card);
    }

    .ui-surface-card-hover {
        transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }
    .ui-surface-card-hover:hover {
        border-color: var(--ui-border-strong);
        box-shadow: var(--shadow-hover);
        transform: translateY(-1px);
    }

    /* ========================================================
       UTILITY CLASSES — KPI / Metric
       ======================================================== */
    .ui-icon-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 2.5rem;
        width: 2.5rem;
        border-radius: 0.75rem;
        background: var(--brand-100);
        color: var(--brand-700);
    }

    .ui-kpi-label {
        font-size: 0.72rem;
        line-height: 1rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: var(--ui-text-muted);
    }

    .ui-kpi-value {
        margin-top: 0.2rem;
        font-size: 1.55rem;
        line-height: 1.9rem;
        font-weight: 700;
        color: var(--ui-text);
    }

    /* ========================================================
       UTILITY CLASSES — Focus
       ======================================================== */
    .ui-focus-ring:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px var(--ui-primary-soft);
    }

    /* ========================================================
       UTILITY CLASSES — Buttons
       ======================================================== */
    .ui-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        line-height: 1.25rem;
        padding: 0.5rem 1rem;
        border-radius: var(--radius-sm);
        transition: background-color 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        cursor: pointer;
        border: none;
    }
    .ui-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .ui-btn-primary {
        background-color: var(--brand-700);
        color: #fff;
    }
    .ui-btn-primary:hover:not(:disabled) {
        background-color: var(--brand-600);
    }
    .ui-btn-primary:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px var(--brand-100);
    }

    .ui-btn-secondary {
        background-color: var(--ui-surface);
        color: var(--text-700);
        border: 1px solid var(--ui-border);
    }
    .ui-btn-secondary:hover:not(:disabled) {
        background-color: var(--bg-app);
        border-color: var(--ui-border-strong);
    }

    .ui-btn-danger {
        background-color: var(--danger-600);
        color: #fff;
    }
    .ui-btn-danger:hover:not(:disabled) {
        background-color: #C62828;
    }
    .ui-btn-danger:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px var(--danger-100);
    }

    .ui-btn-info {
        background-color: var(--info-600);
        color: #fff;
    }
    .ui-btn-info:hover:not(:disabled) {
        background-color: #0288D1;
    }

    .ui-btn-ghost {
        background: transparent;
        color: var(--text-700);
    }
    .ui-btn-ghost:hover:not(:disabled) {
        background-color: var(--bg-app);
    }

    .ui-btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        line-height: 1rem;
    }
    .ui-btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.6875rem;
        line-height: 0.875rem;
    }
    .ui-btn-lg {
        padding: 0.625rem 1.5rem;
        font-size: 0.9375rem;
    }

    /* ========================================================
       UTILITY CLASSES — Badges
       ======================================================== */
    .ui-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        font-weight: 500;
        font-size: 0.75rem;
        line-height: 1rem;
        padding: 0.125rem 0.625rem;
        white-space: nowrap;
    }
    .ui-badge-success {
        background-color: var(--success-100);
        color: var(--success-600);
    }
    .ui-badge-warning {
        background-color: var(--warning-100);
        color: #F57F17;
    }
    .ui-badge-danger {
        background-color: var(--danger-100);
        color: var(--danger-600);
    }
    .ui-badge-info {
        background-color: var(--info-100);
        color: #0277BD;
    }
    .ui-badge-muted {
        background-color: #F5F5F5;
        color: var(--muted-600);
    }

    /* ========================================================
       UTILITY CLASSES — Links
       ======================================================== */
    .ui-link {
        color: var(--brand-700);
        text-decoration: none;
        font-weight: 500;
    }
    .ui-link:hover {
        color: var(--brand-600);
        text-decoration: underline;
    }

    /* ========================================================
       UTILITY CLASSES — Sidebar navigation
       ======================================================== */
    .ui-nav-active {
        background-color: var(--brand-100) !important;
        color: var(--brand-700) !important;
        border-left: 3px solid var(--brand-700);
        padding-left: 9px;
    }
    .ui-nav-active svg {
        color: var(--brand-700) !important;
    }
    .ui-nav-sub-active {
        color: var(--brand-700) !important;
        font-weight: 600;
    }

    /* e-Blotter keeps red per government severity semantics */
    .ui-nav-blotter-active {
        background-color: #FFEBEE !important;
        color: #C62828 !important;
        border-left: 3px solid var(--danger-600);
        padding-left: 9px;
    }
    .ui-nav-blotter-active svg {
        color: var(--danger-600) !important;
    }
    .ui-nav-blotter-sub-active {
        color: #C62828 !important;
        font-weight: 600;
    }

    /* ========================================================
       UTILITY CLASSES — Avatar & Position
       ======================================================== */
    .ui-avatar-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background-color: var(--brand-100);
        color: var(--brand-700);
        font-weight: 700;
        font-size: 0.875rem;
    }
    .ui-position-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        background-color: var(--brand-100);
        color: var(--brand-700);
        font-weight: 600;
        font-size: 0.75rem;
        line-height: 1rem;
        padding: 0.25rem 0.625rem;
        box-shadow: inset 0 0 0 1px rgba(46, 125, 50, 0.2);
    }

    /* ========================================================
       UTILITY CLASSES — Resident top-nav active
       ======================================================== */
    .ui-topnav-active {
        color: var(--brand-700) !important;
    }
    .ui-topnav-underline {
        background-color: var(--brand-700);
    }
</style>
