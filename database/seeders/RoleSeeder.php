<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public const ROLES = [
        'super_admin',
        'manager_engineering',
        'team_lead',
        'hr_admin',
        'finance_admin',
        'sales_exec',
        'programmer',
        'marketer',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
