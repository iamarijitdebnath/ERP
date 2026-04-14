<?php

namespace App\Services\Inventory\Report;

use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Uom;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockValuationService
{
    /**
     * Calculate stock valuation report
     *
     * @param string $method 'FIFO', 'LIFO', 'WEIGHTED_AVERAGE'
     * @param string|array $date Can be 'Y-m-d' (as on) or ['start' => 'Y-m-d', 'end' => 'Y-m-d']
     * @param array $warehouseIds
     * @param array $itemIds
     * @return array
     */
    public function calculateValuation($method, $date, $warehouseIds = [], $itemIds = [])
    {
        // 1. Resolve Date Range
        $endDate = is_array($date) ? $date['end'] : $date;
        $startDate = is_array($date) ? $date['start'] : null;

        // 2. Fetch Items
        $itemsQuery = Item::query()->with('uom');
        if (!empty($itemIds)) {
            $itemsQuery->whereIn('id', $itemIds);
        }
        $items = $itemsQuery->get();

        $reportData = [];

        foreach ($items as $item) {
            foreach ($warehouseIds as $warehouseId) {
                // Determine logic based on method
                switch ($method) {
                    case 'FIFO':
                        $result = $this->calculateFIFO($item, $warehouseId, $endDate);
                        break;

                    case 'WEIGHTED_AVERAGE':
                        $result = $this->calculateWeightedAverage($item, $warehouseId, $endDate);
                        break;
                    default:
                        $result = ['quantity' => 0, 'value' => 0, 'rate' => 0];
                }

                if ($result['quantity'] != 0 || $result['value'] != 0) {
                    $reportData[] = [
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'item_code' => $item->code,
                        'uom' => $item->uom->name ?? 'N/A',
                        'warehouse_id' => $warehouseId,
                        // 'warehouse_name' => ... (can map later or eager load)
                        'quantity' => $result['quantity'],
                        'rate' => $result['rate'],
                        'value' => $result['value'],
                        'method' => $method
                    ];
                }
            }
        }

        return $reportData;
    }

    /**
     * Get all transactions for an item/warehouse up to a date
     */
    private function getTransactions($itemId, $warehouseId, $endDate)
    {
        return Transaction::query()
            ->with(['items' => function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            }])
            ->whereHas('items', function ($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })
            ->where('transaction_date', '<=', Carbon::parse($endDate)->endOfDay())
            ->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                  ->orWhere('to_warehouse_id', $warehouseId);
            })
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    private function calculateFIFO($item, $warehouseId, $endDate)
    {
        $transactions = $this->getTransactions($item->id, $warehouseId, $endDate);
        
        // Queue of Layers: [['qty' => 10, 'rate' => 100], ...]
        $layers = []; 

        foreach ($transactions as $txn) {
            foreach ($txn->items as $txnItem) {
                if ($txnItem->item_id != $item->id) continue;

                $qty = $this->normalizeQty($txnItem, $item);
                $rate = $this->normalizeRate($txnItem, $item); // Price per Base Unit

                if ($txn->to_warehouse_id == $warehouseId) {
                    $layers[] = ['qty' => $qty, 'rate' => $rate];
                } elseif ($txn->from_warehouse_id == $warehouseId) {
                    $layers = $this->consumeLayers($layers, $qty);
                }
            }
        }

        return $this->aggregateLayers($layers);
    }



    private function consumeLayers($layers, $qtyToConsume)
    {
        $remainder = $qtyToConsume;

        while ($remainder > 0.0001 && !empty($layers)) {
            // Get layer index (FIFO only now)
            $idx = 0;
            
            $layerQty = $layers[$idx]['qty'];

            if ($layerQty > $remainder) {
                // Partial consumption
                $layers[$idx]['qty'] -= $remainder;
                $remainder = 0;
            } else {
                // Full consumption of layer
                $remainder -= $layerQty;
                // Remove layer (FIFO only)
                array_shift($layers);
            }
        }
        
        // If remainder > 0, it means we issued more than we had. 
        // In strictly valid systems this shouldn't happen, but we can't crash.
        // We will just return empty layers (technically negative stock is complex in FIFO/LIFO).
        
        return $layers;
    }

    private function calculateWeightedAverage($item, $warehouseId, $endDate)
    {
        $transactions = $this->getTransactions($item->id, $warehouseId, $endDate);
        
        $totalQty = 0;
        $totalValue = 0;

        foreach ($transactions as $txn) {
            foreach ($txn->items as $txnItem) {
                if ($txnItem->item_id != $item->id) continue;
                
                $qty = $this->normalizeQty($txnItem, $item);
                
                // Determine rate. If it's a purchase (GRN), use the transaction price.
                // If it's a Transfer IN, we *should* ideally look up the source cost, 
                // but if not available, we might fallback to current avg or 0.
                // For simplicity, let's assume 'price' on txn item is the effective cost.
                $inputRate = $this->normalizeRate($txnItem, $item);

                if ($txn->to_warehouse_id == $warehouseId) {
                    // INBOUND
                    // Weighted Average: New Avg = (OldVal + NewVal) / (OldQty + NewQty)
                    $totalQty += $qty;
                    $totalValue += ($qty * $inputRate);
                } elseif ($txn->from_warehouse_id == $warehouseId) {
                    // OUTBOUND
                    // Issue at CURRENT Average Cost
                    $currentAvgRate = ($totalQty > 0) ? ($totalValue / $totalQty) : 0;
                    
                    $totalQty -= $qty;
                    $totalValue -= ($qty * $currentAvgRate);
                }
            }
        }
        
        // Handle floating point weirdness or near-zero
        if ($totalQty <= 0.0001) {
            $totalQty = 0;
            $totalValue = 0;
        }

        return [
            'quantity' => $totalQty,
            'value' => $totalValue,
            'rate' => ($totalQty > 0) ? ($totalValue / $totalQty) : 0
        ];
    }

    private function normalizeQty($txnItem, $item)
    {
        $txnSi = $txnItem->uom->si_unit ?? 1;
        $baseSi = $item->uom->si_unit ?? 1;
        return $txnItem->quantity * ($txnSi / $baseSi);
    }

    private function normalizeRate($txnItem, $item)
    {
        // Price is usually per Txn Unit. 
        // e.g. Price $10 per Dozen.
        // Base Unit = Piece.
        // Rate per Base Unit = $10 / 12 = $0.833
        
        $price = $txnItem->price ?? 0; // Or calculate from tax? Usually price is unit price pre-tax or post-tax depending on policy. Using raw price for now. 
        
        $txnSi = $txnItem->uom->si_unit ?? 1;
        $baseSi = $item->uom->si_unit ?? 1;
        
        if ($txnSi == 0) return 0;

        return $price / ($txnSi / $baseSi);
    }

    private function aggregateLayers($layers)
    {
        $totalQty = 0;
        $totalValue = 0;

        foreach ($layers as $layer) {
            $totalQty += $layer['qty'];
            $totalValue += ($layer['qty'] * $layer['rate']);
        }

        return [
            'quantity' => $totalQty,
            'value' => $totalValue,
            'rate' => ($totalQty > 0) ? ($totalValue / $totalQty) : 0
        ];
    }
}
