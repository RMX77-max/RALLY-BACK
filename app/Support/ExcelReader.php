<?php
// app/Support/ExcelReader.php
namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReader
{
    /**
     * Devuelve:
     *  - columnas: headers (primera fila) o Col_1, Col_2...
     *  - filas: array de objetos asociativos
     *  - total_filas (sin contar encabezado)
     */
    public static function readAsDisplayed(string $fullPath, bool $firstRowAsHeader = true): array
    {
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(false); // importante para getFormattedValue()
        $spreadsheet = $reader->load($fullPath);
        $sheet = $spreadsheet->getSheet(0);

        $highestRow    = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestIndex  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $rowsRaw = [];
        for ($row = 1; $row <= $highestRow; $row++) {
            $current = [];
            for ($col = 1; $col <= $highestIndex; $col++) {
                $cell  = $sheet->getCellByColumnAndRow($col, $row);
                $value = $cell->getFormattedValue(); // “como se ve” en Excel
                $current[] = is_null($value) ? '' : (string)$value;
            }
            $rowsRaw[] = $current;
        }

        $columnas = [];
        $filas = [];

        if ($firstRowAsHeader && count($rowsRaw) > 0) {
            $headers = array_map(fn($h) => $h === '' ? 'Col_' . uniqid() : (string)$h, $rowsRaw[0]);
            $columnas = $headers;

            for ($i = 1; $i < count($rowsRaw); $i++) {
                $rowAssoc = [];
                foreach ($headers as $idx => $header) {
                    $rowAssoc[$header] = $rowsRaw[$i][$idx] ?? '';
                }
                $filas[] = $rowAssoc;
            }
        } else {
            if (count($rowsRaw) > 0) {
                $maxCols = max(array_map('count', $rowsRaw));
                for ($i = 1; $i <= $maxCols; $i++) $columnas[] = "Col_$i";
                foreach ($rowsRaw as $r) {
                    $rowAssoc = [];
                    foreach ($columnas as $idx => $header) {
                        $rowAssoc[$header] = $r[$idx] ?? '';
                    }
                    $filas[] = $rowAssoc;
                }
            }
        }

        return [
            'columnas'    => $columnas,
            'filas'       => $filas,
            'total_filas' => max(0, count($filas)),
        ];
    }
}
