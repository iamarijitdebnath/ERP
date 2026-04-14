<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class MenuGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'module_slug' => 'hrms',
                'items' => [
                    ['name' => 'Employee Management', 'color' => 'FF5733'],
                    ['name' => 'Attendance', 'color' => 'C70039'],
                    ['name' => 'Leave Management', 'color' => '900C3F'],
                    ['name' => 'Payroll', 'color' => '581845'],
                ],
            ],
            [
                'module_slug' => 'inventory',
                'items' => [
                    ['name' => 'Stock Management', 'color' => '33C1FF'],
                    ['name' => 'Warehouse', 'color' => '1A8CFF'],
                    ['name' => 'Product Catalog', 'color' => '005BBB'],
                ],
            ],
            [
                'module_slug' => 'sales',
                'items' => [
                    ['name' => 'Sales Orders', 'color' => '28A745'],
                    ['name' => 'Invoices', 'color' => '1E7A38'],
                ],
            ],
            [
                'module_slug' => 'purchase',
                'items' => [
                    ['name' => 'Purchase Orders', 'color' => 'FFC300'],
                ],
            ],
        ];

        // Loop through modules
        foreach ($groups as $groupData) {

            // Find module by slug
            $module = DB::table('system_modules')
                        ->where('slug', $groupData['module_slug'])
                        ->first();

            if (!$module) continue;

            // Insert menu groups for that module
            foreach ($groupData['items'] as $index => $item) {
                DB::table('system_menu_groups')->insert([
                    'id'         => Str::uuid(),
                    'name'       => $item['name'],
                    'color'      => $item['color'],
                    'sequence'   => $index + 1,
                    'is_active'  => true,
                    'module_id'  => $module->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
