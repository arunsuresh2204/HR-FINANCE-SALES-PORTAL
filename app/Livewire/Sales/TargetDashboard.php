<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TargetDashboard extends Component
{
    public string $monthPicker;

    public bool $showTargetForm = false;

    public ?int $targetUserId = null;

    public ?int $targetMonth = null;

    public ?int $targetYear = null;

    #[Validate('required|numeric|min:0')]
    public string $target_amount = '';

    #[Validate('nullable|numeric|min:0|max:100')]
    public string $commission_percent = '0';

    public function mount(): void
    {
        $this->monthPicker = now()->format('Y-m');
    }

    public function shiftAnchor(int $delta): void
    {
        $anchor = Carbon::createFromFormat('Y-m', $this->monthPicker)->addMonthsNoOverflow($delta);
        $this->monthPicker = $anchor->format('Y-m');
    }

    protected function periods(): array
    {
        $anchor = Carbon::createFromFormat('Y-m', $this->monthPicker)->startOfMonth();

        return collect(range(0, 4))->map(fn ($i) => $anchor->copy()->subMonthsNoOverflow($i))->all();
    }

    protected function canManage(User $targetUser): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin() || $authUser->allDescendants()->contains('id', $targetUser->id);
    }

    public function openTargetForm(int $userId, int $month, int $year): void
    {
        $targetUser = User::findOrFail($userId);

        if (! $this->canManage($targetUser)) {
            return;
        }

        $existing = SalesTarget::where('user_id', $userId)->where('month', $month)->where('year', $year)->first();

        $this->targetUserId = $userId;
        $this->targetMonth = $month;
        $this->targetYear = $year;
        $this->target_amount = $existing ? (string) $existing->target_amount : '';
        $this->commission_percent = $existing ? (string) $existing->commission_percent : '0';
        $this->resetValidation();
        $this->showTargetForm = true;
    }

    public function saveTarget(): void
    {
        $targetUser = User::findOrFail($this->targetUserId);

        if (! $this->canManage($targetUser)) {
            return;
        }

        $this->validate();

        SalesTarget::updateOrCreate(
            ['user_id' => $this->targetUserId, 'month' => $this->targetMonth, 'year' => $this->targetYear],
            ['target_amount' => $this->target_amount, 'commission_percent' => $this->commission_percent ?: 0]
        );

        $this->showTargetForm = false;
        $this->dispatch('toast', message: 'Target saved.', type: 'success');
    }

    protected function monthStatsFor(User $user, Carbon $period): array
    {
        $target = SalesTarget::where('user_id', $user->id)->where('month', $period->month)->where('year', $period->year)->first();

        return [
            'month' => $period->month,
            'year' => $period->year,
            'label' => $period->format('F Y'),
            'target' => $target,
            'achieved' => $target?->achievedAmount() ?? 0,
            'effectiveTarget' => $target?->effectiveTargetAmount() ?? 0,
            'deficitCarried' => $target?->deficitCarriedIn() ?? 0,
            'clientsAcquired' => Lead::where('sales_person_id', $user->id)
                ->where('status', 'won')
                ->whereYear('updated_at', $period->year)
                ->whereMonth('updated_at', $period->month)
                ->count(),
        ];
    }

    public function render()
    {
        $authUser = Auth::user();

        if ($authUser->isSuperAdmin()) {
            $salesPeople = User::role('sales_exec')->orderBy('name')->get();
        } elseif ($authUser->canSetSalesTargets()) {
            $reportIds = $authUser->allDescendants()->pluck('id');
            $salesPeople = User::role('sales_exec')->whereIn('id', $reportIds)->orderBy('name')->get();
        } else {
            $salesPeople = collect([$authUser]);
        }

        $periods = $this->periods();

        $rows = $salesPeople->map(function (User $sp) use ($periods) {
            $totalLeads = Lead::where('sales_person_id', $sp->id)->count();
            $wonLeadsAllTime = Lead::where('sales_person_id', $sp->id)->where('status', 'won')->count();

            return [
                'user' => $sp,
                'openLeads' => Lead::where('sales_person_id', $sp->id)->whereNotIn('status', Lead::CLOSED_STATUSES)->count(),
                'conversionRate' => $totalLeads ? round($wonLeadsAllTime / $totalLeads * 100) : 0,
                'months' => collect($periods)->map(fn ($p) => $this->monthStatsFor($sp, $p))->all(),
            ];
        })->sortByDesc(fn ($row) => $row['months'][0]['achieved'])->values();

        return view('livewire.sales.target-dashboard', [
            'rows' => $rows,
            'canSetTargets' => $authUser->canSetSalesTargets(),
        ]);
    }
}
