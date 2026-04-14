<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\System\Company;

use App\Models\HRMS\Employee;

class DatabaseSeeder extends Seeder {

    use WithoutModelEvents;

    public function run(): void {

        $company = Company::create([
            'name' => 'Science & Surgical',
            'is_active' => true
        ]);

        Employee::create([
            'first_name' => 'Soubhagya',
            'last_name' => 'Biswas',
            'code' => 'E00001',
            'email' => 'soubhagya@thebiswasco.com',
            'password' => Hash::make('Admin@1234'),
            'company_id' => $company->id
        ]);

        $this->call([
            ModuleSeeder::class,
            MenuGroupSeeder::class,
            MenuSeeder::class,
            InventoryItemSeeder::class,
            InventoryTransactionSeeder::class,
        ]);

    }
}
