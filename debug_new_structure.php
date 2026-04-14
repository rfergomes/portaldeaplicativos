<?php
require 'vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('socios_por_tipo_exportar.xls');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "Rows 0 to 2:\n";
for($i=0; $i<3; $i++) {
    echo "Row $i: " . json_encode($rows[$i]) . "\n";
}
