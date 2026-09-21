<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'access_projects', 'guard_name' => 'web']);
        $role = Role::where('name', 'programmer')->first();
        $role?->givePermissionTo($permission);
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'access_projects')->first();
        $role = Role::where('name', 'programmer')->first();

        if ($permission && $role) {
            $role->revokePermissionTo($permission);
        }
    }
};
