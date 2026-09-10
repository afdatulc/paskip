<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('storage/app/templates/template_fra.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$data = $worksheet->toArray(null, true, true, true);
print_r(array_slice($data, 0, 10));
