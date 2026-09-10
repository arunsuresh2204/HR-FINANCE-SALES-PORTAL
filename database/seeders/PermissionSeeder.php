<?php

namespace Database\Seeders;

use App\Support\FeatureCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(FeatureCatalog::FEATURES) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (FeatureCatalog::DEFAULT_ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                $role->syncPermissions($permissions);
            }
        }
    }
}
