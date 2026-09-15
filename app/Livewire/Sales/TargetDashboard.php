<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

    /**
     * Individual (non-manager) view still shows their own last 5 months
     * in one glance, so they don't need a drill-down page just for themselves.
     */
    protected function periods(): array
    {
        $anchor = Carbon::createFromFormat('Y-m', $this->monthPicker)->startOfMonth();

        return collect(range(0, 4))->map(fn ($i) => $anchor->copy()->subMonthsNoOverflow($i))->all();
    }

    protected function canManage(User $targetUser): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin()
            || $authUser->id === $targetUser->id
            || $authUser->allDescendants()->contains('id', $targetUser->id);
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

    /**
     * Target/achieved summed across a group of people for one month,
     * without the per-member extras (clients acquired etc.) a totals-only
     * comparison doesn't need.
     */
    protected function teamTotalsFor(Collection $people, Carbon $period): array
    {
        $target = 0.0;
        $achieved = 0.0;

        foreach ($people as $person) {
            $t = SalesTarget::where('user_id', $person->id)->where('month', $period->month)->where('year', $period->year)->first();
            $target += $t?->effectiveTargetAmount() ?? 0;
            $achieved += $t?->achievedAmount() ?? 0;
        }

        return ['target' => $target, 'achieved' => $achieved];
    }

    public function render()
    {
        $authUser = Auth::user();
        $isTeamView = false;

        if ($authUser->isSuperAdmin()) {
            $salesPeople = User::permission('access_sales_targets')->orderBy('name')->get();
            $isTeamView = true;
        } elseif ($authUser->canSetSalesTargets()) {
            $reportIds = $authUser->allDescendants()->pluck('id');
            $salesPeople = User::permission('access_sales_targets')->whereIn('id', $reportIds)->orderBy('name')->get();

            // The manager's own personal quota (if they carry one) counts
            // toward the team total alongside their reports', so it's
            // included as the first row rather than shown separately.
            if ($authUser->can('access_sales_targets')) {
                $salesPeople->prepend($authUser);
            }

            $isTeamView = true;
        } else {
            $salesPeople = collect([$authUser]);
        }

        $anchor = Carbon::createFromFormat('Y-m', $this->monthPicker)->startOfMonth();

        if ($isTeamView) {
            $members = $salesPeople->map(function (User $sp) use ($anchor, $authUser) {
                $stats = $this->monthStatsFor($sp, $anchor);
                $target = $stats['effectiveTarget'];
                $achieved = $stats['achieved'];

                return [
                    'user' => $sp,
                    'isSelf' => $sp->id === $authUser->id,
                    'hasTarget' => (bool) $stats['target'],
                    'target' => $target,
                    'achieved' => $achieved,
                    'clientsAcquired' => $stats['clientsAcquired'],
                    'attainment' => $target > 0 ? (int) round($achieved / $target * 100) : 0,
                ];
            })->values();

            $selfRow = $members->firstWhere('isSelf', true);
            $otherRows = $members->reject(fn ($r) => $r['isSelf'])->sortByDesc('attainment')->values();
            $members = $selfRow ? collect([$selfRow])->concat($otherRows) : $otherRows;

            $teamTarget = (float) $members->sum('target');
            $teamAchieved = (float) $members->sum('achieved');
            $teamAttainment = $teamTarget > 0 ? (int) round($teamAchieved / $teamTarget * 100) : 0;

            $prevTotals = $this->teamTotalsFor($salesPeople, $anchor->copy()->subMonthNoOverflow());
            $prevAttainment = $prevTotals['target'] > 0 ? (int) round($prevTotals['achieved'] / $prevTotals['target'] * 100) : 0;

            return view('livewire.sales.target-dashboard', [
                'isTeamView' => true,
                'period' => $anchor,
                'members' => $members,
                'teamTarget' => $teamTarget,
                'teamAchieved' => $teamAchieved,
                'teamPending' => max(0, $teamTarget - $teamAchieved),
                'teamAttainment' => $teamAttainment,
                'attainmentDelta' => $teamAttainment - $prevAttainment,
                'teamClientsAcquired' => $members->sum('clientsAcquired'),
            ]);
        }

        $periods = $this->periods();

        $rows = collect([$authUser])->map(fn (User $sp) => [
            'user' => $sp,
            'months' => collect($periods)->map(fn ($p) => $this->monthStatsFor($sp, $p))->all(),
        ]);

        return view('livewire.sales.target-dashboard', [
            'isTeamView' => false,
            'rows' => $rows,
        ]);
    }
}
