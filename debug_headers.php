<?php
require 'vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('socios_por_tipo_exportar.xls');
$worksheet = $spreadsheet->getActiveSheet();
$highestColumn = $worksheet->getHighestColumn();
$headerRange = 'A1:' . $highestColumn . '1';
$headerRows = $worksheet->rangeToArray($headerRange, null, true, true, false);

echo "Headers:\n";
print_r($headerRows[0]);
