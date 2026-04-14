<?php

namespace App\Http\Controllers\Inventory\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\GrnRequest;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryGoodsReceiptNote;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HRMS\Employee;
use Carbon\Carbon;

class GrnController extends Controller
{
    protected $type = 'grn';
    protected $routePrefix = 'inventory.grn';
    protected $viewPrefix = 'pages.inventory.grn';
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $grns = InventoryGoodsReceiptNote::with(['transaction.toWarehouse', 'transaction.items', 'receivedBy'])
                ->when($request->filled('q'), function ($q) use ($request) {
                    $q->where('doc_no', 'like', '%' . $request->q . '%')
                      ->orWhere('remarks', 'like', '%' . $request->q . '%')
                      ->orWhereHas('transaction.toWarehouse', fn($ssq) => $ssq->where('name', 'like', '%' . $request->q . '%'));
                })
                ->forCurrentCompany()
                ->orderByDesc('created_at')
                ->paginate(10)
                ->onEachSide(1);

            $supplierIds = $grns->pluck('supplier_id')->filter()->unique();
            $suppliers = DB::table('purchase_suppliers')->whereIn('id', $supplierIds)->pluck('name', 'id');
            
            $poIds = $grns->pluck('purchase_order_id')->filter()->unique();
            $pos = DB::table('purchase_orders')->whereIn('id', $poIds)->pluck('doc_no', 'id');

            $grns->getCollection()->transform(function ($grn) use ($suppliers, $pos) {
                $grn->supplier_name = $suppliers->get($grn->supplier_id) ?? '-';
                $grn->purchase_order_number = $grn->purchase_order_id ? ($pos->get($grn->purchase_order_id) ?? $grn->purchase_order_no) : $grn->purchase_order_no;
                return $grn;
            });

            return $this->apiResponse(200, "Fetched Transactions", ['transactions' => $grns]);
        }
        return view($this->viewPrefix . '.index', ['type' => $this->type, 'routePrefix' => $this->routePrefix]);
    }

    public function create(Request $request)
    {
        $data = $this->getDependencyData();
        $data['type'] = $this->type;
        $data['transaction'] = null;
        $data['grn'] = null;
        $data['routePrefix'] = $this->routePrefix;
        $data['default_transaction_date'] = now()->format('Y-m-d');
        
        return view($this->viewPrefix . '.show', $data);
    }

    public function store(GrnRequest $request)
    {
        $request->merge(['type' => $this->type]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'type' => $this->type,
                'company_id' => auth()->user()->company_id,
                'from_warehouse_id' => null, 
                'to_warehouse_id' => $request->to_warehouse_id,
                'transaction_date' => $request->transaction_date, 
            ]);

            $grn = InventoryGoodsReceiptNote::create([
                'transaction_id' => $transaction->id,
                'company_id' => auth()->user()->company_id,
                'date' => $request->transaction_date,
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'purchase_order_no' => $request->purchase_order_no, 
                'received_by' => $request->received_by,
                'remarks' => $request->remarks,
                'status' => 'active',                
            ]);

            $transaction->update(['type_id' => $grn->id]);

            $this->syncItems($transaction, $request->items ?? []);

            $transaction->load('items.item.uom', 'items.uom'); 

            foreach ($transaction->items as $item) {
                $this->stockService->addStock(
                    $item->item_id,
                    $transaction->to_warehouse_id,
                    $item->quantity,
                    $item->uom_id,
                    $item->batch_no,
                    $item->lot_no,
                    $item->serial_no
                );
            }

            DB::commit();
            return redirect()->route($this->routePrefix . '.index')->with('success', strtoupper($this->type) . ' created successfully. GRN No: ' . $grn->doc_no);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $grn = InventoryGoodsReceiptNote::with(['transaction.items.item', 'transaction.items.uom'])->find($id);

        if (!$grn) {
             abort(404, 'GRN not found.');
        }

        $data = $this->getDependencyData();
        $data['type'] = $this->type;
        $data['grn'] = $grn;
        $data['transaction'] = $grn->transaction;
        $data['routePrefix'] = $this->routePrefix;
        $data['default_transaction_date'] = $grn->date ? Carbon::parse($grn->date)->format('Y-m-d') : now()->format('Y-m-d');
        
        if ($data['transaction'] && $data['transaction']->items) {
            $data['transaction']->items->each(function($item) {
                $item->formatted_exp_date = $item->exp_date ? Carbon::parse($item->exp_date)->format('Y-m-d') : '';
            });
        }

        return view($this->viewPrefix . '.show', $data);
    }

    public function update(GrnRequest $request, $id)
    {
        $grn = InventoryGoodsReceiptNote::findOrFail($id);
        $transaction = $grn->transaction;

        if (!$transaction) {
            return redirect()->back()->with('error', 'Linked transaction not found.');
        }

        $request->merge(['type' => $this->type]);

        DB::beginTransaction();
        try {
            $transaction->load('items.item.uom', 'items.uom');

            foreach ($transaction->items as $item) {
                $this->stockService->deductStock(
                    $item->item_id,
                    $transaction->to_warehouse_id,
                    $item->quantity,
                    $item->uom_id,
                    $item->batch_no,
                    $item->lot_no,
                    $item->serial_no,
                    $transaction->transaction_date,
                    $transaction->id
                );
            }

            $transaction->update([
                'to_warehouse_id' => $request->to_warehouse_id,
                'transaction_date' => $request->transaction_date,
            ]);

            $grn->update([
                'date' => $request->transaction_date,
                'supplier_id' => $request->supplier_id,
                'purchase_order_id' => $request->purchase_order_id,
                'purchase_order_no' => $request->purchase_order_no,
                'received_by' => $request->received_by,
                'remarks' => $request->remarks,
            ]);

            $this->syncItems($transaction, $request->items ?? []);

            $transaction->refresh(); 
            $transaction->load('items.item.uom', 'items.uom');

            foreach ($transaction->items as $item) {
                $this->stockService->addStock(
                    $item->item_id,
                    $transaction->to_warehouse_id,
                    $item->quantity,
                    $item->uom_id,
                    $item->batch_no,
                    $item->lot_no,
                    $item->serial_no
                );
            }

            DB::commit();
            return redirect()->route($this->routePrefix . '.index')->with('success', strtoupper($this->type) . ' updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $grn = InventoryGoodsReceiptNote::find($id);

        if (!$grn) abort(404);
        
        $transaction = $grn->transaction;

        try {
            DB::transaction(function () use ($transaction, $grn) {
                if ($transaction) {
                    $transaction->load('items.item.uom', 'items.uom');
                    foreach ($transaction->items as $item) {
                        $this->stockService->deductStock(
                            $item->item_id,
                            $transaction->to_warehouse_id,
                            $item->quantity,
                            $item->uom_id,
                            $item->batch_no,
                            $item->lot_no,
                            $item->serial_no,
                            $transaction->transaction_date,
                            $transaction->id
                        );
                    }
                    $transaction->items()->delete();
                    $transaction->delete();
                }
                $grn->delete();
            });
            return redirect()->route($this->routePrefix . '.index')->with('success', 'GRN deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    private function syncItems(Transaction $transaction, array $itemsInput)
    {
        $existingItemIds = $transaction->items()->pluck('id')->toArray();
        $processedItemIds = [];

        foreach ($itemsInput as $itemData) {
            $attributes = [
                'inventory_transaction_id' => $transaction->id,
                'item_id' => $itemData['item_id'],
                'uom_id' => $itemData['uom_id'],
                'quantity' => $itemData['quantity'],
                'batch_no' => $itemData['batch_no'] ?? null,
                'lot_no' => $itemData['lot_no'] ?? null,
                'serial_no' => $itemData['serial_no'] ?? null,
                'exp_date' => $itemData['exp_date'] ?? null,
                'price' => $itemData['price'] ?? 0,
                'igst' => $itemData['igst'] ?? 0,
                'cgst' => $itemData['cgst'] ?? 0,
                'sgst' => $itemData['sgst'] ?? 0,
                'cess' => $itemData['cess'] ?? 0,
            ];

            if (!empty($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                $item = TransactionItem::find($itemData['id']);
                if ($item) {
                    $item->update($attributes);
                    $processedItemIds[] = $itemData['id'];
                }
            } else {
                $newItem = TransactionItem::create($attributes);
                $processedItemIds[] = $newItem->id;
            }
        }

        $itemsToDelete = array_diff($existingItemIds, $processedItemIds);
        if (!empty($itemsToDelete)) {
            TransactionItem::destroy($itemsToDelete);
        }
    }

    private function getDependencyData()
    {
        $companyId = auth()->user()->company_id;
        return [
            'warehouses' => Warehouse::forCurrentCompany()->get(),
            'suppliers' => DB::table('purchase_suppliers')->where('company_id', $companyId)->get(),
            'purchase_orders' => DB::table('purchase_orders')->where('company_id', $companyId)->get(),
            'employees' => Employee::forCurrentCompany()->get(),
        ];
    }
}
