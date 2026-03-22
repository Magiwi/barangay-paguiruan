<?php

namespace App\Services\Reports;

class ReportCsvFormatter
{
    /**
     * @param resource $handle
     * @param iterable<mixed> $rows
     */
    public function writeOfficialCsv(
        $handle,
        string $reportTitle,
        array $metaRows,
        array $headers,
        iterable $rows,
        callable $rowMapper
    ): void {
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['Republic of the Philippines']);
        fputcsv($handle, ['Office of the Barangay Chairman']);
        fputcsv($handle, ['Barangay Paguiruan, Floridablanca']);
        fputcsv($handle, ['Province of Pampanga']);
        fputcsv($handle, ['------------------------------------------------------------']);
        fputcsv($handle, [strtoupper($reportTitle)]);

        foreach ($metaRows as $metaRow) {
            if (is_array($metaRow)) {
                fputcsv($handle, $metaRow);
            } else {
                fputcsv($handle, [(string) $metaRow]);
            }
        }

        fputcsv($handle, []);
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            $mapped = $rowMapper($row);
            fputcsv($handle, is_array($mapped) ? $mapped : [(string) $mapped]);
        }
    }
}
