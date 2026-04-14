<?php

namespace App\Http\Controllers\Inventory\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Warehouse;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    protected $type = 'transfer';
    protected $routePrefix = 'inventory.stock-transfer';
    protected $viewPrefix = 'pages.inventory.stock-transfer';
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $transactions = Transaction::with(['fromWarehouse', 'toWarehouse', 'items'])
                ->when($request->filled('q'), function ($q) use ($request) {
                    $q->where(function($sq) use ($request) {
                        $sq->where('type', 'like', '%' . $request->q . '%')
                           ->orWhereHas('fromWarehouse', fn($ssq) => $ssq->where('name', 'like', '%' . $request->q . '%'))
                           ->orWhereHas('toWarehouse', fn($ssq) => $ssq->where('name', 'like', '%' . $request->q . '%'));
                    });
                })
                ->where('type', $this->type)
                ->forCurrentCompany()
                ->orderByDesc('created_at')
                ->paginate(10)
                ->onEachSide(1);

            return $this->apiResponse(200, "Fetched Transactions", ['transactions' => $transactions]);
        }
        return view($this->viewPrefix . '.index', ['type' => $this->type, 'routePrefix' => $this->routePrefix]);
    }

    public function create(Request $request)
    {
        $data = $this->getDependencyData();
        $data['type'] = $this->type;
        $data['transaction'] = null;
        $data['routePrefix'] = $this->routePrefix;
        $data['default_transaction_date'] = now()->format('Y-m-d\TH:i');
        
        return view($this->viewPrefix . '.show', $data);
    }

    public function store(StockTransferRequest $request)
    {
        $request->merge(['type' => $this->type]);

        if (!empty($request->items)) {
             $stockErrors = [];
             foreach ($request->items as $index => $item) {
                 try {
                     $this->stockService->validateStockHistory(
                         $item['item_id'],
                         $request->from_warehouse_id,
                         $item['quantity'], 
                         $item['uom_id'],   
                         $request->transaction_date
                     );
                 } catch (\Exception $e) {
                     $stockErrors["items.$index.quantity"] = $e->getMessage();
                 }
             }
             
             if (!empty($stockErrors)) {
                 return redirect()->back()->withErrors($stockErrors)->withInput();
             }
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'type' => $this->type,
                'company_id' => auth()->user()->company_id,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transaction_date' => $request->transaction_date,
            ]);

            $this->syncItems($transaction, $request->items ?? []);

            $transaction->load('items.item.uom', 'items.uom');

            foreach ($transaction->items as $item) {
                $this->stockService->transferStock(
                    $item->item_id,
                    $transaction->from_warehouse_id,
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

            DB::commit();
            return redirect()->route($this->routePrefix . '.index')->with('success', strtoupper($this->type) . ' created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Transaction $transaction)
    {
        if ($transaction->type !== $this->type) {
             abort(404);
        }

        $data = $this->getDependencyData();
        $data['type'] = $transaction->type;
        $data['transaction'] = $transaction;
        $data['transaction']->load('items.item', 'items.uom');
        $data['routePrefix'] = $this->routePrefix;
        $data['default_transaction_date'] = $transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');

        return view($this->viewPrefix . '.show', $data);
    }

    public function update(StockTransferRequest $request, Transaction $transaction)
    {
        if ($transaction->type !== $this->type) {
            abort(403);
       }

        $request->merge(['type' => $this->type]);


        DB::beginTransaction();
        try {
            $transaction->load('items.item.uom', 'items.uom');
            foreach ($transaction->items as $item) {
                $this->stockService->transferStock(
                    $item->item_id,
                    $transaction->to_warehouse_id,
                    $transaction->from_warehouse_id, 
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
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transaction_date' => $request->transaction_date,
            ]);

            $this->syncItems($transaction, $request->items ?? []);
            
            $transaction->refresh();
            $transaction->load('items.item.uom', 'items.uom');

            foreach ($transaction->items as $item) {
                $this->stockService->transferStock(
                    $item->item_id,
                    $transaction->from_warehouse_id,
                    $transaction->to_warehouse_id,
                    $item->quantity,
                    $item->uom_id,
                    $item->batch_no,
                    $item->lot_no,
                    $item->serial_no
                );
            }

            DB::commit();
            return redirect()->route($this->routePrefix . '.index')->with('success', strtoupper($transaction->type) . ' updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->type !== $this->type) {
            abort(403);
       }

        try {
            DB::transaction(function () use ($transaction) {
                $transaction->load('items.item.uom', 'items.uom');
                foreach ($transaction->items as $item) {
                    $this->stockService->transferStock(
                        $item->item_id,
                        $transaction->to_warehouse_id,
                        $transaction->from_warehouse_id, 
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
            });
            return redirect()->route($this->routePrefix . '.index')->with('success', 'Transaction deleted successfully.');
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
        ];
    }
}
