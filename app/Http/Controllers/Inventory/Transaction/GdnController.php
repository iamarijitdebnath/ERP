<?php

namespace App\Http\Controllers\Inventory\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\GdnRequest;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\InventoryGoodsDeliveryNote;
use App\Services\Inventory\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\StockValidationException;
use App\Models\HRMS\Employee;

class GdnController extends Controller
{
    protected $type = 'gdn';
    protected $routePrefix = 'inventory.gdn';
    protected $viewPrefix = 'pages.inventory.gdn';
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $gdns = InventoryGoodsDeliveryNote::with(['transaction.fromWarehouse', 'transaction.items', 'issuedBy'])
                ->when($request->filled('q'), function ($q) use ($request) {
                    $q->where('doc_no', 'like', '%' . $request->q . '%')
                      ->orWhere('remarks', 'like', '%' . $request->q . '%')
                      ->orWhereHas('transaction.fromWarehouse', fn($ssq) => $ssq->where('name', 'like', '%' . $request->q . '%'));
                })
                ->forCurrentCompany()
                ->orderByDesc('created_at')
                ->paginate(10)
                ->onEachSide(1);

            $customerIds = $gdns->pluck('customer_id')->filter()->unique();
            $customers = DB::table('sales_customers')->whereIn('id', $customerIds)->pluck('name', 'id');
            
            $invoiceIds = $gdns->pluck('invoice_id')->filter()->unique();
            $invoices = DB::table('sales_invoices')->whereIn('id', $invoiceIds)->pluck('doc_no', 'id');

            $gdns->getCollection()->transform(function ($gdn) use ($customers, $invoices) {
                $gdn->customer_name = $customers->get($gdn->customer_id) ?? '-';
                $gdn->invoice_number = $invoices->get($gdn->invoice_id) ?? '-';
                return $gdn;
            });

            return $this->apiResponse(200, "Fetched Transactions", ['transactions' => $gdns]);
        }
        return view($this->viewPrefix . '.index', ['type' => $this->type, 'routePrefix' => $this->routePrefix]);
    }

    public function create(Request $request)
    {
        $data = $this->getDependencyData();
        $data['type'] = $this->type;
        $data['transaction'] = null;
        $data['gdn'] = null;
        $data['routePrefix'] = $this->routePrefix;
        $data['default_transaction_date'] = now()->format('Y-m-d');
        
        return view($this->viewPrefix . '.show', $data);
    }

    public function store(GdnRequest $request)
    {
        $request->merge(['type' => $this->type]);
        if (!empty($request->items)) {
             $stockErrors = [];
             foreach ($request->items as $index => $item) {
                 try {
                     $normalizedQty = $this->stockService->getNormalizedQuantity($item['item_id'], $item['uom_id'], $item['quantity']);
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
                'to_warehouse_id' => null,
                'transaction_date' => $request->transaction_date,
            ]);

            $gdn = InventoryGoodsDeliveryNote::create([
                'transaction_id' => $transaction->id,
                'company_id' => auth()->user()->company_id,
                'date' => $request->transaction_date,
                'customer_id' => $request->customer_id,
                'invoice_id' => $request->invoice_id,
                'issued_by' => $request->issued_by,
                'way_bill_no' => $request->way_bill_no,
                'remarks' => $request->remarks,
                'status' => 'active',
            ]);

            $transaction->update(['type_id' => $gdn->id]);

            $this->syncItems($transaction, $request->items ?? []);

            $transaction->load('items.item.uom', 'items.uom');

            foreach ($transaction->items as $item) {
                $this->stockService->deductStock(
                    $item->item_id,
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

            DB::commit();
            return redirect()->route($this->routePrefix . '.index')->with('success', strtoupper($this->type) . ' created successfully. Doc No: ' . $gdn->doc_no);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Creation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $gdn = InventoryGoodsDeliveryNote::with(['transaction.items.item', 'transaction.items.uom'])->find($id);

        if (!$gdn) {
             abort(404, 'GDN not found.');
        }

        $data = $this->getDependencyData();
        $data['type'] = $this->type;
        $data['gdn'] = $gdn;
        $data['transaction'] = $gdn->transaction;
        $data['routePrefix'] = $this->routePrefix;
        $data['default_transaction_date'] = $gdn->date ? \Carbon\Carbon::parse($gdn->date)->format('Y-m-d') : now()->format('Y-m-d');
        
        if ($data['transaction'] && $data['transaction']->items) {
            $data['transaction']->items->each(function($item) {
                $item->formatted_exp_date = $item->exp_date ? \Carbon\Carbon::parse($item->exp_date)->format('Y-m-d') : '';
            });
        }
        
        return view($this->viewPrefix . '.show', $data);
    }

    public function update(GdnRequest $request, $id)
    {
        $gdn = InventoryGoodsDeliveryNote::findOrFail($id);
        $transaction = $gdn->transaction;

        if (!$transaction) {
            return redirect()->back()->with('error', 'Linked transaction not found.');
        }

        $request->merge(['type' => $this->type]);

        DB::beginTransaction();
        try {

            $transaction->load('items.item.uom', 'items.uom');
            foreach ($transaction->items as $item) {
                 $this->stockService->addStock(
                    $item->item_id,
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
                'transaction_date' => $request->transaction_date,
            ]);


            $gdn->update([
                'date' => $request->transaction_date,
                'customer_id' => $request->customer_id,
                'invoice_id' => $request->invoice_id,
                'issued_by' => $request->issued_by,
                'way_bill_no' => $request->way_bill_no,
                'remarks' => $request->remarks,
            ]);

            $this->syncItems($transaction, $request->items ?? []);
            
            $transaction->refresh();
            $transaction->load('items.item.uom', 'items.uom');

             foreach ($transaction->items as $index => $item) { 
                try {
                    $this->stockService->validateStockHistory(
                         $item->item_id,
                         $transaction->from_warehouse_id,
                         $item->quantity, 
                         $item->uom_id,   
                         $transaction->transaction_date
                     );
                } catch (\Exception $e) {
                     $inputIndex = 0; 
                      if ($request->items) {
                         foreach ($request->items as $idx => $reqItem) {
                             if (isset($reqItem['id']) && $reqItem['id'] == $item->id) {
                                  $inputIndex = $idx;
                                  break;
                             }

                             if (!isset($reqItem['id']) && $reqItem['item_id'] == $item->item_id && $reqItem['quantity'] == $item->quantity) {
                                  $inputIndex = $idx;
                             }
                         }
                     }
                     throw new StockValidationException($e->getMessage(), "items.$inputIndex.quantity");
                }
            }


            foreach ($transaction->items as $index => $item) { 
                $this->stockService->deductStock(
                    $item->item_id,
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

            DB::commit();
            return redirect()->route($this->routePrefix . '.index')->with('success', strtoupper($this->type) . ' updated successfully.');

        } catch (StockValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([$e->getField() => $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Update failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $gdn = InventoryGoodsDeliveryNote::find($id);

        if (!$gdn) abort(404);

        $transaction = $gdn->transaction;

        try {
            DB::transaction(function () use ($transaction, $gdn) {
                if ($transaction) {
                    $transaction->load('items.item.uom', 'items.uom');
                    foreach ($transaction->items as $item) {
                        $this->stockService->addStock(
                            $item->item_id,
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
                }
                $gdn->delete();
            });
            
            return redirect()->route($this->routePrefix . '.index')->with('success', 'GDN deleted successfully.');
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
            'customers' => DB::table('sales_customers')->where('company_id', $companyId)->get(),
            'invoices' => DB::table('sales_invoices')->where('company_id', $companyId)->get(),
            'employees' => Employee::forCurrentCompany()->get(),
        ];
    }
}
