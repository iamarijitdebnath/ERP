<?php

namespace App\Http\Controllers\HRMS;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class DepartmentController extends Controller
{
    public function index(Request $request) {

        $employee = $request->user();

        if($request->ajax()) {
            $departments = Department::with('company')
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
                'departments'=> $departments
            ];

            return $this->apiResponse(200, "Fetched departments", $response);
        }

        return view('pages.hrms.department.index');
    }

    public function create()
    {
        return view('pages.hrms.department.show');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:hrms_departments,code',
        ]);

        if($validator->fails()) {
            return redirect()->back()
                             ->withErrors($validator->errors())
                             ->withInput()
                             ->with('error', 'Validation Error');
        }

        $data = $validator->validated();

        Department::create(
            array_merge([
                'company_id' => auth()->user()->company_id,
            ], $data)
        );
        return redirect()->route('hrms.department.index')->with('success', 'Department created successfully.');
    }

    public function edit($id) {

        $department = Department::findOrFail($id);
        $response = [
            'department'=> $department
        ];
        return view('pages.hrms.department.show', $response);
    }

    public function update(Request $request, $id) {
        
        $department = Department::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:hrms_departments,code,' . $department->id,
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
            $department->update($data);
            DB::commit();
            return redirect()->route('hrms.department.index')->with('success', 'Department updated successfully.');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->with("error","Update failed")->withInput($request->all());
        }
    }

    public function destroy(Request $request) {
        $id = $request->input('id');
        try {
            $department = Department::findOrFail($id);
            $department->delete();
            return redirect()->route('hrms.department.index')->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }
}
