<?php
require 'vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('socios_por_tipo_exportar.xls');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "Full Header Row:\n";
echo json_encode($rows[0]) . "\n";
echo "First Data Row:\n";
echo json_encode($rows[1]) . "\n";
