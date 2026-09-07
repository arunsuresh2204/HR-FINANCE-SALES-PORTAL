<?php

namespace App\Livewire\Hr;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ExpenseIndex extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    #[Validate('required|numeric|min:0.01')]
    public string $amount = '';

    #[Validate('required|in:software,travel,office,marketing,other')]
    public string $category = 'software';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('required|date|before_or_equal:today')]
    public string $expense_date = '';

    #[Validate('nullable|file|max:5120|mimes:jpg,jpeg,png,pdf')]
    public $receipt = null;

    public function openForm(): void
    {
        $this->reset(['amount', 'description', 'receipt']);
        $this->category = 'software';
        $this->expense_date = now()->toDateString();
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        $path = $this->receipt?->store('receipts', 'public');

        Expense::create([
            'user_id' => Auth::id(),
            'amount' => $this->amount,
            'category' => $this->category,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
            'receipt_file' => $path,
            'status' => 'pending',
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Expense claim submitted.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr.expense-index', [
            'expenses' => Expense::where('user_id', Auth::id())->latest()->paginate(10),
        ]);
    }
}
