<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamTargetStats extends Component
{
    public int $year;

    public int $month;

    public function mount(int $year, int $month): void
    {
        $authUser = Auth::user();

        if (! $authUser->isSuperAdmin() && ! $authUser->canSetSalesTargets()) {
            abort(403);
        }

        $this->year = $year;
        $this->month = $month;
    }

    protected function teamFor(User $authUser)
    {
        if ($authUser->isSuperAdmin()) {
            return User::permission('access_sales_targets')->orderBy('name')->get();
        }

        $reportIds = $authUser->allDescendants()->pluck('id');
        $team = User::permission('access_sales_targets')->whereIn('id', $reportIds)->orderBy('name')->get();

        if ($authUser->can('access_sales_targets')) {
            $team->prepend($authUser);
        }

        return $team;
    }

    protected function totalsFor($people, Carbon $period): array
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
        $period = Carbon::create($this->year, $this->month, 1);
        $team = $this->teamFor($authUser);

        $members = $team->map(function (User $person) use ($period) {
            $target = SalesTarget::where('user_id', $person->id)->where('month', $period->month)->where('year', $period->year)->first();
            $targetAmount = $target?->effectiveTargetAmount() ?? 0;
            $achieved = $target?->achievedAmount() ?? 0;

            return [
                'user' => $person,
                'target' => $targetAmount,
                'achieved' => $achieved,
                'pending' => max(0, $targetAmount - $achieved),
                'attainment' => $targetAmount > 0 ? (int) round($achieved / $targetAmount * 100) : 0,
                'clientsAcquired' => Lead::where('sales_person_id', $person->id)
                    ->where('status', 'won')
                    ->whereYear('updated_at', $period->year)
                    ->whereMonth('updated_at', $period->month)
                    ->count(),
            ];
        })->sortByDesc('attainment')->values();

        $teamTarget = (float) $members->sum('target');
        $teamAchieved = (float) $members->sum('achieved');

        $trend = collect(range(0, 5))->map(function ($i) use ($team, $period) {
            $p = $period->copy()->subMonthsNoOverflow(5 - $i);
            $totals = $this->totalsFor($team, $p);

            return [
                'label' => $p->format('M'),
                'target' => $totals['target'],
                'achieved' => $totals['achieved'],
                'isCurrent' => $p->month === $period->month && $p->year === $period->year,
            ];
        });

        return view('livewire.sales.team-target-stats', [
            'period' => $period,
            'members' => $members,
            'teamTarget' => $teamTarget,
            'teamAchieved' => $teamAchieved,
            'teamPending' => max(0, $teamTarget - $teamAchieved),
            'teamAttainment' => $teamTarget > 0 ? (int) round($teamAchieved / $teamTarget * 100) : 0,
            'trend' => $trend,
        ]);
    }
}
