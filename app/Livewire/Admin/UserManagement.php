<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    public array $editingRoles = [];

    public bool $showPasswordForm = false;

    public ?int $passwordUserId = null;

    public string $passwordUserName = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $new_password = '';

    public string $new_password_confirmation = '';

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

    public function openPasswordForm(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->resetValidation();
        $this->passwordUserId = $user->id;
        $this->passwordUserName = $user->name;
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->showPasswordForm = true;
    }

    public function generateRandomPassword(): void
    {
        $password = Str::password(12);
        $this->new_password = $password;
        $this->new_password_confirmation = $password;
    }

    public function updatePassword(): void
    {
        $this->validate();

        $user = User::findOrFail($this->passwordUserId);
        $user->update(['password' => Hash::make($this->new_password)]);

        session()->flash('temp_password', $this->new_password);
        session()->flash('temp_password_for', $user->name);

        $this->showPasswordForm = false;
        $this->reset(['new_password', 'new_password_confirmation', 'passwordUserId', 'passwordUserName']);

        $this->dispatch('toast', message: "Password updated for {$user->name}.", type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => User::orderBy('name')->get(),
            'allRoles' => Role::orderBy('name')->pluck('name'),
        ]);
    }
}
