<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockExpiryExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected $reportData;
    protected $filters;
    protected $warehouseName;

    public function __construct($reportData, $filters, $warehouseName)
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
        $this->warehouseName = $warehouseName;
    }

    public function array(): array
    {
        $rows = [];

        // Headers
        $rows[] = ['COMPANY NAME'];
        $rows[] = ['Stock Expiry Report'];
        
        $dateRange = 'All Dates';
        if($this->filters['date_range_type'] === 'this_month') $dateRange = 'Expiring This Month';
        elseif($this->filters['date_range_type'] === 'next_month') $dateRange = 'Expiring Next Month';
        elseif($this->filters['date_range_type'] === 'this_year') $dateRange = 'Expiring This Year';
        elseif(!empty($this->filters['date_from']) && !empty($this->filters['date_to'])){
             $dateRange = \Carbon\Carbon::parse($this->filters['date_from'])->format('d-m-Y') . ' to ' . \Carbon\Carbon::parse($this->filters['date_to'])->format('d-m-Y');
        }

        $rows[] = ["Warehouse: " . $this->warehouseName . " | Expiry Range: " . $dateRange];
        $rows[] = []; // Empty row

        if(empty($this->reportData)) {
            $rows[] = ['No records found for the selected criteria.'];
            return $rows;
        }

        // Table Headers
        $rows[] = ['Item', 'Code', 'Batch No', 'Quantity', 'UOM', 'Expiry Date', 'Days Left', 'Status'];

        foreach($this->reportData as $data) {
             $statusText = 'Valid';
             if ($data['status'] === 'expired') $statusText = 'Expired';
             elseif ($data['status'] === 'nearing_expiry') $statusText = 'Expiring Soon';

             $rows[] = [
                $data['item_name'],
                $data['item_code'],
                $data['batch_no'],
                $data['quantity'],
                $data['uom'],
                $data['exp_date'],
                $data['days_left'],
                $statusText
             ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Global alignment for header
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');
        
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        if(empty($this->reportData)) {
            $sheet->mergeCells('A5:H5');
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            return;
        }

        // Headers Row
        $sheet->getStyle("A5:H5")->getFont()->setBold(true);
        $sheet->getStyle("A5:H5")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6'); // Light gray
        
        // Right align numeric columns
        $sheet->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Qty
        $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Days Left
        
        // Center align date
        $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Highlight Expired/Near Expiry rows
        $row = 6;
        foreach($this->reportData as $data) {
            if ($data['status'] === 'expired') {
                $sheet->getStyle("A{$row}:H{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
            } elseif ($data['status'] === 'nearing_expiry') {
                $sheet->getStyle("A{$row}:H{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFA500')); // Orange/Amber
            }
            $row++;
        }
    }
}
