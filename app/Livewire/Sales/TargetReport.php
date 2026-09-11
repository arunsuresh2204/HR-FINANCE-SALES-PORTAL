<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TargetReport extends Component
{
    public User $user;

    public int $year;

    public int $month;

    public function mount(User $user, int $year, int $month): void
    {
        $authUser = Auth::user();

        if ($authUser->id !== $user->id && ! $authUser->isSuperAdmin() && ! $authUser->allDescendants()->contains('id', $user->id)) {
            abort(403);
        }

        $this->user = $user;
        $this->year = $year;
        $this->month = $month;
    }

    protected function statusColors(): array
    {
        return [
            'pending' => '#3987e5',
            'positive' => '#199e70',
            'negative' => '#d95926',
            'proposal_sent' => '#c98500',
            'rejected' => '#9085e9',
            'won' => '#008300',
            'lost' => '#d55181',
        ];
    }

    protected function adjacentPeriod(int $delta): array
    {
        $period = Carbon::create($this->year, $this->month, 1)->addMonthsNoOverflow($delta);

        return ['year' => $period->year, 'month' => $period->month];
    }

    public function render()
    {
        $period = Carbon::create($this->year, $this->month, 1);

        $contacted = Lead::where('sales_person_id', $this->user->id)
            ->whereYear('contacted_date', $this->year)
            ->whereMonth('contacted_date', $this->month)
            ->get();

        $totalContacted = $contacted->count();
        $statusColors = $this->statusColors();

        $statusCounts = collect(Lead::STATUSES)->mapWithKeys(
            fn ($s) => [$s => $contacted->where('status', $s)->count()]
        );

        $pct = fn (int $n) => $totalContacted > 0 ? (int) round($n / $totalContacted * 100) : 0;

        $breakdown = $statusCounts->map(fn ($count, $status) => [
            'status' => $status,
            'label' => ucwords(str_replace('_', ' ', $status)),
            'count' => $count,
            'pct' => $pct($count),
            'color' => $statusColors[$status],
        ])->values();

        $closedCount = $statusCounts['won'] + $statusCounts['lost'] + $statusCounts['rejected'];

        $achievement = collect(range(0, 5))->map(function ($i) use ($period) {
            $p = $period->copy()->subMonthsNoOverflow(5 - $i);
            $target = SalesTarget::where('user_id', $this->user->id)->where('month', $p->month)->where('year', $p->year)->first();

            return [
                'label' => $p->format('M Y'),
                'achieved' => (float) ($target?->achievedAmount() ?? 0),
                'target' => (float) ($target?->effectiveTargetAmount() ?? 0),
            ];
        });

        $wonLeads = Lead::where('sales_person_id', $this->user->id)
            ->where('status', 'won')
            ->whereYear('updated_at', $this->year)
            ->whereMonth('updated_at', $this->month)
            ->with(['client.projects.billingRequests', 'activities'])
            ->latest('updated_at')
            ->get();

        $clientRows = $wonLeads->map(fn (Lead $lead) => [
            'lead' => $lead,
            'projects' => $lead->client?->projects ?? collect(),
            'contacts' => $lead->activities->count(),
        ]);

        $currentTarget = SalesTarget::where('user_id', $this->user->id)->where('month', $this->month)->where('year', $this->year)->first();

        $sources = $contacted->pluck('source')->merge($wonLeads->pluck('source'))->unique()->sort()->values();

        $sourceStats = $sources->map(function ($source) use ($contacted, $wonLeads, $totalContacted) {
            $contactedForSource = $contacted->where('source', $source);
            $wonForSource = $wonLeads->where('source', $source);

            return [
                'source' => $source,
                'contacted' => $contactedForSource->count(),
                'contactedPct' => $totalContacted > 0 ? (int) round($contactedForSource->count() / $totalContacted * 100) : 0,
                'won' => $wonForSource->count(),
                'dealValue' => $wonForSource->sum('budget'),
            ];
        })->sortByDesc('contacted')->values();

        return view('livewire.sales.target-report', [
            'period' => $period,
            'totalContacted' => $totalContacted,
            'positivePct' => $pct($statusCounts['positive']),
            'negativePct' => $pct($statusCounts['negative']),
            'closedPct' => $pct($closedCount),
            'winRatePct' => $pct($statusCounts['won']),
            'breakdown' => $breakdown,
            'achievement' => $achievement,
            'clientRows' => $clientRows,
            'totalClients' => $wonLeads->count(),
            'totalDealValue' => $wonLeads->sum('budget'),
            'totalProjects' => $clientRows->sum(fn ($r) => $r['projects']->count()),
            'currentTarget' => $currentTarget,
            'sourceStats' => $sourceStats,
            'prevPeriod' => $this->adjacentPeriod(-1),
            'nextPeriod' => $this->adjacentPeriod(1),
        ]);
    }
}
