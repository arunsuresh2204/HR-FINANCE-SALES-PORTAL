<?php

namespace App\Livewire\Finance;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Invoice::with('client')->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.finance.invoice-index', [
            'invoices' => $query->paginate(12),
        ]);
    }
}
