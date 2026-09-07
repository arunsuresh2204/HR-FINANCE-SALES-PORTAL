<?php

namespace App\Livewire\Finance;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseApprovals extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public function approve(Expense $expense): void
    {
        $expense->update(['status' => 'approved', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);
        $this->dispatch('toast', message: 'Expense approved.', type: 'success');
    }

    public function reject(Expense $expense): void
    {
        $expense->update(['status' => 'rejected', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);
        $this->dispatch('toast', message: 'Expense rejected.', type: 'success');
    }

    public function render()
    {
        $query = Expense::with('user')->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.finance.expense-approvals', [
            'expenses' => $query->paginate(12),
            'byCategory' => Expense::where('status', 'approved')->whereMonth('expense_date', now()->month)->selectRaw('category, sum(amount) as total')->groupBy('category')->pluck('total', 'category'),
        ]);
    }
}
