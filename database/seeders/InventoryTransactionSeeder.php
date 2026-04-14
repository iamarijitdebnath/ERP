<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\Item;
use App\Models\Inventory\Uom;
use App\Models\System\Company;
use App\Services\Inventory\StockService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InventoryTransactionSeeder extends Seeder
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function run()
    {
        $company = Company::where('name', 'Science & Surgical')->first() ?? Company::first();
        if (!$company) {
            $company = Company::create(['name' => 'Science & Surgical', 'is_active' => true]);
        }
        $companyId = $company->id;

        // Ensure Warehouses
        $wh1 = Warehouse::firstOrCreate(
            ['code' => 'WH001'],
            ['name' => 'Central Warehouse', 'company_id' => $companyId, 'address1' => 'Main St', 'city' => 'Metropolis', 'state' => 'NY']
        );

        $wh2 = Warehouse::firstOrCreate(
            ['code' => 'WH002'],
            ['name' => 'Retail Outlet', 'company_id' => $companyId, 'address1' => 'City Center', 'city' => 'Gotham', 'state' => 'NJ']
        );

        $uom = Uom::firstOrCreate(
            ['name' => 'Piece', 'company_id' => $companyId],
            ['si_unit' => 1, 'category' => 'Count']
        );

        // Get Items (Create if none)
        $items = Item::where('company_id', $companyId)->take(10)->get();
        if ($items->isEmpty()) {
             $items = collect();
             for($i=0; $i<5; $i++) {
                $items->push(Item::create([
                    'name' => 'Test Item ' . $i,
                    'code' => 'TEST-'.$i,
                    'uom_id' => $uom->id,
                    'company_id' => $companyId,
                    'is_active' => true
                ]));
             }
        }

        DB::beginTransaction();
        try {
            $this->command->info('Creating 5 GRNs...');
            for ($i = 0; $i < 5; $i++) {
                $transaction = Transaction::create([
                    'type' => 'grn',
                    'company_id' => $companyId,
                    'from_warehouse_id' => null, 
                    'to_warehouse_id' => $wh1->id,
                    'transaction_date' => Carbon::now()->subDays(rand(10, 20)),
                ]);

                $numItems = rand(1, 3);
                for ($j = 0; $j < $numItems; $j++) {
                    $item = $items->random();
                    $qty = rand(10, 50);
                    $price = rand(100, 500);

                    $txnItem = TransactionItem::create([
                        'inventory_transaction_id' => $transaction->id,
                        'item_id' => $item->id,
                        'uom_id' => $uom->id,
                        'quantity' => $qty,
                        'price' => $price,
                        'cgst' => 0, 'sgst' => 0, 'igst' => 0, 'cess' => 0
                    ]);

                    $this->stockService->addStock(
                        $item->id,
                        $transaction->to_warehouse_id,
                        $qty,
                        $uom->id,
                        null, null, null,
                        $transaction->transaction_date,
                        $transaction->id
                    );
                }
            }

            // 2. Create 5 Stock Transfers
            $this->command->info('Creating 5 Stock Transfers...');
            for ($i = 0; $i < 5; $i++) {
                $transaction = Transaction::create([
                    'type' => 'transfer',
                    'company_id' => $companyId,
                    'from_warehouse_id' => $wh1->id,
                    'to_warehouse_id' => $wh2->id,
                    'transaction_date' => Carbon::now()->subDays(rand(5, 9)),
                ]);

                $numItems = rand(1, 2);
                for ($j = 0; $j < $numItems; $j++) {
                    $item = $items->random();
                    $qty = rand(1, 5); 

                    $txnItem = TransactionItem::create([
                        'inventory_transaction_id' => $transaction->id,
                        'item_id' => $item->id,
                        'uom_id' => $uom->id,
                        'quantity' => $qty,
                        'price' => 0, 
                    ]);

                    try {
                        $this->stockService->transferStock(
                            $item->id,
                            $transaction->from_warehouse_id,
                            $transaction->to_warehouse_id,
                            $qty,
                            $uom->id,
                            null, null, null,
                            $transaction->transaction_date,
                            $transaction->id
                        );
                    } catch (\Exception $e) {
                         $this->command->warn("Transform failed for item {$item->code}: " . $e->getMessage());
                    }
                }
            }

            //GDN
            $this->command->info('Creating 5 GDNs...');
            for ($i = 0; $i < 5; $i++) {
                $useWh = rand(0, 1) ? $wh1->id : $wh2->id; 
                $transaction = Transaction::create([
                    'type' => 'gdn',
                    'company_id' => $companyId,
                    'from_warehouse_id' => $useWh,
                    'to_warehouse_id' => null, 
                    'transaction_date' => Carbon::now()->subDays(rand(0, 4)),
                ]);

                $numItems = rand(1, 2);
                for ($j = 0; $j < $numItems; $j++) {
                    $item = $items->random();
                    $qty = rand(1, 3); 

                    $txnItem = TransactionItem::create([
                        'inventory_transaction_id' => $transaction->id,
                        'item_id' => $item->id,
                        'uom_id' => $uom->id,
                        'quantity' => $qty,
                        'price' => 0, 
                    ]);

                    try {
                        $this->stockService->deductStock(
                            $item->id,
                            $transaction->from_warehouse_id,
                            $qty,
                            $uom->id,
                            null, null, null,
                            $transaction->transaction_date,
                            $transaction->id
                        );
                    } catch (\Exception $e) {
                         $this->command->warn("GDN failed for item {$item->code}: " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            $this->command->info('Inventory Transaction Seeding Completed!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error($e->getMessage());
        }
    }
}
