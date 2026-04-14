<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\System\MenuGroup;
use App\Models\System\Module;

class MenuGroupsController extends Controller
{
    public function index(Request $request){
        $menuGroups = MenuGroup::with('module')->get();
        $response = [
            'menuGroups'=> $menuGroups
        ];
        return view('pages.system.menugroups.index',$response );
    }

    public function create(){
        $allModules = Module::all();

        $response = [
            'allModules'=> $allModules
        ];
        return view('pages.system.menugroups.show', $response);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:6',
            'module_id' => 'required|exists:system_modules,id',
            'sequence' => 'sometimes|integer',
            'is_active' => 'required|boolean',
        ]);

        MenuGroup::create($validated);

        return redirect()->route('system.menugroups.index')->with('success', 'Menu Group created successfully.');
    }

    public function edit(Request $request, MenuGroup $menuGroup){
        $allModules = Module::all();
        // $response=[
        //     'menuGroup' => $menuGroup,
        //     'allModules' => $allModules
        // ];
        return view('pages.system.menugroups.show', compact('menuGroup','allModules'));
    }

    public function update(Request $request, MenuGroup $menuGroup){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:6',
            'module_id' => 'required|exists:system_modules,id',
            'sequence' => 'sometimes|integer',
            'is_active' => 'required|boolean',
        ]);

        $menuGroup->update($validated);

        return redirect()->route('system.menugroups.index')->with('success', 'Menu Group updated successfully.');
    }

    public function destroy(Request $request, MenuGroup $menuGroup){
        $menuGroup->delete();
        return redirect()->route('system.menugroups.index')->with('success', 'Menu Group deleted successfully.');
    }
}
