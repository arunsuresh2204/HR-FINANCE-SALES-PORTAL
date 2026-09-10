<?php

namespace App\Livewire\Admin;

use App\Support\FeatureCatalog;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleManagement extends Component
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    public bool $showAddForm = false;

    public function openAddForm(): void
    {
        $this->reset(['name']);
        $this->resetValidation();
        $this->showAddForm = true;
    }

    public function addRole(): void
    {
        $this->validate();

        $slug = Str::slug($this->name, '_');

        if ($slug === '') {
            $this->addError('name', 'Enter a valid role name.');

            return;
        }

        if (Role::where('name', $slug)->exists()) {
            $this->addError('name', 'A role with this name already exists.');

            return;
        }

        Role::create(['name' => $slug, 'guard_name' => 'web']);

        $this->reset(['name']);
        $this->showAddForm = false;
        $this->dispatch('toast', message: 'Role created. Assign it to users from Users & Roles.', type: 'success');
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, RoleSeeder::ROLES, true)) {
            $this->dispatch('toast', message: 'Core system roles can\'t be deleted — they\'re wired into the app.', type: 'error');

            return;
        }

        if ($role->users()->count() > 0) {
            $this->dispatch('toast', message: 'Cannot delete a role assigned to users — unassign them first.', type: 'error');

            return;
        }

        $role->delete();
        $this->dispatch('toast', message: 'Role deleted.', type: 'success');
    }

    public function togglePermission(int $roleId, string $permission): void
    {
        if (! array_key_exists($permission, FeatureCatalog::FEATURES)) {
            return;
        }

        $role = Role::findOrFail($roleId);

        // The super_admin role must always keep access to the Super Admin
        // section itself — otherwise an admin could lock everyone
        // (including themselves) out of this very screen with no UI path
        // back in.
        if ($role->name === 'super_admin' && $permission === 'access_super_admin') {
            $this->dispatch('toast', message: 'Super Admin must always keep Super Admin access.', type: 'error');

            return;
        }

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
        } else {
            $role->givePermissionTo($permission);
        }
    }

    public function render()
    {
        $roles = Role::with('permissions')->orderBy('name')->get()->map(function (Role $role) {
            $role->userCount = $role->users()->count();
            $role->isCore = in_array($role->name, RoleSeeder::ROLES, true);
            $role->grantedPermissions = $role->permissions->pluck('name')->all();

            return $role;
        });

        return view('livewire.admin.role-management', [
            'roles' => $roles,
            'features' => FeatureCatalog::FEATURES,
            'featureDescriptions' => FeatureCatalog::FEATURE_DESCRIPTIONS,
        ]);
    }
}
