<?php

namespace App\Http\Controllers\Inventory\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockRegisterExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Inventory\InventoryGoodsReceiptNote;
use App\Models\Inventory\InventoryGoodsDeliveryNote;

class StockRegisterController extends Controller
{

    public function index(Request $request){
        
        if ($request->ajax()) {
            return $this->_getReportData($request);
        }
        $warehouses = Warehouse::all();
        
        return view("pages.inventory.stock-register.index", compact('warehouses'));
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
        ];

        $warehouseName = 'All Warehouses';
        if($request->input('warehouse_id')){
             $w = Warehouse::find($request->input('warehouse_id'));
             if($w) $warehouseName = $w->name;
        }


        $pdf = Pdf::loadView('pages.inventory.stock-register.pdf', compact('reportData', 'filters', 'warehouseName'));
        $pdf->setPaper('a4', 'portrait'); 

        return $pdf->stream('stock-register.pdf');
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
        ];

        $warehouseName = 'All Warehouses';
        if($request->input('warehouse_id')){
             $w = Warehouse::find($request->input('warehouse_id'));
             if($w) $warehouseName = $w->name;
        }

        return Excel::download(new StockRegisterExport($reportData, $filters, $warehouseName), 'stock-register.xlsx');
    }

    private function _getReportData(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $itemId = $request->input('item_id');

        if (!$warehouseId || !$itemId) {
             return response()->json([
                'status' => 'error',
                'message' => 'Please select both Warehouse and Item to generate the report.'
            ]);
        }

        $warehouseIds = $warehouseId ? [$warehouseId] : [];
        $itemIds = $itemId ? [$itemId] : [];

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $q = $request->input('q');

        $itemsQuery = Item::query()->with('uom');
        
        if (!empty($itemIds)) {
            $itemsQuery->whereIn('id', $itemIds);
        }

        if (!empty($q)) {
            $itemsQuery->where(function($query) use ($q){
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $items = $itemsQuery->get();
        
        $reportData = [];

        if(empty($warehouseIds)){
             $warehouseIds = Warehouse::pluck('id')->toArray();
        }

        foreach ($items as $item) {
            $openingTransactions = TransactionItem::where('item_id', $item->id)
                ->whereHas('transaction', function($query) use ($dateFrom, $warehouseIds) {
                    $query->whereDate('transaction_date', '<', $dateFrom)
                          ->where(function($q) use ($warehouseIds) {
                              $q->whereIn('from_warehouse_id', $warehouseIds)
                                ->orWhereIn('to_warehouse_id', $warehouseIds);
                          });
                })
                ->with(['transaction', 'uom'])
                ->get();

            $totalIn = 0;
            $totalOut = 0;

            foreach($openingTransactions as $opItem) {
                $tx = $opItem->transaction;
                
                
                $qty = $opItem->quantity;
                if ($item->uom && $opItem->uom && $item->uom->si_unit > 0 && $opItem->uom->si_unit > 0) {
                     $qty = $opItem->quantity * ($opItem->uom->si_unit / $item->uom->si_unit);
                }

                if (in_array($tx->to_warehouse_id, $warehouseIds)) {
                    $totalIn += $qty;
                }
                if (in_array($tx->from_warehouse_id, $warehouseIds)) {
                    $totalOut += $qty;
                }
            }

            $openingBalance = $totalIn - $totalOut;
            $currentBalance = $openingBalance;
            $transactionsParams = TransactionItem::with(['transaction.fromWarehouse', 'transaction.toWarehouse', 'uom'])
                ->where('item_id', $item->id)
                ->whereHas('transaction', function($query) use ($dateFrom, $dateTo, $warehouseIds) {
                    $query->whereDate('transaction_date', '>=', $dateFrom)
                          ->whereDate('transaction_date', '<=', $dateTo)
                          ->where(function($q) use ($warehouseIds) {
                              $q->whereIn('from_warehouse_id', $warehouseIds)
                                ->orWhereIn('to_warehouse_id', $warehouseIds);
                          });
                })
                ->get()
                ->sortBy([
                    ['transaction.transaction_date', 'asc'],
                    ['transaction.created_at', 'asc']
                ]);

            $grnIds = $transactionsParams->pluck('transaction')->where('type', 'grn')->pluck('type_id')->unique()->toArray();
            $gdnIds = $transactionsParams->pluck('transaction')->where('type', 'gdn')->pluck('type_id')->unique()->toArray();

            $grnDocs = InventoryGoodsReceiptNote::whereIn('id', $grnIds)->pluck('doc_no', 'id');
            $gdnDocs = InventoryGoodsDeliveryNote::whereIn('id', $gdnIds)->pluck('doc_no', 'id');
            
            $formattedTransactions = [];
            
            foreach ($transactionsParams as $txItem) {
                $tx = $txItem->transaction;

                $qty = $txItem->quantity;
                if ($item->uom && $txItem->uom && $item->uom->si_unit > 0 && $txItem->uom->si_unit > 0) {
                     $qty = $txItem->quantity * ($txItem->uom->si_unit / $item->uom->si_unit);
                }

                $inQty = 0;
                $outQty = 0;
                $originalQty = $txItem->quantity;
                $originalIn = 0;
                $originalOut = 0;

                if (in_array($tx->to_warehouse_id, $warehouseIds) && in_array($tx->from_warehouse_id, $warehouseIds)) {
                    $inQty = $qty;
                    $outQty = $qty;
                    $originalIn = $originalQty;
                    $originalOut = $originalQty;
                } elseif (in_array($tx->to_warehouse_id, $warehouseIds)) {
                    $inQty = $qty;
                    $originalIn = $originalQty;
                } elseif (in_array($tx->from_warehouse_id, $warehouseIds)) {
                    $outQty = $qty;
                    $originalOut = $originalQty;
                }
                
                $currentBalance = $currentBalance + $inQty - $outQty;
                
                $uomName = $txItem->uom ? $txItem->uom->name : '';
                $baseUomName = $item->uom ? $item->uom->name : '';

                $docNo = '-';
                if ($tx->type === 'grn') {
                    $docNo = $grnDocs[$tx->type_id] ?? '-';
                } elseif ($tx->type === 'gdn') {
                    $docNo = $gdnDocs[$tx->type_id] ?? '-';
                }

                $formattedTransactions[] = [
                    'date' => $tx->transaction_date->format('d-m-Y'),
                    'doc_no' => $docNo,
                    'type' => $tx->type, 
                    'from' => $tx->fromWarehouse ? $tx->fromWarehouse->name : '-',
                    'to' => $tx->toWarehouse ? $tx->toWarehouse->name : '-',
                    'in' => $inQty > 0 ? trim(($originalIn + 0) . ' ' . $uomName) : '-', 
                    'out' => $outQty > 0 ? trim(($originalOut + 0) . ' ' . $uomName) : '-', 
                    'balance' => trim(($currentBalance + 0) . ' ' . $baseUomName)
                ];
            }
            
            if ($currentBalance != 0 || count($formattedTransactions) > 0 || $openingBalance != 0) {
                 $baseUomName = $item->uom ? $item->uom->name : '';
                 $reportData[] = [
                    'item' => $item,
                    'opening_balance' => trim(($openingBalance + 0) . ' ' . $baseUomName),
                    'transactions' => $formattedTransactions
                 ];
            }
        }


        return response()->json([
            'status' => 'success',
            'data' => $reportData
        ]);
    }
}
