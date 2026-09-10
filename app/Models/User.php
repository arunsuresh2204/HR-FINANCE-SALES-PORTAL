<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'bank_account_holder_name',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'monthly_salary',
        'basic_pay',
        'hra_percent',
        'da_percent',
        'other_allowances',
        'annual_casual_leave',
        'annual_sick_leave',
        'employment_status',
        'employment_type',
        'manager_id',
        'scheduled_login_time',
        'scheduled_logoff_time',
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
            'basic_pay' => 'decimal:2',
            'hra_percent' => 'decimal:2',
            'da_percent' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'annual_casual_leave' => 'integer',
            'annual_sick_leave' => 'integer',
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

    public function attendanceStatusRequests(): HasMany
    {
        return $this->hasMany(AttendanceStatusRequest::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function hasApprovedLeaveOn(string $date): bool
    {
        return $this->leaveRequests()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
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

    public function hasCompleteBankDetails(): bool
    {
        return filled($this->bank_account_holder_name)
            && filled($this->bank_name)
            && filled($this->bank_account_number)
            && filled($this->bank_ifsc)
            && filled($this->bank_branch);
    }

    public function hasSalaryStructure(): bool
    {
        return $this->basic_pay !== null;
    }

    public function hraAmount(): float
    {
        return round((float) $this->basic_pay * (float) $this->hra_percent / 100, 2);
    }

    public function daAmount(): float
    {
        return round((float) $this->basic_pay * (float) $this->da_percent / 100, 2);
    }

    public function grossMonthlySalary(): float
    {
        return round((float) $this->basic_pay + $this->hraAmount() + $this->daAmount() + (float) $this->other_allowances, 2);
    }

    public function approvedLeaveDaysOfType(string $type, string $from, string $to): int
    {
        if ($to < $from) {
            return 0;
        }

        return (int) $this->leaveRequests()
            ->where('type', $type)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$from, $to])
            ->sum('days');
    }

    public function casualLeaveUsed(int $year): int
    {
        return $this->approvedLeaveDaysOfType('vacation', "{$year}-01-01", "{$year}-12-31");
    }

    public function sickLeaveUsed(int $year): int
    {
        return $this->approvedLeaveDaysOfType('sick', "{$year}-01-01", "{$year}-12-31");
    }

    public function casualLeaveRemaining(int $year): int
    {
        return max(0, ($this->annual_casual_leave ?? 0) - $this->casualLeaveUsed($year));
    }

    public function sickLeaveRemaining(int $year): int
    {
        return max(0, ($this->annual_sick_leave ?? 0) - $this->sickLeaveUsed($year));
    }

    /**
     * Days of leave in the given month that should be unpaid: casual/sick days
     * that pushed the employee past their annual allotment during this month,
     * plus any leave explicitly requested as unpaid within the month.
     */
    public function unpaidLeaveDaysForMonth(int $year, int $month): int
    {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $yearStart = "{$year}-01-01";
        $beforeMonth = $monthStart->copy()->subDay()->toDateString();

        $casualAllotment = $this->annual_casual_leave ?? 0;
        $sickAllotment = $this->annual_sick_leave ?? 0;

        $casualUsedBefore = $this->approvedLeaveDaysOfType('vacation', $yearStart, $beforeMonth);
        $sickUsedBefore = $this->approvedLeaveDaysOfType('sick', $yearStart, $beforeMonth);
        $casualUsedThrough = $this->approvedLeaveDaysOfType('vacation', $yearStart, $monthEnd->toDateString());
        $sickUsedThrough = $this->approvedLeaveDaysOfType('sick', $yearStart, $monthEnd->toDateString());

        $excessBefore = max(0, $casualUsedBefore - $casualAllotment) + max(0, $sickUsedBefore - $sickAllotment);
        $excessThrough = max(0, $casualUsedThrough - $casualAllotment) + max(0, $sickUsedThrough - $sickAllotment);

        $balanceExceededDays = max(0, $excessThrough - $excessBefore);

        $unpaidTypeDays = $this->approvedLeaveDaysOfType('unpaid', $monthStart->toDateString(), $monthEnd->toDateString());

        return $balanceExceededDays + $unpaidTypeDays;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function additionalManagers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_additional_managers', 'user_id', 'manager_id');
    }

    public function additionalReports(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_additional_managers', 'manager_id', 'user_id');
    }

    public function allManagers()
    {
        return $this->additionalManagers->when($this->manager, fn ($c) => $c->push($this->manager))->unique('id');
    }

    public function allReports()
    {
        return $this->directReports->concat($this->additionalReports)->unique('id');
    }

    /**
     * Every report under this user's reporting line, at any depth
     * (e.g. a manager's team leads and, in turn, those leads' programmers).
     */
    public function allDescendants()
    {
        return $this->allReports()->reduce(
            fn ($descendants, User $report) => $descendants->push($report)->merge($report->allDescendants()),
            collect()
        )->unique('id');
    }

    public function isManagerOf(User $user): bool
    {
        return $user->manager_id === $this->id || $user->additionalManagers->contains('id', $this->id);
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

    public function isManager(): bool
    {
        return $this->hasAnyRole(['manager_engineering', 'super_admin']);
    }

    public function isTeamLead(): bool
    {
        return $this->hasAnyRole(['team_lead', 'super_admin']);
    }

    public function canSetSalesTargets(): bool
    {
        // Sales-target authority belongs to sales leadership, not engineering
        // management — no functional role currently covers that (Manager –
        // Sales is planned but not yet built), so it's Super Admin-only for
        // now.
        return $this->isSuperAdmin();
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name));
        $initials = collect($words)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');

        return $initials ?: 'U';
    }
}
