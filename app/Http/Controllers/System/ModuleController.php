<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request){
        $module = Module::select('id', 'name','is_active')->orderBy('sequence')->get();
        $response = [
            "module"=> $module
        ];

        // return($response);
        return view('pages.system.module.index',$response);
    }

    public function create(){
        return view('pages.system.module.show');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:system_modules,slug',
            'is_active' => 'required|boolean',
        ]);

        Module::create($validated);

        return redirect()->route('system.module.index')->with('success', 'Module created successfully.');
    }

    public function edit(Request $request, Module $module){
        $response = [
            'module'=> $module
        ];
        return view('pages.system.module.show', $response);
    }

    public function update(Request $request, Module $module){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:system_modules,slug,' . $module->id,
            'is_active' => 'required|boolean',
        ]);

        $module->update($validated);

        return redirect()->route('system.module.index')->with('success', 'Module updated successfully.');
    }

    public function destroy(Request $request, Module $module){
        $module->delete();
        return redirect()->route('system.module.index')->with('success', 'Module deleted successfully.');
    }
}
