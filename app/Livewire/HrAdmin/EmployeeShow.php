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

    public string $date_of_joining = '';

    public ?int $manager_id = null;

    public array $additional_manager_ids = [];

    public string $scheduled_login_time = '';

    public string $scheduled_logoff_time = '';

    #[Validate('required|numeric|min:0')]
    public string $basic_pay = '';

    #[Validate('required|numeric|min:0')]
    public string $hra_percent = '0';

    #[Validate('required|numeric|min:0')]
    public string $da_percent = '0';

    #[Validate('nullable|numeric|min:0')]
    public string $other_allowances = '0';

    #[Validate('required|integer|min:0')]
    public string $annual_casual_leave = '12';

    #[Validate('required|integer|min:0')]
    public string $annual_sick_leave = '12';

    public bool $showAssetForm = false;

    #[Validate('required|string|max:255')]
    public string $item_name = '';

    #[Validate('nullable|string|max:255')]
    public string $item_type = '';

    public bool $showPromotionForm = false;

    public ?int $editingPromotionId = null;

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

    public bool $showSalaryHikeForm = false;

    public ?int $editingSalaryHikeId = null;

    #[Validate('required|numeric|min:0')]
    public string $hike_new_salary = '';

    #[Validate('required|date')]
    public string $hike_effective_date = '';

    #[Validate('nullable|string|max:500')]
    public string $hike_notes = '';

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
        $this->date_of_joining = $user->date_of_joining ? $user->date_of_joining->toDateString() : '';
        $this->manager_id = $user->manager_id;
        $this->additional_manager_ids = $user->additionalManagers()->pluck('users.id')->all();
        $this->scheduled_login_time = $user->scheduled_login_time ? substr($user->scheduled_login_time, 0, 5) : '';
        $this->scheduled_logoff_time = $user->scheduled_logoff_time ? substr($user->scheduled_logoff_time, 0, 5) : '';
        $this->basic_pay = $user->basic_pay !== null ? (string) $user->basic_pay : '';
        $this->hra_percent = $user->hra_percent !== null ? (string) $user->hra_percent : '0';
        $this->da_percent = $user->da_percent !== null ? (string) $user->da_percent : '0';
        $this->other_allowances = $user->other_allowances !== null ? (string) $user->other_allowances : '0';
        $this->annual_casual_leave = (string) $user->annual_casual_leave;
        $this->annual_sick_leave = (string) $user->annual_sick_leave;
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
            'date_of_joining' => 'nullable|date',
            'scheduled_login_time' => 'nullable|date_format:H:i',
            'scheduled_logoff_time' => 'nullable|date_format:H:i',
        ]);

        $this->user->update([
            'employment_status' => $this->employment_status,
            'employment_type' => $this->employment_type,
            'designation' => $this->designation,
            'department' => $this->department,
            'monthly_salary' => $this->monthly_salary,
            'date_of_joining' => $this->date_of_joining ?: null,
            'scheduled_login_time' => $this->scheduled_login_time ?: null,
            'scheduled_logoff_time' => $this->scheduled_logoff_time ?: null,
        ]);

        $this->dispatch('toast', message: 'Employee details updated.', type: 'success');
    }

    public function saveSalaryStructure(): void
    {
        $this->validate([
            'basic_pay' => 'required|numeric|min:0',
            'hra_percent' => 'required|numeric|min:0',
            'da_percent' => 'required|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
        ]);

        if (! $this->user->monthly_salary) {
            $this->addError('hra_percent', 'Set a Monthly Salary above (in Employment Details) first — HRA and DA are calculated as a percentage of it.');

            return;
        }

        $this->user->update([
            'basic_pay' => $this->basic_pay,
            'hra_percent' => $this->hra_percent,
            'da_percent' => $this->da_percent,
            'other_allowances' => $this->other_allowances ?: 0,
        ]);
        $this->user->refresh();

        $this->dispatch('toast', message: 'Salary structure updated.', type: 'success');
    }

    public function saveLeaveAllotment(): void
    {
        $this->validate([
            'annual_casual_leave' => 'required|integer|min:0',
            'annual_sick_leave' => 'required|integer|min:0',
        ]);

        $this->user->update([
            'annual_casual_leave' => $this->annual_casual_leave,
            'annual_sick_leave' => $this->annual_sick_leave,
        ]);
        $this->user->refresh();

        $this->dispatch('toast', message: 'Leave allotment updated.', type: 'success');
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
        $this->reset(['editingPromotionId', 'new_designation', 'new_department', 'effective_date', 'promotion_notes', 'promotionCertificate']);
        $this->new_designation = $this->user->designation ?? '';
        $this->new_department = $this->user->department ?? '';
        $this->effective_date = now()->toDateString();
        $this->resetValidation();
        $this->showSalaryHikeForm = false;
        $this->showPromotionForm = true;
    }

    public function editPromotion(int $promotionId): void
    {
        $promotion = Promotion::where('user_id', $this->user->id)
            ->where('type', Promotion::TYPE_PROMOTION)
            ->findOrFail($promotionId);

        $this->editingPromotionId = $promotionId;
        $this->new_designation = $promotion->new_designation;
        $this->new_department = $promotion->new_department ?? '';
        $this->effective_date = $promotion->effective_date->toDateString();
        $this->promotion_notes = $promotion->notes ?? '';
        $this->promotionCertificate = null;
        $this->resetValidation();
        $this->showSalaryHikeForm = false;
        $this->showPromotionForm = true;
    }

    public function cancelPromotionForm(): void
    {
        $this->reset(['showPromotionForm', 'editingPromotionId', 'new_designation', 'new_department', 'effective_date', 'promotion_notes', 'promotionCertificate']);
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
            'type' => Promotion::TYPE_PROMOTION,
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

    public function updatePromotion(): void
    {
        $promotion = Promotion::where('user_id', $this->user->id)
            ->where('type', Promotion::TYPE_PROMOTION)
            ->findOrFail($this->editingPromotionId);

        $this->validate([
            'new_designation' => 'required|string|max:255',
            'new_department' => 'nullable|string|max:255',
            'effective_date' => 'required|date',
            'promotion_notes' => 'nullable|string|max:500',
            'promotionCertificate' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);

        $promotion->update([
            'new_designation' => $this->new_designation,
            'new_department' => $this->new_department ?: $promotion->new_department,
            'effective_date' => $this->effective_date,
            'notes' => $this->promotion_notes,
            'certificate_path' => $this->promotionCertificate
                ? $this->promotionCertificate->store('promotion-certificates', 'public')
                : $promotion->certificate_path,
        ]);

        // Keep the employee's live designation/department in sync only when we just
        // edited their most recent promotion — an older record shouldn't overwrite it.
        $latest = $this->user->promotions()->first();

        if ($latest && $latest->id === $promotion->id) {
            $this->user->update([
                'designation' => $promotion->new_designation,
                'department' => $promotion->new_department,
            ]);
            $this->user->refresh();
            $this->designation = $this->user->designation ?? '';
            $this->department = $this->user->department ?? '';
        }

        $this->cancelPromotionForm();
        $this->dispatch('toast', message: 'Promotion updated.', type: 'success');
    }

    public function openSalaryHikeForm(): void
    {
        $this->reset(['editingSalaryHikeId', 'hike_new_salary', 'hike_effective_date', 'hike_notes']);
        $this->hike_effective_date = now()->toDateString();
        $this->resetValidation();
        $this->showPromotionForm = false;
        $this->showSalaryHikeForm = true;
    }

    public function editSalaryHike(int $promotionId): void
    {
        $hike = Promotion::where('user_id', $this->user->id)
            ->where('type', Promotion::TYPE_SALARY_HIKE)
            ->findOrFail($promotionId);

        $this->editingSalaryHikeId = $promotionId;
        $this->hike_new_salary = $hike->new_salary !== null ? (string) $hike->new_salary : '';
        $this->hike_effective_date = $hike->effective_date->toDateString();
        $this->hike_notes = $hike->notes ?? '';
        $this->resetValidation();
        $this->showPromotionForm = false;
        $this->showSalaryHikeForm = true;
    }

    public function cancelSalaryHikeForm(): void
    {
        $this->reset(['showSalaryHikeForm', 'editingSalaryHikeId', 'hike_new_salary', 'hike_effective_date', 'hike_notes']);
    }

    public function addSalaryHike(): void
    {
        $this->validate([
            'hike_new_salary' => 'required|numeric|min:0',
            'hike_effective_date' => 'required|date',
            'hike_notes' => 'nullable|string|max:500',
        ]);

        Promotion::create([
            'user_id' => $this->user->id,
            'created_by' => Auth::id(),
            'type' => Promotion::TYPE_SALARY_HIKE,
            'previous_designation' => $this->user->designation,
            'new_designation' => $this->user->designation ?? '',
            'previous_department' => $this->user->department,
            'new_department' => $this->user->department,
            'previous_salary' => $this->user->monthly_salary,
            'new_salary' => $this->hike_new_salary,
            'effective_date' => $this->hike_effective_date,
            'notes' => $this->hike_notes,
        ]);

        $this->user->update(['monthly_salary' => $this->hike_new_salary]);
        $this->user->refresh();
        $this->monthly_salary = $this->user->monthly_salary ? (float) $this->user->monthly_salary : null;

        $this->showSalaryHikeForm = false;
        $this->dispatch('toast', message: 'Salary hike recorded.', type: 'success');
    }

    public function updateSalaryHike(): void
    {
        $hike = Promotion::where('user_id', $this->user->id)
            ->where('type', Promotion::TYPE_SALARY_HIKE)
            ->findOrFail($this->editingSalaryHikeId);

        $this->validate([
            'hike_new_salary' => 'required|numeric|min:0',
            'hike_effective_date' => 'required|date',
            'hike_notes' => 'nullable|string|max:500',
        ]);

        $hike->update([
            'new_salary' => $this->hike_new_salary,
            'effective_date' => $this->hike_effective_date,
            'notes' => $this->hike_notes,
        ]);

        // Keep the employee's live salary in sync only when we just edited their most
        // recent career-history record (promotion or hike) — an older one shouldn't overwrite it.
        $latest = $this->user->promotions()->first();

        if ($latest && $latest->id === $hike->id) {
            $this->user->update(['monthly_salary' => $hike->new_salary]);
            $this->user->refresh();
            $this->monthly_salary = $this->user->monthly_salary ? (float) $this->user->monthly_salary : null;
        }

        $this->cancelSalaryHikeForm();
        $this->dispatch('toast', message: 'Salary hike updated.', type: 'success');
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
            'casualLeaveUsed' => $this->user->casualLeaveUsed(now()->year),
            'casualLeaveRemaining' => $this->user->casualLeaveRemaining(now()->year),
            'sickLeaveUsed' => $this->user->sickLeaveUsed(now()->year),
            'sickLeaveRemaining' => $this->user->sickLeaveRemaining(now()->year),
            'recentAttendance' => $this->user->attendances()->orderByDesc('work_date')->limit(5)->get(),
            'catalog' => EmployeeDocument::CATALOG,
            'documents' => $documents,
            'requiredMissing' => collect(EmployeeDocument::requiredKeys())->diff($documents->keys())->count()
                + ($this->user->hasCompleteBankDetails() ? 0 : 1),
            'promotions' => $this->user->promotions,
            'offerLetter' => $this->user->offerLetter(),
        ]);
    }
}
