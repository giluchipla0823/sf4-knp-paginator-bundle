<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PhpSpreadsheetHelper
{
    /**
     * Adjust the size of columns automatically.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public static function setAutoSizeColumns(Worksheet $sheet): void {
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(TRUE);

        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(TRUE);
        }
    }
}