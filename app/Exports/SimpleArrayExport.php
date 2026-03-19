<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SimpleArrayExport implements FromArray, WithHeadings, WithStyles
{
    protected $data;
    protected $headings;

    public function __construct(array $data)
    {
        $this->headings = array_shift($data); // Lấy dòng đầu làm heading
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        // Header: đậm, căn giữa, nền vàng nhạt
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => [ 'bold' => true ],
            'alignment' => [ 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [ 'rgb' => 'FFF9C4' ] // vàng nhạt
            ]
        ]);
        // Border cho toàn bộ bảng
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF888888'],
                ],
            ],
        ]);
        // Căn trái dữ liệu
        $sheet->getStyle('A2:' . $highestColumn . $highestRow)
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        // Tự động xuống dòng cho toàn bộ sheet
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)
            ->getAlignment()->setWrapText(true);
        // Tự động co giãn cột
        foreach (range('A', $highestColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        return [];
    }
} 