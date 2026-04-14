<?php
require 'vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('socios_por_tipo_exportar.xls');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "Header (first 15): " . json_encode(array_slice($rows[0], 0, 15)) . "\n";
echo "Data (first 15): " . json_encode(array_slice($rows[1], 0, 15)) . "\n";
