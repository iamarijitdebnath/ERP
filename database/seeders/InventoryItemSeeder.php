<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Inventory\ItemGroup;
use App\Models\Inventory\Item;
use App\Models\System\Company;
use App\Models\Inventory\Uom;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $companyId = $company ? $company->id : null;

        $uom = Uom::firstOrCreate(
            ['name' => 'Piece', 'company_id' => $companyId],
            ['si_unit' => 1, 'category' => 'Count']
        );

        $lastItem = Item::where('company_id', $companyId)
            ->where('code', 'like', 'I%')
            ->orderBy('code', 'desc')
            ->first();

        $counter = $lastItem ? intval(substr($lastItem->code, 1)) : 0;


        $data = [
            'Anaesthesia Machine' => [
                'Ventilator Anaesthesia Machine',
                'Boyles Apparatus',
            ],
            'Hospital Bed' => [
                'Upgraded 10 Parts ICU Bed Manual',
                'Attendant Bed',
                'ABS HEAD AND LEG BOW FOWLER(UPGRADED) BED',
                'Motorized ICU Bed',
                'Fowler Bed Acuator',
                'ABS Head And Leg Bow Semi Fowler Bed',
                'Medical Baby Cot Bed',
                'Abs Head and Leg Bow Fowler Bed',
                'Fowler Bed Manual',
                'Children Medical Bed',
                'Modified Two Section Bed',
            ],
            'Hospital Equipment' => [
                'FUMIGATOR WITH DIGITAL TIMMER',
                'FOGGER MACHINE WITH DIGITAL TIMMER',
                'Magnamed Fleximag ICU Ventilator',
            ],
            'Hospital Furniture' => [
                'Electric Blood Bonor Chair',
                'Examination Couch',
                'Scrub Station',
                'Monitor Trolley',
                'Bedside Locker ABS',
                'BEDSIDE LOCKER SS TOP',
                'Cadaver Tank',
                'Three Fold Bed Side Screen',
                'Emergency Foldable Rescue Stretcher',
            ],
            'Modular Operation Theater' => [
                'Hospital Modular Operation Theater',
            ],
            'Operation Theater Light' => [
                'Ceiling OT Light (DELTA 500)',
                'LED Operation Theater Light',
            ],
            'OT Table' => [
                'Manual Ot Table',
                'Obstetric Labour Table',
                'Electric OT Table',
                'Electro Hydraulic Operating Table',
                'Operation Theatre Table With Remote And CPR',
                'OT Table',
            ],
            'Hospital Holloware' => [
                'Three Bucket Mop Trolley',
                'Hospital Dressing Drum',
            ],
            'X-Ray View Box' => [
                'Double X-Ray View Box',
                'Single X-Ray View Box',
            ],
            'Medical Sterilizers' => [
                'Automatic Horizontal Sterilizer',
                'Autoclave Vertical',
                'Horizontal Sterilizer',
            ],
            'Medical Table' => [
                'Fixed Labour Table',
                'ExaminatioN TA',
                'C-Arm Electric OT Table',
                'Overbed Table',
                'Adjustable Food Table',
                'Manual Food Table',
            ],
            'Overbed Table' => [
                'Overbed Table Sunmica Top',
                'GEAR MECHANISM OVERBED TABLE LAMINATED TOP',
                'Gear Mechanism Overbed Table',
            ],
            'Medical Equipment Trolley' => [
                'Oxygen Cylinder Trolley',
                'Instrument Trolley',
                'Mayo Trolley',
                'Hot Food Trolley',
                'Jumbo Oxygen Cylinder Trolley',
            ],
            'Medical Stand' => [
                'Saline Stand',
                'Bowl Stand',
            ],
            'Medical Stool' => [
                'Square Medical Stool',
                'Four Leg Revolving Stool',
                'Revolving Stool',
            ],
            'Stretcher Trolley' => [
                'Emergency Recovery Trolley',
                'SS Stretcher Trolley',
                'SCOOP STRETCHER',
                'Emergency Stretcher Trolley',
            ],
            'Medical Trolley' => [
                'Curved Trolley',
                'Canvas Linen Trolley',
                'Bedside Trolley',
            ],
            'Laboratory Equipment' => [
                'Stainless Steel Solid Linen Trolley',
                'Autoclave Sterilizer',
                'Instrument Sterilizer',
                'Crash Cart',
            ],
            'Medical Pump' => [
                'Syringe Pump',
            ],
            'Monitors and ECG Machines' => [
                'Patient Monitor (Make Nidek Model Bravo 8)',
                'Patient Monitor (Make Nidek Model Horizon) 12.1 Inch',
                'Patient Monitor (Make Nidek Model Bravo 10)',
                'Patient Monitor (Make Nidek Model Horizon Eco)',
                'ECG Machine (Make Nidek Model 712) 12 Channel',
            ],
            'Public Place Seating Chair' => [
                'Public Place Seating Chair 3 Seater',
                'Public Place Seating Chair 4 Seater',
            ],
            'WARD FURNITURE' => [
                'Kick Bucket',
                'Wheel Chair',
                'Cloth Locker',
                'Waste Container',
            ],
        ];

        foreach ($data as $groupName => $items) {
            $group = ItemGroup::firstOrCreate(
                [
                    'name' => $groupName, 
                    'company_id' => $companyId
                ],
                [
                    'description' => $groupName . ' Group',
                    'is_active' => true,
                ]
            );

            foreach ($items as $itemName) {
                $counter++;
                $itemCode = 'I' . str_pad($counter, 4, '0', STR_PAD_LEFT);

                Item::firstOrCreate(
                    [
                        'name' => $itemName,
                        'company_id' => $companyId
                    ],
                    [
                        'code' => $itemCode,
                        'sku' => $itemCode, 
                        'group_id' => $group->id,
                        'description' => $itemName,
                        'is_active' => true,
                        'uom_id' => $uom->id,
                    ]
                );
            }
        }
    }
}
