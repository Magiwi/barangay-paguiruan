<style>
    .phone-fold { display: none; }
    .phone-only { display: none; }

    @media (max-width: 767px) {
        .phone-only { display: inline; }
        .phone-fold { display: block; }

        .overflow-x-auto:has(> table.phone-stack),
        .overflow-x-auto:has(> form > table.phone-stack) {
            overflow-x: visible;
        }

        table.phone-stack {
            display: block;
            width: 100%;
            min-width: 0;
        }

        table.phone-stack > thead { display: none; }

        table.phone-stack > tbody {
            display: block;
        }

        table.phone-stack > tbody > tr {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        table.phone-stack > tbody > tr > td {
            display: block;
            width: 100%;
            max-width: 100%;
            padding: 0.1rem 0;
            white-space: normal !important;
            text-align: left !important;
            overflow-wrap: anywhere;
        }

        table.phone-stack > tbody > tr > td.phone-desktop-only {
            display: none !important;
        }

        table.phone-stack > tbody > tr > td[data-label]::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 0.15rem;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b7280;
        }

        table.phone-stack > tbody > tr > td.phone-title::before {
            display: none;
        }

        table.phone-stack form,
        table.phone-stack td .flex,
        table.phone-stack td .inline-flex {
            flex-wrap: wrap;
            max-width: 100%;
        }

        table.phone-stack input[type="text"],
        table.phone-stack input[type="search"],
        table.phone-stack select,
        table.phone-stack textarea {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }
    }

    @media print {
        table.phone-stack { display: table !important; width: 100% !important; min-width: 0 !important; }
        table.phone-stack > thead { display: table-header-group !important; }
        table.phone-stack > tbody { display: table-row-group !important; }
        table.phone-stack > tbody > tr { display: table-row !important; }
        table.phone-stack > tbody > tr > td,
        table.phone-stack > tbody > tr > td.phone-desktop-only {
            display: table-cell !important;
            width: auto !important;
            max-width: none !important;
            white-space: normal !important;
        }
        .phone-fold, .phone-only { display: none !important; }
    }
</style>
