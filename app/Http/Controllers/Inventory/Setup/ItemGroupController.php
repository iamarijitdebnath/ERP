<?php

namespace App\Http\Controllers\Inventory\Setup;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class ItemGroupController extends Controller {
    
    public function index(Request $request) {

        $employee = $request->user();

        if($request->ajax()) {
            $itemGroups = ItemGroup::with('company')
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
                'itemGroups'=> $itemGroups
            ];

            return $this->apiResponse(200, "Fetched Item Groups", $response);
        }

        return view('pages.inventory.item_group.index');
    }

    public function create(Request $request) {
        return view('pages.inventory.item_group.show');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
        ]);

        if($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator->errors())
                             ->withInput()
                             ->with('error', 'Validation Error');
        }

        $data = $validator->validated();

        ItemGroup::create(
            array_merge([
                'company_id' => auth()->user()->company_id,
            ], $request->all())
        );
        return redirect()->route('inventory.item-group.index')->with('success', 'Item Group created successfully.');
    }

    public function edit(ItemGroup $itemGroup) {
        $response = [
            'itemGroup'=> $itemGroup
        ];
        return view('pages.inventory.item_group.show', $response);
    }

    public function update(Request $request, ItemGroup $itemGroup) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
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
            $itemGroup->update($request->all());
            DB::commit();
            return redirect()->route('inventory.item-group.index')->with('success', 'Item Group updated successfully.');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with("error","Update failed")->withInput($request->all());
        }
    }

    public function destroy(ItemGroup $itemGroup) {
        try {
            $itemGroup->delete();
            return redirect()->route('inventory.item-group.index')->with('success', 'Item Group deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
    public function select2(Request $request) {
        $employee = $request->user();

        if($request->ajax()) {
            $itemGroups = ItemGroup::select('id', 'name as text')
                            ->forCurrentCompany()
                            ->when($request->filled('q'), function ($q) use ($request) {
                                $q->whereLike('name', "%{$request->q}%");
                            })
                            ->orderBy('name')
                            ->paginate(10);
                            
            return response()->json([
                'results' => $itemGroups->items(),
                'pagination' => [
                    'more' => $itemGroups->hasMorePages()
                ]
            ]);
        }
    }
}
