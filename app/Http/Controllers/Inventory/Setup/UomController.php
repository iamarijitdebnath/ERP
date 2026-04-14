<?php

namespace App\Http\Controllers\Inventory\Setup;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\Item;
use Validator;

class UomController extends Controller {
    
    public function index(Request $request) {

        $employee = $request->user();

        if($request->ajax()) {
            $uoms = Uom::with('company')
                                     ->whereHas('company', function($q) use($employee) {
                                        $q->where('is_active', '=', 1)
                                          ->where('id', '=', $employee->company_id);
                                     })
                                     ->when($request->filled('q'), function ($q) use ($request) {
                                        $q->where(function ($q2) use ($request) {
                                            $q2->whereLike('name', "%{$request->q}%");
                                        });
                                     })
                                     ->orderBy('name')
                                     ->paginate(10)
                                     ->onEachSide(1);
            $response = [
                'uoms'=> $uoms
            ];

            return $this->apiResponse(200, "Fetched UOMs", $response);
        }

        return view('pages.inventory.uom.index');
    }

    public function create(Request $request) {
        $categories = Uom::forCurrentCompany()
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category');
        
        return view('pages.inventory.uom.show', compact('categories'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'si_unit' => 'required|string|max:255',
        ]);

        if($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator->errors())
                             ->withInput()
                             ->with('error', 'Validation Error');
        }

        $data = $validator->validated();

        Uom::create(
            array_merge([
                'company_id' => auth()->user()->company_id,
            ], $request->all())
        );
        return redirect()->route('inventory.uom.index')->with('success', 'UOM created successfully.');
    }

    public function edit(Uom $uom) {
        $categories = Uom::forCurrentCompany()
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category');
                        
        $response = [
            'uom'=> $uom,
            'categories' => $categories
        ];
        return view('pages.inventory.uom.show', $response);
    }

    public function update(Request $request, Uom $uom) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'si_unit' => 'required|string|max:255',
        ]);

        if($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator->errors())
                             ->withInput()
                             ->with('error', 'Validation Error');
        }

        DB::beginTransaction();
        try {
            $data = $validator->validated();
            $uom->update($request->all());
            DB::commit();
            return redirect()->route('inventory.uom.index')->with('success', 'UOM updated successfully.');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with("error","Update failed")->withInput($request->all());
        }
    }

    public function destroy(Uom $uom) {
        try {
            $uom->delete();
            return redirect()->route('inventory.uom.index')->with('success', 'UOM deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
    public function select2(Request $request) {
        $employee = $request->user();

        if($request->ajax()) {
            $uoms = Uom::select('id', DB::raw("name as text"))
                            ->forCurrentCompany()
                            ->when($request->filled('item_id'), function($q) use ($request) {
                                $item = Item::find($request->item_id);
                                if ($item && $item->uom) { 
                                    $q->where('category', $item->uom->category);
                                }
                            })
                            ->when($request->filled('q'), function ($q) use ($request) {
                                $q->where(function ($q2) use ($request) {
                                    $q2->whereLike('name', "%{$request->q}%")
                                       ->orWhereLike('si_unit', "%{$request->q}%");
                                });
                            })
                            ->orderBy('name')
                            ->paginate(10);
                            
            return response()->json([
                'results' => $uoms->items(),
                'pagination' => [
                    'more' => $uoms->hasMorePages()
                ]
            ]);
        }
    }
}
