<?php

namespace App\Livewire\HrAdmin;

use App\Models\Asset;
use App\Models\CompanyDocument;
use App\Models\EmployeeDocument;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class EmployeeShow extends Component
{
    use WithFileUploads;

    public User $user;

    public array $roles = [];

    public string $employment_status = 'active';

    public string $employment_type = 'full_time';

    public string $designation = '';

    public string $department = '';

    public ?float $monthly_salary = null;

    public ?int $manager_id = null;

    public array $additional_manager_ids = [];

    public string $scheduled_login_time = '';

    public string $scheduled_logoff_time = '';

    public bool $showAssetForm = false;

    #[Validate('required|string|max:255')]
    public string $item_name = '';

    #[Validate('nullable|string|max:255')]
    public string $item_type = '';

    public bool $showPromotionForm = false;

    #[Validate('required|string|max:255')]
    public string $new_designation = '';

    #[Validate('nullable|string|max:255')]
    public string $new_department = '';

    #[Validate('required|date')]
    public string $effective_date = '';

    #[Validate('nullable|string|max:500')]
    public string $promotion_notes = '';

    #[Validate('nullable|file|max:5120|mimes:jpg,jpeg,png,pdf')]
    public $promotionCertificate = null;

    public bool $showOfferLetterForm = false;

    #[Validate('required|file|max:10240|mimes:jpg,jpeg,png,pdf')]
    public $offerLetterFile = null;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->roles = $user->getRoleNames()->toArray();
        $this->employment_status = $user->employment_status;
        $this->employment_type = $user->employment_type;
        $this->designation = $user->designation ?? '';
        $this->department = $user->department ?? '';
        $this->monthly_salary = $user->monthly_salary ? (float) $user->monthly_salary : null;
        $this->manager_id = $user->manager_id;
        $this->additional_manager_ids = $user->additionalManagers()->pluck('users.id')->all();
        $this->scheduled_login_time = $user->scheduled_login_time ? substr($user->scheduled_login_time, 0, 5) : '';
        $this->scheduled_logoff_time = $user->scheduled_logoff_time ? substr($user->scheduled_logoff_time, 0, 5) : '';
    }

    public function saveReporting(): void
    {
        $this->validate([
            'manager_id' => 'nullable|exists:users,id',
            'additional_manager_ids' => 'array',
            'additional_manager_ids.*' => 'exists:users,id',
        ]);

        if ($this->manager_id === $this->user->id) {
            $this->addError('manager_id', 'An employee cannot report to themselves.');

            return;
        }

        $this->user->update(['manager_id' => $this->manager_id]);
        $this->user->additionalManagers()->sync(array_diff($this->additional_manager_ids, [$this->user->id]));

        $this->dispatch('toast', message: 'Reporting line updated.', type: 'success');
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
            'employment_type' => 'required|in:full_time,trainee_paid,trainee_unpaid,intern',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'monthly_salary' => 'nullable|numeric|min:0',
            'scheduled_login_time' => 'nullable|date_format:H:i',
            'scheduled_logoff_time' => 'nullable|date_format:H:i',
        ]);

        $this->user->update([
            'employment_status' => $this->employment_status,
            'employment_type' => $this->employment_type,
            'designation' => $this->designation,
            'department' => $this->department,
            'monthly_salary' => $this->monthly_salary,
            'scheduled_login_time' => $this->scheduled_login_time ?: null,
            'scheduled_logoff_time' => $this->scheduled_logoff_time ?: null,
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

    public function openPromotionForm(): void
    {
        $this->reset(['new_designation', 'new_department', 'effective_date', 'promotion_notes', 'promotionCertificate']);
        $this->new_designation = $this->user->designation ?? '';
        $this->new_department = $this->user->department ?? '';
        $this->effective_date = now()->toDateString();
        $this->resetValidation();
        $this->showPromotionForm = true;
    }

    public function addPromotion(): void
    {
        $this->validate([
            'new_designation' => 'required|string|max:255',
            'new_department' => 'nullable|string|max:255',
            'effective_date' => 'required|date',
            'promotion_notes' => 'nullable|string|max:500',
            'promotionCertificate' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);

        Promotion::create([
            'user_id' => $this->user->id,
            'created_by' => Auth::id(),
            'previous_designation' => $this->user->designation,
            'new_designation' => $this->new_designation,
            'previous_department' => $this->user->department,
            'new_department' => $this->new_department ?: $this->user->department,
            'effective_date' => $this->effective_date,
            'notes' => $this->promotion_notes,
            'certificate_path' => $this->promotionCertificate?->store('promotion-certificates', 'public'),
        ]);

        $this->user->update([
            'designation' => $this->new_designation,
            'department' => $this->new_department ?: $this->user->department,
        ]);
        $this->user->refresh();
        $this->designation = $this->user->designation ?? '';
        $this->department = $this->user->department ?? '';

        $this->showPromotionForm = false;
        $this->dispatch('toast', message: 'Promotion recorded and designation updated.', type: 'success');
    }

    public function submitOfferLetter(): void
    {
        $this->validate(['offerLetterFile' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf']);

        CompanyDocument::updateOrCreate(
            ['user_id' => $this->user->id, 'type' => CompanyDocument::TYPE_OFFER_LETTER],
            [
                'title' => 'Offer Letter',
                'file_path' => $this->offerLetterFile->store('company-documents', 'public'),
                'uploaded_by' => Auth::id(),
            ]
        );

        $this->showOfferLetterForm = false;
        $this->reset(['offerLetterFile']);
        $this->dispatch('toast', message: 'Offer letter uploaded.', type: 'success');
    }

    public function render()
    {
        $documents = EmployeeDocument::where('user_id', $this->user->id)->get()->keyBy('type');

        return view('livewire.hr-admin.employee-show', [
            'allRoles' => Role::orderBy('name')->pluck('name'),
            'potentialManagers' => User::where('id', '!=', $this->user->id)->orderBy('name')->get(),
            'assets' => Asset::where('user_id', $this->user->id)->orderByDesc('assigned_date')->get(),
            'leaveBalanceUsed' => $this->user->leaveRequests()->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days'),
            'recentAttendance' => $this->user->attendances()->orderByDesc('work_date')->limit(5)->get(),
            'catalog' => EmployeeDocument::CATALOG,
            'documents' => $documents,
            'requiredMissing' => collect(EmployeeDocument::requiredKeys())->diff($documents->keys())->count(),
            'promotions' => $this->user->promotions,
            'offerLetter' => $this->user->offerLetter(),
        ]);
    }
}
