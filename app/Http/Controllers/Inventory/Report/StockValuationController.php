<?php

namespace App\Http\Controllers\Inventory\Report;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\Report\StockValuationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockValuationExport;
use \Carbon\Carbon;
use Illuminate\Http\Request;

class StockValuationController extends Controller
{
    protected StockValuationService $valuationService;

    public function __construct(StockValuationService $valuationService)
    {
        $this->valuationService = $valuationService;
    }

    public function index()
    {
        $warehouses = Warehouse::all();
        $items = Item::select('id', 'name', 'code')->where('is_active', true)->get();

        return view('pages.inventory.report.stock-valuation.index', compact('warehouses', 'items'));
    }

    public function generate(Request $request)
    {
        $reportData = $this->getReportData($request);

        return response()->json([
            'data' => $reportData
        ]);
    }

    public function exportPdf(Request $request)
    {
        $reportData = $this->getReportData($request);

        $filters = [
            'date' => Carbon::parse($request->date)->format('d-m-Y'),
            'method' => $request->method,
            'warehouse_ids' => $request->warehouse_ids,
        ];

        $pdf = Pdf::loadView('pages.inventory.report.stock-valuation.pdf', compact('reportData', 'filters'));
        $pdf->setPaper('a4', 'portrait'); 

        return $pdf->stream('stock-valuation-report.pdf');
    }

    public function exportExcel(Request $request)
    {
        $reportData = $this->getReportData($request);

        $filters = [
            'date' => $request->date,
            'method' => $request->method,
            'warehouse_ids' => $request->warehouse_ids,
        ];

        return Excel::download(new StockValuationExport($reportData, $filters), 'stock-valuation-report.xlsx');
    }


    private function getReportData(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'method' => 'required|in:FIFO,WEIGHTED_AVERAGE',
            'warehouse_ids' => 'required|array|min:1',
            'item_ids' => 'nullable|array',
        ]);

        $reportData = $this->valuationService->calculateValuation(
            $request->method,
            $request->date,
            $request->warehouse_ids ?? Warehouse::pluck('id')->toArray(),
            $request->item_ids ?? [] 
        );


        $warehouses = Warehouse::pluck('name', 'id');
        foreach ($reportData as &$row) {
            $row['warehouse_name'] = $warehouses[$row['warehouse_id']] ?? 'Unknown';
        }

        return $reportData;
    }


}
