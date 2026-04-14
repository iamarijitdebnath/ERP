<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\System\Module;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $modules = [
            ['name' => 'HRMS',      'slug' => 'hrms', 'icon'=>'fa-solid fa-user'],
            ['name' => 'Inventory', 'slug' => 'inventory', 'icon'=>'fa-solid fa-warehouse'],
            ['name' => 'Sales',     'slug' => 'sales', 'icon'=>'fa-solid fa-cart-shopping'],
            ['name' => 'Purchase',  'slug' => 'purchase', 'icon'=>'fa-solid fa-basket-shopping'],
        ];
        $sequence = 1;
        foreach ($modules as $module) {
            DB::table('system_modules')->insert([
                'id'        => Str::uuid(),
                'name'      => $module['name'],
                'slug'      => $module['slug'],
                'icon'      => $module['icon'],
                'sequence'  => $sequence++,
                'is_active' => true,
                'created_at'=> now(),
                'updated_at'=> now(),
            ]);
        }
    }
}
