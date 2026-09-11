<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'access_projects', 'guard_name' => 'web']);

        foreach (['super_admin', 'manager_engineering', 'team_lead_it'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $role?->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'access_projects')->first();

        if ($permission) {
            foreach (Role::all() as $role) {
                $role->revokePermissionTo($permission);
            }

            $permission->delete();
        }
    }
};
