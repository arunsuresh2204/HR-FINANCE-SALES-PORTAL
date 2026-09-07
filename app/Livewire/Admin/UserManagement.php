<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    public array $editingRoles = [];

    public function toggleEdit(int $userId): void
    {
        if (isset($this->editingRoles[$userId])) {
            unset($this->editingRoles[$userId]);

            return;
        }

        $this->editingRoles[$userId] = User::find($userId)->getRoleNames()->toArray();
    }

    public function saveRoles(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->syncRoles($this->editingRoles[$userId] ?? []);
        unset($this->editingRoles[$userId]);
        $this->dispatch('toast', message: "Roles updated for {$user->name}.", type: 'success');
    }

    public function resetPassword(int $userId): void
    {
        $user = User::findOrFail($userId);
        $tempPassword = Str::password(12);
        $user->update(['password' => Hash::make($tempPassword)]);

        session()->flash('temp_password', $tempPassword);
        session()->flash('temp_password_for', $user->name);
        $this->dispatch('toast', message: "Password reset for {$user->name}.", type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => User::orderBy('name')->get(),
            'allRoles' => Role::orderBy('name')->pluck('name'),
        ]);
    }
}
