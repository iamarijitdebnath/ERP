<?php

namespace App\Http\Controllers\Inventory\Setup;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class WarehouseController extends Controller {
    
    public function index(Request $request) {

        $employee = $request->user();

        if($request->ajax()) {
            $warehouses = Warehouse::with('company')
                                     ->whereHas('company', function($q) use($employee) {
                                        $q->where('is_active', '=', 1)
                                          ->where('id', '=', $employee->company_id);
                                     })
                                     ->when($request->filled('q'), function ($q) use ($request) {
                                        $q->where(function ($q2) use ($request) {
                                            $q2->whereLike('name', "%{$request->q}%")
                                            ->orWhereLike('code', "%{$request->q}%");
                                        });
                                     })
                                     ->orderBy('name')
                                     ->paginate(10)
                                     ->onEachSide(1);
            $response = [
                'warehouses'=> $warehouses
            ];

            return $this->apiResponse(200, "Fetched warehouses", $response);
        }

        return view('pages.inventory.warehouse.index');
    }

    public function create(Request $request) {
        return view('pages.inventory.warehouse.show');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:inventory_warehouses,code',
            'address1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
        ]);

        if($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator->errors())
                             ->withInput()
                             ->with('error', 'Validation Error');
        }

        $data = $validator->validated();

        Warehouse::create(
            array_merge([
                'company_id' => auth()->user()->company_id,
            ], $request->all())
        );
        return redirect()->route('inventory.warehouse.index')->with('success', 'Warehouse created successfully.');
    }

    public function edit(Warehouse $warehouse) {
        $response = [
            'warehouse'=> $warehouse
        ];
        return view('pages.inventory.warehouse.show', $response);
    }

    public function update(Request $request, Warehouse $warehouse) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:inventory_warehouses,code,' . $warehouse->id,
            'address1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
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
            $warehouse->update($request->all());
            DB::commit();
            return redirect()->route('inventory.warehouse.index')->with('success', 'Warehouse updated successfully.');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with("error","Update failed")->withInput($request->all());
        }
    }

    public function destroy(Warehouse $warehouse) {
        try {
            $warehouse->delete();
            return redirect()->route('inventory.warehouse.index')->with('success', 'Warehouse deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
}
