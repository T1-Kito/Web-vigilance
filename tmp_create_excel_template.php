<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('BaoGiaTemplate');

// Header
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'MẪU EXCEL PLACEHOLDER - BÁO GIÁ');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A3', 'Mã báo giá:');
$sheet->setCellValue('B3', '{{QuoteCode}}');
$sheet->setCellValue('D3', 'Ngày:');
$sheet->setCellValue('E3', '{{Date}}');

$sheet->setCellValue('A4', 'Khách hàng:');
$sheet->setCellValue('B4', '{{CustomerName}}');
$sheet->setCellValue('D4', 'MST:');
$sheet->setCellValue('E4', '{{TaxCode}}');

$sheet->setCellValue('A5', 'Địa chỉ:');
$sheet->setCellValue('B5', '{{Address}}');
$sheet->setCellValue('D5', 'Liên hệ:');
$sheet->setCellValue('E5', '{{ContactPerson}}');

$sheet->setCellValue('A6', 'Điện thoại:');
$sheet->setCellValue('B6', '{{Phone}}');
$sheet->setCellValue('D6', 'Email:');
$sheet->setCellValue('E6', '{{Email}}');

// Table header
$sheet->setCellValue('A8', '{{#Items}}');
$sheet->setCellValue('A9', 'STT');
$sheet->setCellValue('B9', 'Tên hàng hóa');
$sheet->setCellValue('C9', 'Đơn vị');
$sheet->setCellValue('D9', 'Số lượng');
$sheet->setCellValue('E9', 'Đơn giá');
$sheet->setCellValue('F9', 'Thành tiền');
$sheet->getStyle('A9:F9')->getFont()->setBold(true);
$sheet->getStyle('A9:F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Item row template
$sheet->setCellValue('A10', '{{Item.No}}');
$sheet->setCellValue('B10', '{{Item.Name}}');
$sheet->setCellValue('C10', '{{Item.Unit}}');
$sheet->setCellValue('D10', '{{Item.Quantity}}');
$sheet->setCellValue('E10', '{{Item.UnitPrice}}');
$sheet->setCellValue('F10', '{{Item.LineTotal}}');
$sheet->setCellValue('A11', '{{/Items}}');

// Summary
$sheet->setCellValue('D13', 'Tạm tính');
$sheet->setCellValue('F13', '{{SubTotal}}');
$sheet->setCellValue('D14', 'Chiết khấu (%)');
$sheet->setCellValue('E14', '{{DiscountPercent}}');
$sheet->setCellValue('D15', 'VAT (%)');
$sheet->setCellValue('E15', '{{VatPercent}}');
$sheet->setCellValue('F15', '{{VatAmount}}');
$sheet->setCellValue('D16', 'Tổng cộng');
$sheet->setCellValue('F16', '{{TotalAmount}}');
$sheet->getStyle('D16:F16')->getFont()->setBold(true);

// Styling borders
$sheet->getStyle('A9:F10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('D13:F16')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Widths
$sheet->getColumnDimension('A')->setWidth(8);
$sheet->getColumnDimension('B')->setWidth(38);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(12);
$sheet->getColumnDimension('E')->setWidth(16);
$sheet->getColumnDimension('F')->setWidth(18);

$output = __DIR__ . '/storage/app/private/document_templates/templates/excel-template-placeholder-mau.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($output);

echo $output . PHP_EOL;
