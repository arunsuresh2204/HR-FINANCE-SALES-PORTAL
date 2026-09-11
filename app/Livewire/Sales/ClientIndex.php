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

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['grid', 'list'], true) ? $view : 'list';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Client::with(['salesPerson', 'invoices'])->latest();

        if ($user->isSuperAdmin() || $user->isFinanceAdmin() || $user->isManager()) {
            // sees every client, company-wide
        } elseif ($user->teamVisibilityFor('access_sales_clients')) {
            $teamIds = $user->allDescendants()->pluck('id')->push($user->id);
            $query->whereIn('sales_person_id', $teamIds);
        } else {
            $query->where('sales_person_id', $user->id);
        }

        if ($this->search) {
            $query->where('business_name', 'like', "%{$this->search}%");
        }

        return view('livewire.sales.client-index', [
            'clients' => $query->paginate(10),
        ]);
    }
}
