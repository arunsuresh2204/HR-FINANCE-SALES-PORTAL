<?php

namespace App\Livewire\HrAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class EmployeeIndex extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('nullable|string|max:255')]
    public string $designation = '';

    #[Validate('nullable|string|max:255')]
    public string $department = '';

    #[Validate('required|date')]
    public string $date_of_joining = '';

    #[Validate('array')]
    public array $roles = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openForm(): void
    {
        $this->reset(['name', 'email', 'designation', 'department', 'roles']);
        $this->date_of_joining = now()->toDateString();
        $this->showForm = true;
    }

    public function createEmployee(): void
    {
        $this->validate();

        $lastCode = User::orderByDesc('id')->value('employee_code');
        $nextNumber = $lastCode ? ((int) Str::afterLast($lastCode, '-')) + 1 : 1;

        $tempPassword = Str::password(12);

        $user = User::create([
            'employee_code' => 'EMP-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT),
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($tempPassword),
            'designation' => $this->designation,
            'department' => $this->department,
            'date_of_joining' => $this->date_of_joining,
            'employment_status' => 'active',
        ]);

        if (! empty($this->roles)) {
            $user->assignRole($this->roles);
        }

        $this->showForm = false;
        session()->flash('temp_password', $tempPassword);
        session()->flash('temp_password_for', $user->name);
        $this->dispatch('toast', message: "Employee {$user->name} onboarded successfully.", type: 'success');
    }

    public function render()
    {
        return view('livewire.hr-admin.employee-index', [
            'employees' => User::query()
                ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%")->orWhere('employee_code', 'like', "%{$this->search}%")))
                ->orderBy('name')
                ->paginate(10),
            'allRoles' => Role::orderBy('name')->pluck('name'),
        ]);
    }
}
