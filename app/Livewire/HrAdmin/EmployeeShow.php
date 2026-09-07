<?php

namespace App\Livewire\HrAdmin;

use App\Models\Asset;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class EmployeeShow extends Component
{
    public User $user;

    public array $roles = [];

    public string $employment_status = 'active';

    public string $designation = '';

    public string $department = '';

    public ?float $monthly_salary = null;

    public bool $showAssetForm = false;

    #[Validate('required|string|max:255')]
    public string $item_name = '';

    #[Validate('nullable|string|max:255')]
    public string $item_type = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->roles = $user->getRoleNames()->toArray();
        $this->employment_status = $user->employment_status;
        $this->designation = $user->designation ?? '';
        $this->department = $user->department ?? '';
        $this->monthly_salary = $user->monthly_salary ? (float) $user->monthly_salary : null;
    }

    public function saveRoles(): void
    {
        $this->user->syncRoles($this->roles);
        $this->dispatch('toast', message: 'Roles updated.', type: 'success');
    }

    public function saveDetails(): void
    {
        $this->validate([
            'employment_status' => 'required|in:active,on_notice,resigned,offboarded',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'monthly_salary' => 'nullable|numeric|min:0',
        ]);

        $this->user->update([
            'employment_status' => $this->employment_status,
            'designation' => $this->designation,
            'department' => $this->department,
            'monthly_salary' => $this->monthly_salary,
        ]);

        $this->dispatch('toast', message: 'Employee details updated.', type: 'success');
    }

    public function assignAsset(): void
    {
        $this->validate(['item_name' => 'required|string|max:255', 'item_type' => 'nullable|string|max:255']);

        Asset::create([
            'user_id' => $this->user->id,
            'item_name' => $this->item_name,
            'item_type' => $this->item_type,
            'assigned_date' => now()->toDateString(),
            'status' => 'assigned',
        ]);

        $this->reset(['item_name', 'item_type']);
        $this->showAssetForm = false;
        $this->dispatch('toast', message: 'Asset assigned.', type: 'success');
    }

    public function markAssetReturned(Asset $asset): void
    {
        $asset->update(['status' => 'returned', 'return_date' => now()->toDateString()]);
        $this->dispatch('toast', message: 'Asset marked as returned.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr-admin.employee-show', [
            'allRoles' => Role::orderBy('name')->pluck('name'),
            'assets' => Asset::where('user_id', $this->user->id)->orderByDesc('assigned_date')->get(),
            'leaveBalanceUsed' => $this->user->leaveRequests()->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days'),
            'recentAttendance' => $this->user->attendances()->orderByDesc('work_date')->limit(5)->get(),
        ]);
    }
}
