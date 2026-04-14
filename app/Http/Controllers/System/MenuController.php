<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\System\Menu;
use App\Models\System\MenuGroup;

class MenuController extends Controller
{
    public function index(Request $request){
        $menus = Menu::with('group.module')->get();
        $response = [
            'menus'=> $menus
        ];
        return view('pages.system.menu.index', $response);
    }

    public function create(){
        $menuGroups = MenuGroup::all();
        $response = [
            'menuGroups'=> $menuGroups
        ];
        return view('pages.system.menu.show', $response);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'group_id' => 'required|exists:system_menu_groups,id',
            'sequence' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        Menu::create($validated);

        return redirect()->route('system.menu.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Request $request, Menu $menu){
        $menuGroups = MenuGroup::all();

        $response = [
            'menuGroups'=> $menuGroups,
            'menu'=> $menu
        ];
        return view('pages.system.menu.show', $response);
    }

    public function update(Request $request, Menu $menu){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'group_id' => 'required|exists:system_menu_groups,id',
            'sequence' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $menu->update($validated);

        return redirect()->route('system.menu.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Request $request, Menu $menu){
        $menu->delete();
        return redirect()->route('system.menu.index')->with('success', 'Menu deleted successfully.');
    }
}
