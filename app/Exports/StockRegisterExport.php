<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockRegisterExport implements FromArray, ShouldAutoSize, WithStyles
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
        $rows[] = ['Stock Register'];
        
        $dateRange = '-';
        if(!empty($this->filters['date_from']) && !empty($this->filters['date_to'])){
             $dateRange = \Carbon\Carbon::parse($this->filters['date_from'])->format('d-m-Y') . ' to ' . \Carbon\Carbon::parse($this->filters['date_to'])->format('d-m-Y');
        }
        $rows[] = ["Warehouse: " . $this->warehouseName . " | Date Range: " . $dateRange];
        $rows[] = []; // Empty row

        if(empty($this->reportData)) {
            $rows[] = ['No records found for the selected criteria.'];
            return $rows;
        }

        foreach($this->reportData as $data) {
            $item = $data['item'];
            $itemName = $item['name'];
            if(isset($item['uom']) && !empty($item['uom'])) {
                $itemName .= ' (' . $item['uom']['name'] . ')';
            }
            $itemName .= ' - Code: ' . ($item['code'] ?? 'N/A');

            $rows[] = [$itemName]; // Item Header
            
            // Transaction Table Headers
            $rows[] = ['Date', 'Type', 'From', 'To', 'Ref', 'In', 'Out', 'Balance'];

            // Opening Balance
            // We use '' for cells that will be merged later
            $rows[] = ['Opening Balance', '', '', '', '', '', '', $data['opening_balance']];

            if(isset($data['transactions']) && is_array($data['transactions'])) {
                foreach($data['transactions'] as $tx) {
                     $rows[] = [
                        $tx['date'],
                        $tx['type'],
                        $tx['from'],
                        $tx['to'],
                        $tx['ref'],
                        $tx['in'] !== '-' ? $tx['in'] : '',
                        $tx['out'] !== '-' ? $tx['out'] : '',
                        $tx['balance']
                     ];
                }
            }
            $rows[] = []; // Spacer
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

        // We need to track the current row index to apply styles
        $row = 5; // Rows 1-3 are headers, 4 is empty, so data starts at 5

        foreach($this->reportData as $data) {
            // Item Header Row
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF3F4F6'); // Light gray
            $row++; // Move to next row

            // Column Headers Row
            $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
            $row++;

            // Opening Balance Row
            $sheet->mergeCells("A{$row}:E{$row}"); 
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;

            if(isset($data['transactions']) && is_array($data['transactions'])) {
                foreach($data['transactions'] as $tx) {
                    $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("H{$row}")->getFont()->setBold(true);
                    $row++;
                }
            }
            $row++; // Spacer row
        }
    }
}
