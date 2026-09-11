<?php

use App\Support\FeatureCatalog;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Introduces a generic "Team Manager" permission that replaces the
     * hardcoded per-role checks (isManager()/isSalesManager()) previously
     * used to decide whether a role sees its reports' data. Grants it to
     * every role that already relied on that hardcoded behavior, so
     * existing deployments — including any custom role like "Manager-
     * Sales" created through Functional Roles before this permission
     * existed — keep working exactly as before.
     */
    public function up(): void
    {
        Permission::firstOrCreate(['name' => FeatureCatalog::TEAM_MANAGER_PERMISSION, 'guard_name' => 'web']);

        foreach (['super_admin', 'manager_engineering', 'manager_sales'] as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role && ! $role->hasPermissionTo(FeatureCatalog::TEAM_MANAGER_PERMISSION)) {
                $role->givePermissionTo(FeatureCatalog::TEAM_MANAGER_PERMISSION);
            }
        }
    }

    public function down(): void
    {
        $permission = Permission::where('name', FeatureCatalog::TEAM_MANAGER_PERMISSION)->first();

        if ($permission) {
            $permission->delete();
        }
    }
};
