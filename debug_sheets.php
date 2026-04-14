<?php
require 'vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('socios_por_tipo_exportar.xls');
$sheetNames = $spreadsheet->getSheetNames();
echo "Sheet Names: " . json_encode($sheetNames) . "\n";

foreach($sheetNames as $name) {
    $sheet = $spreadsheet->getSheetByName($name);
    $rows = $sheet->toArray();
    echo "Sheet $name - Row 0: " . json_encode($rows[0] ?? []) . "\n";
}
