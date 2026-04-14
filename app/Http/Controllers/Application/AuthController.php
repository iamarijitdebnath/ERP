<?php

namespace App\Http\Controllers\Application;

use App\Models\System\LoginLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\System\Company;
use App\Models\HRMS\Employee;

class AuthController extends Controller {
    
    public function login(Request $request) {
        
        $companies = Company::where('is_active', '=', true)
                            ->orderBy('name')
                            ->get();

        $response = [
            'companies' => $companies
        ];

        return view('pages.application.auth.login', $response);
    }

    public function login_action(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'company_id' => 'required'
        ]);

        if($validator->fails()) {
            return back()->withErrors($validator->errors())
                         ->with("error", "Done")
                         ->withInput();
        }

        $data = $validator->validated();

        $employee = Employee::where('company_id', '=', $data['company_id'])
                            ->where('email', '=', $data['email'])
                            ->whereNull('deleted_at')
                            ->first();
        
        // if($employee == null) {
        //     return back()->withErrors($validator->errors())
        //                  ->with("error", "Credentials does not match.")
        //                  ->withInput();
        // }
        if (!$employee || !Hash::check($data['password'], $employee->password)) {
                LoginLog::create([
                'email'       => $request->email,
                'status'      => 400, // success
                'ip_address'  => $request->ip(),
                'employee_id' => auth()->user()->id ?? null,
            ]); 
            return back()->withErrors(['email' => 'Credentials do not match.'])
                         ->with("error", "Credentials do not match.")
                         ->withInput($request->except('password'));
        }

        Auth::loginUsingId($employee->id);

        LoginLog::create([
            'email'       => $request->email,
            'status'      => 200, // success
            'ip_address'  => $request->ip(),
            'employee_id' => auth()->user()->id ?? null,
        ]);  

        
        return redirect()->route('application.dashboard')
                         ->with('success', 'Login successfull');
    }

    public function logout_action(Request $request) {
        Auth::logout();
        return redirect()->route('application.auth.login');
    }

}
