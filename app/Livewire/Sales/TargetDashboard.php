<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TargetDashboard extends Component
{
    public int $month;

    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
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
            'openLeads' => Lead::where('sales_person_id', $user->id)->whereNotIn('status', ['won', 'lost'])->count(),
            'conversionRate' => $totalLeads ? round($wonLeads / $totalLeads * 100) : 0,
        ];
    }

    public function render()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $salesPeople = User::role('sales_exec')->get();
            $rows = $salesPeople->map(fn ($sp) => $this->statsFor($sp))->sortByDesc('achieved')->values();
        } else {
            $rows = collect([$this->statsFor($user)]);
        }

        return view('livewire.sales.target-dashboard', [
            'rows' => $rows,
        ]);
    }
}
