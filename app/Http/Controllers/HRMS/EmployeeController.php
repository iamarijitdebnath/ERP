<?php

namespace App\Http\Controllers\HRMS;

use App\Http\Controllers\Controller;
use App\Models\HRMS\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\HRMS\EmployeePermission;
use App\Models\System\Company;
use App\Models\HRMS\Department;
use App\Models\System\Module;


class EmployeeController extends Controller {
   
    public function index(Request $request) {
        $user = $request->user();

        if ($request->ajax()) {
            $employees = Employee::with(['company', 'department', 'reportingTo'])
                ->forCurrentCompany()
                ->when($request->filled('q'), function ($q) use ($request) {
                    $q->where(function ($subQ) use ($request) {
                        $term = "%{$request->q}%";
                        $subQ->whereLike('first_name', $term)
                             ->orWhereLike('last_name', $term)
                             ->orWhereLike('code', $term)
                             ->orWhereLike('email', $term);
                    });
                })
                ->orderBy('id')
                ->paginate(10)
                ->onEachSide(1);

            
                $response = [
                    'employees' => $employees
                ];

            return $this->apiResponse(200, "Fetched employee", $response);
        }

        return view('pages.hrms.employee.index');
    }

    public function create() {
        $departments = Department::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('first_name')->get();
        $response = [
            'departments'=> $departments,
            'employees'=> $employees
        ];
        return view('pages.hrms.employee.show', $response);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'salutation' => 'required|in:Mr,Ms,Mrs',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:hrms_employees,code',
            'email' => 'required|email|max:150|unique:hrms_employees,email',
            'password' => 'nullable|string|min:8', 
            'gender' => 'nullable|in:male,female,other',
            'employment_type' => 'required|in:internship,contractual,part-time,full-time',
            'payment_type' => 'required|in:salary,wage',
            'date_of_birth' => 'nullable|date',
            'date_of_joining' => 'nullable|date',
            'is_active' => 'boolean',
            'department_id' => 'nullable|exists:hrms_departments,id',
            'under_id' => 'nullable|exists:hrms_employees,id',
        ]);

        if (empty($validated['password'])) {
            $validated['password'] = Hash::make('password');
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        $validated['is_active'] = $request->has('is_active') ? $request->is_active : true;
        $validated['company_id'] = auth()->user()->company_id;

        Employee::create($validated);

        return redirect()->route('hrms.employee.index')->with('success', 'Employee created successfully.');
    }

    public function edit($id) {
        $crudEmployee = Employee::findOrFail($id);
        $departments = Department::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->where('id', '!=', $id)->orderBy('first_name')->get();
        $response = [
            'crudEmployee'=> $crudEmployee,
            'departments'=> $departments,
            'employees'=> $employees
        ];
        return view('pages.hrms.employee.show', $response);
    }

    public function update(Request $request, $id) {
        $employee = Employee::findOrFail($id);
        $validated = $request->validate([
            'salutation' => 'required|in:Mr,Ms,Mrs',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:hrms_employees,code,' . $employee->id,
            'email' => 'required|email|max:150|unique:hrms_employees,email,' . $employee->id,
            'password' => 'nullable|string|min:8',
            'gender' => 'nullable|in:male,female,other',
            'employment_type' => 'required|in:internship,contractual,part-time,full-time',
            'payment_type' => 'required|in:salary,wage',
            'date_of_birth' => 'nullable|date',
            'date_of_joining' => 'nullable|date',
            'is_active' => 'boolean',
            'department_id' => 'nullable|exists:hrms_departments,id',
            'under_id' => 'nullable|exists:hrms_employees,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? $request->is_active : false;

        $employee->update($validated);

        return redirect()->route('hrms.employee.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id) {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('hrms.employee.index')->with('success', 'Employee deleted successfully.');
    }

    public function permission($id) {
        $employee = Employee::findOrFail($id);
        $modules = Module::with(['menuGroups.menus.permissions' => function($query) use ($id) {
            $query->where('employee_id', $id);
        }])->where('is_active', true)->orderBy('sequence')->get();

        $response = [
            'modules'=> $modules,
            'employee'=> $employee
        ];

        return view('pages.hrms.employee.permission', $response);
    }

    public function savePermission(Request $request, $id) {
        $employee = Employee::findOrFail($id);
        $permissions = $request->input('permissions', []);
        foreach ($permissions as $menuId => $perms) {
           EmployeePermission::updateOrCreate(
                ['employee_id' => $id, 'menu_id' => $menuId],
                [
                    'can_create' => isset($perms['can_create']),
                    'can_read' => isset($perms['can_read']),
                    'can_update' => isset($perms['can_update']),
                    'can_delete' => isset($perms['can_delete']),
                ]
            );
        }

        return redirect()->route('hrms.employee.index')->with('success', 'Permissions updated successfully.');
    }
}
