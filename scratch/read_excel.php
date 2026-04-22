<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load(__DIR__ . '/../relmensalidadespgedeb.xls');
$worksheet = $spreadsheet->getActiveSheet();
$firstRow = $worksheet->rangeToArray('A1:Z1', null, true, false)[0];
print_r(array_filter($firstRow));
