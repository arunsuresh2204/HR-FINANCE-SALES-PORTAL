<?php

namespace Database\Seeders;

use App\Models\OperationalExpenseCategory;
use Illuminate\Database\Seeder;

class OperationalExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Rent',
            'Electricity',
            'Internet & Telephony',
            'Software Licenses',
            'Office Supplies',
            'Insurance',
            'Maintenance & Repairs',
            'Travel & Logistics',
            'Professional Fees',
            'Other',
        ] as $name) {
            OperationalExpenseCategory::firstOrCreate(['name' => $name]);
        }
    }
}
