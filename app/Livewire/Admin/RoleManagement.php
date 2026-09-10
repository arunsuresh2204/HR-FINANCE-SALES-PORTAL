<?php

namespace App\Livewire\Admin;

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

    public function render()
    {
        $roles = Role::orderBy('name')->get()->map(function (Role $role) {
            $role->userCount = $role->users()->count();
            $role->isCore = in_array($role->name, RoleSeeder::ROLES, true);

            return $role;
        });

        return view('livewire.admin.role-management', [
            'roles' => $roles,
        ]);
    }
}
