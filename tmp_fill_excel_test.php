<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$input = __DIR__ . '/storage/app/private/document_templates/CRM_Báo giá_08.04.2026_10.50.57_EICL86QK_.xls';
$output = __DIR__ . '/storage/app/private/document_templates/templates/CRM_Bao-gia-filled-test.xlsx';

$map = [
    '{{QuoteCode}}' => 'BG-TEST-0001',
    '{{SalesOrderCode}}' => 'SO-TEST-0001',
    '{{CustomerName}}' => 'CÔNG TY TNHH ABC',
    '{{TaxCode}}' => '0312345678',
    '{{Address}}' => '151-155 Bến Vân Đồn, TP.HCM',
    '{{ContactPerson}}' => 'Nguyễn Văn A',
    '{{Phone}}' => '0909 123 456',
    '{{Email}}' => 'abc@example.com',
    '{{Date}}' => date('d/m/Y'),
    '{{SubTotal}}' => '12.500.000',
    '{{VatPercent}}' => '8',
    '{{VatAmount}}' => '1.000.000',
    '{{DiscountPercent}}' => '0',
    '{{TotalAmount}}' => '13.500.000',
    '{{Item.No}}' => '1',
    '{{Item.Name}}' => 'Máy chấm công mẫu',
    '{{Item.Unit}}' => 'Cái',
    '{{Item.Quantity}}' => '2',
    '{{Item.UnitPrice}}' => '6.250.000',
    '{{Item.LineTotal}}' => '12.500.000',
    '{{#Items}}' => '',
    '{{/Items}}' => '',
];

$spreadsheet = IOFactory::load($input);

$found = [];
foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    for ($row = 1; $row <= $highestRow; $row++) {
        $range = 'A' . $row . ':' . $highestColumn . $row;
        $cells = $sheet->rangeToArray($range, null, true, true, true);
        foreach (($cells[$row] ?? []) as $col => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            if (preg_match_all('/\{\{[^\}]+\}\}/', $value, $m)) {
                foreach ($m[0] as $token) {
                    $found[$token] = true;
                }
            }
            $newValue = strtr($value, $map);
            if ($newValue !== $value) {
                $sheet->setCellValue($col . $row, $newValue);
            }
        }
    }
}

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($output);

echo "INPUT: $input\n";
echo "OUTPUT: $output\n";
echo "TOKENS_FOUND: " . implode(', ', array_keys($found)) . "\n";
