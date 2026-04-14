<?php

namespace App\Http\Middleware\HRMS\Employee;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\System\Module;

class AuthenticateMiddleware {

    public function handle(Request $request, Closure $next): Response {

        $employee = Auth::user();
        
        if($employee == null) {
            return redirect()->route('application.auth.login');
        }
        $modules = Module::where('is_active', '=', true)
                         ->orderBy('sequence')
                         ->get();

        View::share('employee', $employee->toArray());
        View::share('modules', $modules);

        return $next($request);
    }
}
