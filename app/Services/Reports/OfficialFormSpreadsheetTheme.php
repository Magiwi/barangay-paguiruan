<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OfficialFormSpreadsheetTheme
{
    public function applyOfficialHeader(
        Worksheet $sheet,
        string $reportTitle,
        string $scope,
        string $lastColumn,
        array $leftMeta = [],
        array $rightMeta = []
    ): int {
        $sheet->getDefaultRowDimension()->setRowHeight(16);
        $sheet->setShowGridlines(false);
        $sheet->getSheetView()->setZoomScale(115);

        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
        $centerStart = 'B';
        $centerEnd = Coordinate::stringFromColumnIndex(max(2, $lastColumnIndex - 2));

        $sheet->mergeCells($centerStart . '1:' . $centerEnd . '1');
        $sheet->setCellValue($centerStart . '1', 'Republic of the Philippines');
        $sheet->mergeCells($centerStart . '2:' . $centerEnd . '2');
        $sheet->setCellValue($centerStart . '2', 'Office of the Barangay Chairman');
        $sheet->mergeCells($centerStart . '3:' . $centerEnd . '3');
        $sheet->setCellValue($centerStart . '3', 'Barangay Paguiruan, Floridablanca');
        $sheet->mergeCells($centerStart . '4:' . $centerEnd . '4');
        $sheet->setCellValue($centerStart . '4', 'Province of Pampanga');

        $this->attachLogos($sheet, $lastColumnIndex);

        $sheet->mergeCells('A6:' . $lastColumn . '6');
        $sheet->setCellValue('A6', strtoupper($reportTitle));

        $normalizedLeftMeta = array_merge(
            [
                'Report Scope' => $scope,
                'Generated' => now()->format('M d, Y h:i A'),
            ],
            $leftMeta
        );
        $normalizedRightMeta = $rightMeta;

        $midIndex = max(2, (int) floor($lastColumnIndex / 2));
        $leftEnd = Coordinate::stringFromColumnIndex($midIndex);
        $rightStart = Coordinate::stringFromColumnIndex(min($lastColumnIndex, $midIndex + 1));

        $this->fillMetaBlock($sheet, 'A', $leftEnd, 8, $normalizedLeftMeta, Alignment::HORIZONTAL_LEFT);
        if ($rightStart !== 'A' && $rightStart !== $leftEnd) {
            $this->fillMetaBlock($sheet, $rightStart, $lastColumn, 8, $normalizedRightMeta, Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getStyle($centerStart . '1:' . $centerEnd . '1')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '4B5563']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle($centerStart . '2:' . $centerEnd . '2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'name' => 'Cambria', 'color' => ['rgb' => '1F2937']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle($centerStart . '3:' . $centerEnd . '4')->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '4B5563']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A1:' . $lastColumn . '4')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D3DAE4'],
                ],
            ],
        ]);
        $sheet->getStyle('A6:' . $lastColumn . '6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 19, 'color' => ['rgb' => '617589']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(30);

        return 13;
    }

    public function styleTable(Worksheet $sheet, int $headerRow, string $lastColumn, int $lastDataRow): void
    {
        $headerRange = 'A' . $headerRow . ':' . $lastColumn . $headerRow;
        $dataRange = 'A' . $headerRow . ':' . $lastColumn . $lastDataRow;

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '374151']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E4E9F0'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D2D7DF'],
                ],
            ],
        ]);

        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D2D7DF'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => true,
            ],
        ]);

        if ($lastDataRow > $headerRow + 1) {
            for ($row = $headerRow + 1; $row <= $lastDataRow; $row++) {
                if ($row % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FAFCFF'],
                        ],
                    ]);
                }
            }
        }
    }

    public function applySignatureFooter(
        Worksheet $sheet,
        string $lastColumn,
        int $lastDataRow,
        string $preparedByLabel = 'Reports Officer',
        string $notedByLabel = 'Barangay Chairman'
    ): int {
        $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
        $midIndex = max(2, (int) floor($lastColumnIndex / 2));
        $leftEnd = Coordinate::stringFromColumnIndex(max(2, $midIndex - 1));
        $rightStart = Coordinate::stringFromColumnIndex(min($lastColumnIndex, $midIndex + 1));

        $labelRow = $lastDataRow + 3;
        $lineRow = $labelRow + 1;
        $titleRow = $labelRow + 2;

        $sheet->mergeCells('A' . $labelRow . ':' . $leftEnd . $labelRow);
        $sheet->setCellValue('A' . $labelRow, 'Prepared by:');
        $sheet->mergeCells($rightStart . $labelRow . ':' . $lastColumn . $labelRow);
        $sheet->setCellValue($rightStart . $labelRow, 'Noted by:');

        $sheet->mergeCells('A' . $lineRow . ':' . $leftEnd . $lineRow);
        $sheet->setCellValue('A' . $lineRow, '____________________________');
        $sheet->mergeCells($rightStart . $lineRow . ':' . $lastColumn . $lineRow);
        $sheet->setCellValue($rightStart . $lineRow, '____________________________');

        $sheet->mergeCells('A' . $titleRow . ':' . $leftEnd . $titleRow);
        $sheet->setCellValue('A' . $titleRow, $preparedByLabel);
        $sheet->mergeCells($rightStart . $titleRow . ':' . $lastColumn . $titleRow);
        $sheet->setCellValue($rightStart . $titleRow, $notedByLabel);

        $sheet->getStyle('A' . $labelRow . ':' . $lastColumn . $titleRow)->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '374151']],
        ]);
        $sheet->getStyle('A' . $lineRow . ':' . $lastColumn . $lineRow)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A' . $titleRow . ':' . $lastColumn . $titleRow)->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '6B7280']],
        ]);

        return $titleRow;
    }

    public function applyPageSetup(Worksheet $sheet, string $lastColumn, int $lastRow, int $headerRow): void
    {
        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->setSelectedCell('A1');
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
        $sheet->getPageMargins()->setTop(0.35);
        $sheet->getPageMargins()->setBottom(0.35);
        $sheet->getPageMargins()->setLeft(0.3);
        $sheet->getPageMargins()->setRight(0.3);
        $sheet->getPageSetup()->setPrintArea('A1:' . $lastColumn . $lastRow);
        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);
    }

    private function attachLogos(Worksheet $sheet, int $lastColumnIndex): void
    {
        $logo1Path = public_path('images/logo1.png');
        if (file_exists($logo1Path)) {
            $leftLogo = new Drawing();
            $leftLogo->setName('Barangay Logo');
            $leftLogo->setPath($logo1Path);
            $leftLogo->setCoordinates('A1');
            $leftLogo->setOffsetX(8);
            $leftLogo->setOffsetY(2);
            $leftLogo->setHeight(60);
            $leftLogo->setWorksheet($sheet);
        }

        $rightOneColumn = Coordinate::stringFromColumnIndex(max(2, $lastColumnIndex - 1));
        $rightTwoColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);

        $logo2Path = public_path('images/logo2.png');
        if (file_exists($logo2Path)) {
            $rightOne = new Drawing();
            $rightOne->setName('Municipal Seal');
            $rightOne->setPath($logo2Path);
            $rightOne->setCoordinates($rightOneColumn . '1');
            $rightOne->setOffsetX(6);
            $rightOne->setOffsetY(2);
            $rightOne->setHeight(60);
            $rightOne->setWorksheet($sheet);
        }

        $logo3Path = public_path('images/logo3.png');
        if (file_exists($logo3Path)) {
            $rightTwo = new Drawing();
            $rightTwo->setName('Provincial Seal');
            $rightTwo->setPath($logo3Path);
            $rightTwo->setCoordinates($rightTwoColumn . '1');
            $rightTwo->setOffsetX(2);
            $rightTwo->setOffsetY(2);
            $rightTwo->setHeight(60);
            $rightTwo->setWorksheet($sheet);
        }
    }

    private function fillMetaBlock(
        Worksheet $sheet,
        string $startColumn,
        string $endColumn,
        int $startRow,
        array $meta,
        string $alignment
    ): void {
        $row = $startRow;
        foreach ($meta as $label => $value) {
            $sheet->mergeCells($startColumn . $row . ':' . $endColumn . $row);
            $sheet->setCellValue($startColumn . $row, $label . ': ' . (string) $value);
            $row++;
        }

        $endRow = max($startRow, $row - 1);
        $sheet->getStyle($startColumn . $startRow . ':' . $endColumn . $endRow)->applyFromArray([
            'font' => ['size' => 10, 'color' => ['rgb' => '374151']],
            'alignment' => [
                'horizontal' => $alignment,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DCE3EC'],
                ],
            ],
        ]);
    }
}
