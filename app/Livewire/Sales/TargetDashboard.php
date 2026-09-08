<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TargetDashboard extends Component
{
    public int $month;

    public int $year;

    public bool $showTargetForm = false;

    public ?int $targetUserId = null;

    #[Validate('required|numeric|min:0')]
    public string $target_amount = '';

    #[Validate('nullable|numeric|min:0|max:100')]
    public string $commission_percent = '0';

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function prevMonth(): void
    {
        $this->month--;
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }
    }

    public function nextMonth(): void
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
    }

    public function openTargetForm(int $userId): void
    {
        $authUser = Auth::user();
        $targetUser = User::findOrFail($userId);

        if (! $authUser->isSuperAdmin() && ! $authUser->isManagerOf($targetUser)) {
            return;
        }

        $existing = SalesTarget::where('user_id', $userId)->where('month', $this->month)->where('year', $this->year)->first();

        $this->targetUserId = $userId;
        $this->target_amount = $existing ? (string) $existing->target_amount : '';
        $this->commission_percent = $existing ? (string) $existing->commission_percent : '0';
        $this->resetValidation();
        $this->showTargetForm = true;
    }

    public function saveTarget(): void
    {
        $authUser = Auth::user();

        if (! $authUser->canSetSalesTargets()) {
            return;
        }

        $targetUser = User::findOrFail($this->targetUserId);

        if (! $authUser->isSuperAdmin() && ! $authUser->isManagerOf($targetUser)) {
            return;
        }

        $this->validate();

        SalesTarget::updateOrCreate(
            ['user_id' => $this->targetUserId, 'month' => $this->month, 'year' => $this->year],
            ['target_amount' => $this->target_amount, 'commission_percent' => $this->commission_percent ?: 0]
        );

        $this->showTargetForm = false;
        $this->dispatch('toast', message: 'Target saved.', type: 'success');
    }

    protected function statsFor(User $user): array
    {
        $target = SalesTarget::where('user_id', $user->id)->where('month', $this->month)->where('year', $this->year)->first();
        $totalLeads = Lead::where('sales_person_id', $user->id)->count();
        $wonLeads = Lead::where('sales_person_id', $user->id)->where('status', 'won')->count();

        return [
            'user' => $user,
            'target' => $target,
            'achieved' => $target?->achievedAmount() ?? 0,
            'effectiveTarget' => $target?->effectiveTargetAmount() ?? 0,
            'deficitCarried' => $target?->deficitCarriedIn() ?? 0,
            'openLeads' => Lead::where('sales_person_id', $user->id)->whereNotIn('status', Lead::CLOSED_STATUSES)->count(),
            'conversionRate' => $totalLeads ? round($wonLeads / $totalLeads * 100) : 0,
        ];
    }

    public function render()
    {
        $authUser = Auth::user();

        if ($authUser->isSuperAdmin()) {
            $salesPeople = User::role('sales_exec')->orderBy('name')->get();
        } elseif ($authUser->canSetSalesTargets()) {
            $reportIds = $authUser->allReports()->pluck('id');
            $salesPeople = User::role('sales_exec')->whereIn('id', $reportIds)->orderBy('name')->get();
        } else {
            $salesPeople = collect([$authUser]);
        }

        $rows = $salesPeople->map(fn ($sp) => $this->statsFor($sp))->sortByDesc('achieved')->values();

        return view('livewire.sales.target-dashboard', [
            'rows' => $rows,
            'canSetTargets' => $authUser->canSetSalesTargets(),
        ]);
    }
}
