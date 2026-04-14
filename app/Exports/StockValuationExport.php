<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockValuationExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters)
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $rows = [];

        // Headers
        $rows[] = ['SCIENCE & SURGICAL'];
        $rows[] = ['Stock Valuation Report'];
        
        $method = $this->filters['method'] ?? '-';
        $date = isset($this->filters['date']) ? \Carbon\Carbon::parse($this->filters['date'])->format('d-m-Y') : '-';
        
        $rows[] = ["Method: " . $method . " | Date (Upto): " . $date];
        $rows[] = []; // Empty row

        if(empty($this->reportData)) {
            $rows[] = ['No records found for the selected criteria.'];
            return $rows;
        }

        // Table Headers
        $rows[] = ['Item Code', 'Item Name', 'Warehouse', 'UOM', 'Quantity', 'Rate', 'Total Value'];

        $grandTotal = 0;
        foreach($this->reportData as $data) {
             $grandTotal += $data['value'];

             $rows[] = [
                $data['item_code'],
                $data['item_name'],
                $data['warehouse_name'],
                $data['uom'],
                number_format($data['quantity'], 2, '.', ''),
                number_format($data['rate'], 2, '.', ''),
                number_format($data['value'], 2, '.', '')
             ];
        }
        
        $rows[] = [];
        $rows[] = ['', '', '', '', '', 'Grand Total:', number_format($grandTotal, 2, '.', '')];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Global alignment for header
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        if(empty($this->reportData)) {
            $sheet->mergeCells('A5:G5');
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            return;
        }

        // Table Header
        $sheet->getStyle("A5:G5")->getFont()->setBold(true);
        $sheet->getStyle("A5:G5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6'); // Light gray
        
        // Right align numeric columns
        $sheet->getStyle('E:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Qty, Rate, Value
        
        // Center align UOM
        $sheet->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Grand Total Row
        $lastRow = count($this->reportData) + 7; // 4 header rows + 1 empty + data rows + 1 empty + 1 grand total
        // Wait, rows logic:
        // 0: H1
        // 1: H2
        // 2: H3
        // 3: Empty
        // 4: Table Header (Row 5)
        // 5...: Data
        // N: Empty
        // N+1: Grand Total
        
        // Let's rely on finding the last row
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("F{$highestRow}:G{$highestRow}")->getFont()->setBold(true);
        $sheet->getStyle("G{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    }
}
