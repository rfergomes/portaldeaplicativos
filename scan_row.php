<?php
require 'vendor/autoload.php';

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('socios_por_tipo_exportar.xls');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();

echo "Scanning Row 1 for data:\n";
foreach($rows[1] as $idx => $val) {
    if ($val !== null && $val !== '') {
        echo "Index $idx: [$val]\n";
    }
}
