<?php

namespace App\Livewire\Finance;

use App\Models\OperationalExpense;
use App\Models\OperationalExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OperationalExpenseIndex extends Component
{
    use WithPagination;

    public int $month;

    public int $year;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $category_id = null;

    public string $vendor = '';

    public string $amount = '';

    public string $expense_date = '';

    public bool $is_recurring = false;

    public string $notes = '';

    public bool $showCategoryManager = false;

    public string $newCategoryName = '';

    public ?int $editingCategoryId = null;

    public string $editingCategoryName = '';

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->expense_date = now()->format('Y-m-d');
    }

    public function updatingMonth(): void
    {
        $this->resetPage();
    }

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function openAddForm(): void
    {
        $this->reset(['editingId', 'category_id', 'vendor', 'amount', 'is_recurring', 'notes']);
        $this->expense_date = now()->format('Y-m-d');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $expense = OperationalExpense::findOrFail($id);

        $this->editingId = $expense->id;
        $this->category_id = $expense->category_id;
        $this->vendor = (string) $expense->vendor;
        $this->amount = (string) $expense->amount;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->is_recurring = $expense->is_recurring;
        $this->notes = (string) $expense->notes;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function saveExpense(): void
    {
        $this->validate([
            'category_id' => 'required|exists:operational_expense_categories,id',
            'vendor' => 'nullable|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data = [
            'category_id' => $this->category_id,
            'vendor' => $this->vendor ?: null,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date,
            'is_recurring' => $this->is_recurring,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingId) {
            OperationalExpense::findOrFail($this->editingId)->update($data);
            $this->dispatch('toast', message: 'Expense updated.', type: 'success');
        } else {
            $data['created_by'] = Auth::id();
            OperationalExpense::create($data);
            $this->dispatch('toast', message: 'Expense recorded.', type: 'success');
        }

        $this->showForm = false;
        $this->reset(['editingId', 'category_id', 'vendor', 'amount', 'is_recurring', 'notes']);
    }

    public function deleteExpense(int $id): void
    {
        OperationalExpense::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Expense deleted.', type: 'success');
    }

    public function openCategoryManager(): void
    {
        $this->reset(['newCategoryName', 'editingCategoryId', 'editingCategoryName']);
        $this->resetValidation();
        $this->showCategoryManager = true;
    }

    public function addCategory(): void
    {
        $this->validate(['newCategoryName' => 'required|string|max:100|unique:operational_expense_categories,name']);

        OperationalExpenseCategory::create(['name' => $this->newCategoryName]);

        $this->reset(['newCategoryName']);
        $this->dispatch('toast', message: 'Category added.', type: 'success');
    }

    public function startRenameCategory(int $id): void
    {
        $category = OperationalExpenseCategory::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->editingCategoryName = $category->name;
        $this->resetValidation();
    }

    public function cancelRenameCategory(): void
    {
        $this->reset(['editingCategoryId', 'editingCategoryName']);
    }

    public function saveRenameCategory(): void
    {
        $category = OperationalExpenseCategory::findOrFail($this->editingCategoryId);

        $this->validate([
            'editingCategoryName' => 'required|string|max:100|unique:operational_expense_categories,name,'.$category->id,
        ]);

        $category->update(['name' => $this->editingCategoryName]);

        $this->reset(['editingCategoryId', 'editingCategoryName']);
        $this->dispatch('toast', message: 'Category renamed.', type: 'success');
    }

    public function toggleCategoryActive(int $id): void
    {
        $category = OperationalExpenseCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function deleteCategory(int $id): void
    {
        $category = OperationalExpenseCategory::findOrFail($id);

        if ($category->expenseCount() > 0) {
            $this->dispatch('toast', message: 'Cannot delete a category with recorded expenses — deactivate it instead.', type: 'error');

            return;
        }

        $category->delete();
        $this->dispatch('toast', message: 'Category deleted.', type: 'success');
    }

    public function render()
    {
        $query = OperationalExpense::with(['category', 'creator'])
            ->whereMonth('expense_date', $this->month)
            ->whereYear('expense_date', $this->year);

        $expenses = (clone $query)->orderByDesc('expense_date')->paginate(15);

        $byCategory = (clone $query)->get()
            ->groupBy(fn ($e) => $e->category->name)
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        return view('livewire.finance.operational-expense-index', [
            'expenses' => $expenses,
            'categories' => OperationalExpenseCategory::orderBy('name')->get(),
            'byCategory' => $byCategory,
            'totalThisMonth' => (clone $query)->sum('amount'),
            'recurringThisMonth' => (clone $query)->where('is_recurring', true)->sum('amount'),
        ]);
    }
}
