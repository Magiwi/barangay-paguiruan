<style>
    @page {
        size: A4;
        margin: 10mm;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
        color: #1f2937;
        font-size: 9.6px;
        line-height: 1.28;
    }
    .report-sheet {
        width: 100%;
        background: #ffffff;
        padding: 12px 14px;
    }
    .report-header {
        position: relative;
        text-align: center;
        margin-bottom: 7px;
        padding-bottom: 5px;
    }
    .header-top {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .header-left-logo,
    .header-office,
    .header-right-logos {
        display: table-cell;
        vertical-align: top;
    }
    .header-left-logo {
        width: 76px;
        text-align: left;
    }
    .header-left-logo img {
        width: 64px;
        height: 64px;
        object-fit: contain;
    }
    .header-office {
        text-align: center;
        padding: 0 12px;
    }
    .header-right-logos {
        width: 150px;
        text-align: right;
        white-space: nowrap;
    }
    .header-right-logos img {
        width: 64px;
        height: 64px;
        object-fit: contain;
        margin-left: 4px;
    }
    .header-divider {
        border-bottom: 1px solid #d3dae4;
        margin-top: 4px;
    }
    .office-line-1 {
        font-size: 8px;
        color: #4b5563;
    }
    .office-line-2 {
        margin-top: 1px;
        font-size: 13.4px;
        font-weight: 700;
        letter-spacing: 0.1px;
        font-family: Georgia, 'Times New Roman', serif;
    }
    .office-line-3,
    .office-line-4 {
        margin-top: 1px;
        font-size: 8px; 
        color: #4b5563;
    }
    .report-title {
        margin-top: 6px;
        letter-spacing: 3px;
        font-size: 16.4px;
        font-weight: 700;
        color: #617589;
        text-transform: uppercase;
        line-height: 1.1;
    }
    .report-meta {
        margin-top: 5px;
        font-size: 8.5px;
    }
    .report-meta-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-meta-left,
    .report-meta-right {
        width: 50%;
        vertical-align: top;
    }
    .report-meta-left {
        text-align: left;
    }
    .report-meta-right {
        text-align: right;
    }
    .report-meta-item {
        margin: 0.8px 0;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 7px;
        table-layout: fixed;
    }
    .report-table th,
    .report-table td {
        border: 1px solid #d2d7df;
        padding: 2.8px 4px;
        text-align: left;
        vertical-align: top;
        word-wrap: break-word;
    }
    .report-table th {
        background: #e4e9f0;
        font-size: 8.8px;
        font-weight: 700;
        color: #374151;
    }
    .empty-cell {
        text-align: center;
        color: #6b7280;
        font-style: italic;
        padding: 12px 8px;
    }
    .filter-chips {
        margin-top: 6px;
        text-align: left;
    }
    .filter-chip {
        display: inline-block;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 2px 7px;
        margin: 0 3px 3px 0;
        font-size: 8px;
        color: #374151;
        background: #f8fafc;
    }
</style>
