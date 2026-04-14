<?php

namespace App\Http\Controllers\Inventory\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemQuantity;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockExpiryExport;
use Barryvdh\DomPDF\Facade\Pdf;


class StockExpiryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->_getReportData($request);
        }

        $warehouses = Warehouse::all();
        return view('pages.inventory.stock-expiry.index', compact('warehouses'));
    }

    public function exportPdf(Request $request)
    {
        $response = $this->_getReportData($request);
        $data = $response->getData(true);

        if ($data['status'] !== 'success') {
            return redirect()->back()->with('error', $data['message'] ?? 'Error generating report.');
        }

        $reportData = $data['data'];
        $filters = [
            'date_from' => $request->input('date_from') ? Carbon::parse($request->input('date_from'))->format('d-m-Y') : null,
            'date_to' => $request->input('date_to') ? Carbon::parse($request->input('date_to'))->format('d-m-Y') : null,
            'warehouse_id' => $request->input('warehouse_id'),
            'date_range_type' => $request->input('date_range_type'),
        ];

        $warehouseName = 'Selected Warehouse';
        if($request->input('warehouse_id')){
             $w = Warehouse::find($request->input('warehouse_id'));
             if($w) $warehouseName = $w->name;
        }

        $pdf = PDF::loadView('pages.inventory.stock-expiry.pdf', compact('reportData', 'filters', 'warehouseName'));
        $pdf->setPaper('a4', 'portrait'); 

        return $pdf->stream('stock-expiry-report.pdf');
    }

    public function exportExcel(Request $request)
    {
        $response = $this->_getReportData($request);
        $data = $response->getData(true);

        if ($data['status'] !== 'success') {
             return redirect()->back()->with('error', $data['message'] ?? 'Error generating report.');
        }

        $reportData = $data['data'];
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'warehouse_id' => $request->input('warehouse_id'),
             'date_range_type' => $request->input('date_range_type'),
        ];

        $warehouseName = 'Selected Warehouse';
        if($request->input('warehouse_id')){
             $w = Warehouse::find($request->input('warehouse_id'));
             if($w) $warehouseName = $w->name;
        }

        return Excel::download(new StockExpiryExport($reportData, $filters, $warehouseName), 'stock-expiry-report.xlsx');
    }

    private function _getReportData(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $itemIds = $request->input('item_id');
        $dateFrom = $request->input('date_from'); 
        $dateTo = $request->input('date_to');     
        
        if (!$warehouseId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please select a Warehouse.'
            ]);
        }

        if ($itemIds && !is_array($itemIds)) {
            $itemIds = [$itemIds];
        }

        $stockQuery = ItemQuantity::where('warehouse_id', $warehouseId)
            ->whereNotNull('batch_no'); 
        
        // $stockQuery->where('quantity', '>', 0);
        
        if ($itemIds) {
            $stockQuery->whereIn('item_id', $itemIds);
        }

        $stocks = $stockQuery->with(['item.uom', 'warehouse', 'uom'])->get();
        
        if ($stocks->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'No stock found in this warehouse.'
            ]);
        }

        $batchNos = $stocks->pluck('batch_no')->unique()->filter()->toArray();
        $stockItemIds = $stocks->pluck('item_id')->unique()->toArray();
        
        $expiryRecords = TransactionItem::whereIn('item_id', $stockItemIds)
            ->whereIn('batch_no', $batchNos)
            ->whereNotNull('exp_date')
            ->select('item_id', 'batch_no', 'exp_date')
            ->orderBy('created_at', 'desc') 
            ->get()
            ->unique(function ($item) {
                return $item->item_id . '-' . $item->batch_no;
            })
            ->mapWithKeys(function ($item) {
                    return [$item->item_id . '-' . $item->batch_no => $item->exp_date];
            });
        
        $reportData = [];

        foreach ($stocks as $stock) {
            $key = $stock->item_id . '-' . $stock->batch_no;
            
            $expDate = null;
            $daysLeft = 0;
            $status = 'valid'; 

            if ($expiryRecords->has($key)) {
                $expDateStr = $expiryRecords->get($key);
                $expDate = Carbon::parse($expDateStr);

                if ($dateFrom && $expDate->lt(Carbon::parse($dateFrom)->startOfDay())) {
                    continue;
                }
                if ($dateTo && $expDate->gt(Carbon::parse($dateTo)->endOfDay())) {
                    continue;
                }

                $daysLeft = now()->diffInDays($expDate, false);
                
                if ($daysLeft < 0) {
                    $status = 'expired';
                } elseif ($daysLeft <= 30) {
                    $status = 'nearing_expiry';
                }
            } else {
                $status = 'no_expiry';
            }

            // UOM Conversion
            $qty = $stock->quantity;
            $uomName = '-';
            
            if ($stock->item && $stock->item->uom) {
                $uomName = $stock->item->uom->name;

                // Convert to Base UOM if different
                if ($stock->uom && $stock->uom->id !== $stock->item->uom->id) {
                        if ($stock->item->uom->si_unit > 0 && $stock->uom->si_unit > 0) {
                            $qty = $stock->quantity * ($stock->uom->si_unit / $stock->item->uom->si_unit);
                        }
                }
            } elseif ($stock->uom) {
                $uomName = $stock->uom->name;
            }
            
            if (($dateFrom || $dateTo) && $status === 'no_expiry') {
                continue;
            }

            $reportData[] = [
                'item_name' => $stock->item ? $stock->item->name : '-',
                'item_code' => $stock->item ? $stock->item->code : '-',
                'batch_no' => $stock->batch_no ?? '-',
                'quantity' => $qty + 0,
                'uom' => $uomName,
                'exp_date' => $expDate ? $expDate->format('d-m-Y') : '-',
                'days_left' => $expDate ? (int) $daysLeft : '-',
                'status' => $status
            ];
        }

        usort($reportData, function ($a, $b) {
            $dateA = $a['exp_date'] !== '-' ? strtotime($a['exp_date']) : PHP_INT_MAX;
            $dateB = $b['exp_date'] !== '-' ? strtotime($b['exp_date']) : PHP_INT_MAX;
            return $dateA - $dateB;
        });

        return response()->json([
            'status' => 'success',
            'data' => $reportData
        ]);
    }
}
