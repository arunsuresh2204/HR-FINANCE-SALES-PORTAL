<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'password',
        'avatar_path',
        'phone',
        'designation',
        'department',
        'date_of_birth',
        'date_of_joining',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'monthly_salary',
        'employment_status',
        'employment_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'date_of_joining' => 'date',
            'monthly_salary' => 'decimal:2',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function marketingLogs(): HasMany
    {
        return $this->hasMany(MarketingLog::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'sales_person_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'sales_person_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function salesTargets(): HasMany
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function resignations(): HasMany
    {
        return $this->hasMany(Resignation::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class)->orderByDesc('effective_date');
    }

    public function companyDocuments(): HasMany
    {
        return $this->hasMany(CompanyDocument::class);
    }

    public function offerLetter(): ?CompanyDocument
    {
        return $this->companyDocuments()->where('type', CompanyDocument::TYPE_OFFER_LETTER)->latest()->first();
    }

    public function employmentTypeLabel(): string
    {
        return match ($this->employment_type) {
            'trainee_paid' => 'Trainee (Paid)',
            'trainee_unpaid' => 'Trainee (Unpaid)',
            'intern' => 'Intern',
            default => 'Full-time Employee',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isHrAdmin(): bool
    {
        return $this->hasAnyRole(['hr_admin', 'super_admin']);
    }

    public function isFinanceAdmin(): bool
    {
        return $this->hasAnyRole(['finance_admin', 'super_admin']);
    }

    public function isSalesExec(): bool
    {
        return $this->hasAnyRole(['sales_exec', 'super_admin']);
    }

    public function isProgrammer(): bool
    {
        return $this->hasAnyRole(['programmer', 'super_admin']);
    }

    public function isMarketer(): bool
    {
        return $this->hasAnyRole(['marketer', 'super_admin']);
    }

    public function canManageLeads(): bool
    {
        return $this->hasAnyRole(['sales_exec', 'marketer', 'super_admin']);
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name));
        $initials = collect($words)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');

        return $initials ?: 'U';
    }
}
