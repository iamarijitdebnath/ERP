<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\Module;

class DashboardController extends Controller {
    
    public function dashboard(Request $request) {
        return view('pages.application.dashboard');
    }
}
