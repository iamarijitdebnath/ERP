<?php

namespace App\Services\Inventory;

use App\Models\Inventory\Item;
use App\Models\Inventory\ItemQuantity;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\Uom;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{

    public function addStock($itemId, $warehouseId, $quantity, $uomId = null, $batchNo = null, $lotNo = null, $serialNo = null, $transactionDate = null, $transactionId = null)
    {
        // NO Normalization on Add. Store exactly what is passed.
        
        $attributes = [
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'uom_id' => $uomId, // Include UOM in unique constraint
            'batch_no' => $batchNo,
            'lot_no' => $lotNo,
            'serial_no' => $serialNo,
        ];

        $stock = ItemQuantity::where($attributes)->first();

        if ($stock) {
            $stock->increment('quantity', $quantity);
        } else {
            ItemQuantity::create(array_merge($attributes, ['quantity' => $quantity]));
        }
    }

    public function deductStock($itemId, $warehouseId, $quantity, $uomId, $batchNo = null, $lotNo = null, $serialNo = null, $transactionDate = null, $transactionId = null)
    {
        // 1. Validate Limit (Normalize everything to Base UOM for calculation)
        if ($transactionDate) {
            // Note: validateStockHistory signature fixed to ($itemId, $wh, $qty, $uomId, $date, $ignoreTxnId)
            // But wait, validateStockHistory logic should normalize internally. 
            // We pass the Requested Deduction Quantity & UOM directly.
            $this->validateStockHistory($itemId, $warehouseId, $quantity, $uomId, $transactionDate, $transactionId);
        }

        // 2. Deduction Logic (Multi-UOM Smart Deduction)
        
        // Strategy:
        // A. Try to deduct from EXACT UOM match first.
        // B. If not found/insufficient, find ANY match, convert, and deduct.
        
        // A. Exact Match
        $attributes = [
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'uom_id' => $uomId,
            'batch_no' => $batchNo,
            'lot_no' => $lotNo,
            'serial_no' => $serialNo,
        ];
        
        $stock = ItemQuantity::where($attributes)->first();
        
        if ($stock && $stock->quantity >= $quantity) {
            $stock->decrement('quantity', $quantity);
            if ($stock->quantity == 0) $stock->delete(); // Cleanup zero rows? Optional.
            return;
        }
        
        // B. Cross-UOM Deduction (Complex)
        // If we are here, we either have NO stock in this UOM, or insufficient stock.
        // We need to look for stock in OTHER UOMs and deduct equivalent value.
        
        // Calculate Required in Base UOM
        $item = Item::with('uom')->find($itemId);
        $reqUom = Uom::find($uomId);
        $baseSi = $item->uom->si_unit ?? 1;
        $reqSi = $reqUom->si_unit ?? 1;
        
        $requiredBaseQty = $quantity * ($reqSi / $baseSi);
        
        // Fetch ALL stock for this Item/Warehouse (ignoring UOM in where clause)
        $allStock = ItemQuantity::where([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId,
            'batch_no' => $batchNo,
            'lot_no' => $lotNo,
            'serial_no' => $serialNo,
        ])->get();
        
        foreach ($allStock as $stockRecord) {
            if ($requiredBaseQty <= 0) break;
            
            // Calculate Stock in Base UOM
            $stockUom = Uom::find($stockRecord->uom_id);
            $stockSi = $stockUom->si_unit ?? 1;
            
            $stockBaseQty = $stockRecord->quantity * ($stockSi / $baseSi);
            
            if ($stockBaseQty <= 0) continue;
            
            if ($stockBaseQty >= $requiredBaseQty) {
                // Deduct all required from this record
                // Convert Required Base back to Stock UOM
                $deductQtyStockUom = $requiredBaseQty / ($stockSi / $baseSi);
                
                $stockRecord->decrement('quantity', $deductQtyStockUom);
                $requiredBaseQty = 0;
            } else {
                // Consume this record entirely
                $requiredBaseQty -= $stockBaseQty;
                $stockRecord->update(['quantity' => 0]); // Or delete
            }
        }
        
        // If $requiredBaseQty > 0 here, it means we deducted everything but still shortage.
        // This theoretically shouldn't happen if validation passed, BUT if it does, 
        // we might need to create a negative balance record in the REQUESTED UOM.
        if ($requiredBaseQty > 0) {
             // Create negative record in REQUESTED UOM
             $remainingReqQty = $requiredBaseQty / ($reqSi / $baseSi);
             $this->addStock($itemId, $warehouseId, -$remainingReqQty, $uomId, $batchNo, $lotNo, $serialNo);
        }
    }

    public function transferStock($itemId, $fromWarehouseId, $toWarehouseId, $quantity, $uomId = null, $batchNo = null, $lotNo = null, $serialNo = null, $transactionDate = null, $transactionId = null)
    {
        DB::transaction(function () use ($itemId, $fromWarehouseId, $toWarehouseId, $quantity, $uomId, $batchNo, $lotNo, $serialNo, $transactionDate, $transactionId) {
            $this->deductStock($itemId, $fromWarehouseId, $quantity, $uomId, $batchNo, $lotNo, $serialNo, $transactionDate, $transactionId);
            $this->addStock($itemId, $toWarehouseId, $quantity, $uomId, $batchNo, $lotNo, $serialNo, $transactionDate, $transactionId);
        });
    }

    public function getNormalizedQuantity($itemId, $uomId, $quantity)
    {
        $item = Item::with('uom')->find($itemId);
        $uom = Uom::find($uomId);

        if (!$item || !$item->uom || !$uom) {
            return $quantity;
        }

        $baseSi = $item->uom->si_unit;
        $txnSi = $uom->si_unit;

        if ($baseSi <= 0 || $txnSi <= 0) {
             return $quantity;
        }

        return $quantity * ($txnSi / $baseSi);
    }

    public function validateStockHistory($itemId, $warehouseId, $deductQuantity, $deductUomId, $txnDate, $ignoreTxnId = null)
    {
        // 1. Normalize Request to Base UOM
        $deductBaseQty = $this->getNormalizedQuantity($itemId, $deductUomId, $deductQuantity);

        // 2. Calculate Current Stock in Base UOM (Sum of all UOMs normalized)
        $currentStockRecords = ItemQuantity::where([
            'item_id' => $itemId,
            'warehouse_id' => $warehouseId
        ])->get();

        $currentStockBase = 0;
        foreach ($currentStockRecords as $record) {
             $currentStockBase += $this->getNormalizedQuantity($itemId, $record->uom_id, $record->quantity);
        }

        // 3. Fetch Future Transactions
        $transactions = Transaction::query()
            ->with(['items' => function ($query) use ($itemId) {
                $query->where('item_id', $itemId)
                    ->with(['uom', 'item.uom']);
            }])
            ->whereHas('items', function ($query) use ($itemId) {
                $query->where('item_id', $itemId);
            })
            ->where('transaction_date', '>=', $txnDate)
            ->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                    ->orWhere('to_warehouse_id', $warehouseId);
            })
            ->when($ignoreTxnId, function ($q) use ($ignoreTxnId) {
                $q->where('id', '!=', $ignoreTxnId);
            })
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 4. Calculate Future Impact (Backwards from Now) using Normalized Quantities
        $futureImpactBase = 0;
        foreach ($transactions as $txn) {
            foreach ($txn->items as $txnItem) {
                if ($txnItem->item_id != $itemId) continue;
                
                $normalizedQty = $this->getNormalizedQuantity($txnItem->item_id, $txnItem->uom_id, $txnItem->quantity);

                if ($txn->to_warehouse_id == $warehouseId) {
                    $futureImpactBase += $normalizedQty;
                } elseif ($txn->from_warehouse_id == $warehouseId) {
                    $futureImpactBase -= $normalizedQty;
                }
            }
        }

        // 5. Calculate Running Balance at the moment *before* this transaction
        $runningBalanceBase = $currentStockBase - $futureImpactBase;

        // 6. Simulate Timeline Forward
        $deductionApplied = false;

        foreach ($transactions as $txn) {
            // Apply our deduction if we reached/passed its date
            if ($txn->transaction_date > $txnDate && !$deductionApplied) {
                $runningBalanceBase -= $deductBaseQty;
                $deductionApplied = true;

                if ($runningBalanceBase < -0.0001) {
                    throw new Exception("Insufficient stock availability on $txnDate. Normalized Balance would be " . number_format($runningBalanceBase, 4) . ".");
                }
            }

            // Apply Transaction Impact
            foreach ($txn->items as $txnItem) {
                if ($txnItem->item_id != $itemId) continue;
                
                $normalizedQty = $this->getNormalizedQuantity($txnItem->item_id, $txnItem->uom_id, $txnItem->quantity);

                if ($txn->to_warehouse_id == $warehouseId) {
                    $runningBalanceBase += $normalizedQty;
                } elseif ($txn->from_warehouse_id == $warehouseId) {
                    $runningBalanceBase -= $normalizedQty;
                }
            }
            
            if ($runningBalanceBase < -0.0001) {
                 throw new Exception("Insufficient stock. Future transaction (ID: {$txn->id}) on {$txn->transaction_date->format('Y-m-d')} would result in negative stock.");
            }
        }

        // If newer than all future transactions
        if (!$deductionApplied) {
            $runningBalanceBase -= $deductBaseQty;
            if ($runningBalanceBase < -0.0001) {
                 throw new Exception("Insufficient stock request. Balance would be " . number_format($runningBalanceBase, 4) . ".");
            }
        }
    }
}



