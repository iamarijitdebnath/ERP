<?php

namespace App\Http\Controllers\Inventory\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ItemRequest;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemGroup;
use App\Models\Inventory\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ItemController extends Controller {
    
    public function index(Request $request) {
        if($request->ajax()) {
            $items = Item::with(['company', 'group', 'uom'])
                         ->forCurrentCompany()
                         ->whereHas('company', fn($q) => $q->where('is_active', 1))
                         ->when($request->filled('q'), fn($q) => $q->search($request->q))
                         ->orderBy('name')
                         ->paginate($request->input('limit', 25))
                         ->onEachSide(1);

            return $this->apiResponse(200, "Fetched Items", ['items' => $items]);
        }

        return view('pages.inventory.item.index');
    }

    public function variantIndex(Request $request) {
        if($request->ajax()) {
            $items = Item::with(['company', 'group', 'uom'])
                         ->forCurrentCompany()
                         ->whereHas('company', fn($q) => $q->where('is_active', 1))
                         ->whereNotNull('master_id')
                         ->when($request->filled('q'), fn($q) => $q->search($request->q))
                         ->orderBy('name')
                         ->paginate(10)
                         ->onEachSide(1);

            return $this->apiResponse(200, "Fetched Variants", ['items' => $items]);
        }

        return view('pages.inventory.item.variant.index');
    }

    public function create(Request $request) {
        $data = $this->getDependencyData();
        return view('pages.inventory.item.show', $data);
    }

    public function store(ItemRequest $request) {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $itemData = collect($data)->except('variants')->toArray();
            $itemData['is_active'] = $data['is_active'] ?? 1;
            $itemData['company_id'] = auth()->user()->company_id;

            $item = Item::create($itemData);

            if (!empty($data['variants'])) {
                $this->syncVariants($item, $data['variants']);
            }

            DB::commit();
            return redirect()->route('inventory.item.index')->with('success', 'Item created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Item creation failed: ' . $e->getMessage())
                             ->withInput();
        }
    }

    public function edit(Item $item) {
        $data = $this->getDependencyData($item);
        $data['item'] = $item;
        return view('pages.inventory.item.show', $data);
    }

    public function update(ItemRequest $request, Item $item) {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $itemData = collect($data)->except('variants')->toArray();
            $item->update($itemData);
            if (isset($data['variants'])) {
                $this->syncVariants($item, $data['variants'] ?? []);
            }

            DB::commit();
            return redirect()->route('inventory.item.index')->with('success', 'Item updated successfully.');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with("error","Update failed: " . $e->getMessage())->withInput($request->all());
        }
    }

    public function destroy(Item $item) {
        try {
            $item->delete();
            return redirect()->route('inventory.item.index')->with('success', 'Item deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    public function select2Item(Request $request) {
        if($request->ajax()) {
            $items = Item::select('id', 'name as text', 'code', 'tracking', 'has_expiry')
                            ->forCurrentCompany()
                            ->when($request->filled('q'), fn($q) => $q->search($request->q))
                            ->when($request->filled('has_expiry'), fn($q) => $q->where('has_expiry', $request->boolean('has_expiry')))
                            ->orderBy('name')
                            ->paginate(10);
                            
            return response()->json([
                'results' => $items->items(),
                'pagination' => ['more' => $items->hasMorePages()]
            ]);
        }
    }

    private function syncVariants(Item $parentItem, array $variantsInput)
    {
        $existingVariantIds = $parentItem->variants()->pluck('id')->toArray();
        $processedVariantIds = [];

        foreach ($variantsInput as $variantData) {
            if (empty($variantData['name']) || empty($variantData['code'])) {
                continue;
            }

            $variantAttributes = [
                'name' => $variantData['name'],
                'code' => $variantData['code'],
                'sku' => $variantData['sku'] ?? null,
                'group_id' => $parentItem->group_id,
                'uom_id' => $parentItem->uom_id,
                'is_active' => $parentItem->is_active,
                'company_id' => $parentItem->company_id,
                'master_id' => $parentItem->id,
            ];

            if (!empty($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                $variant = Item::find($variantData['id']);
                if ($variant) {
                    $variant->update($variantAttributes);
                    $processedVariantIds[] = $variantData['id'];
                }
            } else {
                $newVariant = Item::create($variantAttributes);
                $processedVariantIds[] = $newVariant->id;
            }
        }
        $variantsToDelete = array_diff($existingVariantIds, $processedVariantIds);
        if (!empty($variantsToDelete)) {
            Item::whereIn('id', $variantsToDelete)->delete();
        }
    }

    private function getDependencyData(?Item $currentItem = null): array
    {
        $companyId = auth()->user()->company_id;
        
        return [
            'groups' => ItemGroup::forCurrentCompany()->get(),
            'uoms' => Uom::forCurrentCompany()->get(),
            'items' => Item::forCurrentCompany()
                          ->when($currentItem, fn($q) => $q->where('id', '!=', $currentItem->id))
                          ->get()
        ];
    }
}
