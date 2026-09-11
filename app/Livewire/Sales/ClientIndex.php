<?php

namespace App\Livewire\Sales;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $view = 'list';

    public string $tab = 'mine';

    public string $memberFilter = '';

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['grid', 'list'], true) ? $view : 'list';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMemberFilter(): void
    {
        $this->resetPage();
    }

    protected function canViewTeam(): bool
    {
        $user = Auth::user();

        return ! $user->isSuperAdmin() && ! $user->isFinanceAdmin() && ! $user->isManager() && $user->teamVisibilityFor('access_sales_clients');
    }

    public function setTab(string $tab): void
    {
        $this->tab = ($tab === 'team' && $this->canViewTeam()) ? 'team' : 'mine';
        $this->memberFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $canViewTeam = $this->canViewTeam();

        $query = Client::with(['salesPerson', 'invoices'])->latest();
        $teamMembers = collect();

        if ($user->isSuperAdmin() || $user->isFinanceAdmin() || $user->isManager()) {
            // sees every client, company-wide
        } elseif ($canViewTeam && $this->tab === 'team') {
            $team = $user->allDescendants();
            $teamIds = $team->pluck('id');
            $teamMembers = $team->sortBy('name')->values();

            if ($this->memberFilter && $teamIds->contains((int) $this->memberFilter)) {
                $query->where('sales_person_id', $this->memberFilter);
            } else {
                $query->whereIn('sales_person_id', $teamIds);
            }
        } else {
            $query->where('sales_person_id', $user->id);
        }

        if ($this->search) {
            $query->where('business_name', 'like', "%{$this->search}%");
        }

        return view('livewire.sales.client-index', [
            'clients' => $query->paginate(10),
            'canViewTeam' => $canViewTeam,
            'teamMembers' => $teamMembers,
        ]);
    }
}
