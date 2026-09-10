<?php

namespace App\Livewire\Hr;

use App\Models\CompanyDocument;
use App\Models\EmployeeDocument;
use App\Models\PolicyDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentIndex extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?string $activeKey = null;

    #[Validate('required|file|max:10240|mimes:jpg,jpeg,png,pdf')]
    public $file = null;

    #[Validate('nullable|string|max:255')]
    public string $bank_account_holder_name = '';

    #[Validate('nullable|string|max:255')]
    public string $bank_name = '';

    #[Validate('nullable|string|max:100')]
    public string $bank_account_number = '';

    #[Validate('nullable|string|max:50')]
    public string $bank_ifsc = '';

    #[Validate('nullable|string|max:255')]
    public string $bank_branch = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->bank_account_holder_name = $user->bank_account_holder_name ?? '';
        $this->bank_name = $user->bank_name ?? '';
        $this->bank_account_number = $user->bank_account_number ?? '';
        $this->bank_ifsc = $user->bank_ifsc ?? '';
        $this->bank_branch = $user->bank_branch ?? '';
    }

    public function saveBankDetails(): void
    {
        $this->validate([
            'bank_account_holder_name' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_ifsc' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
        ]);

        Auth::user()->update([
            'bank_account_holder_name' => $this->bank_account_holder_name ?: null,
            'bank_name' => $this->bank_name ?: null,
            'bank_account_number' => $this->bank_account_number ?: null,
            'bank_ifsc' => $this->bank_ifsc ?: null,
            'bank_branch' => $this->bank_branch ?: null,
        ]);

        $this->dispatch('toast', message: 'Bank account details updated.', type: 'success');
    }

    public function openForm(string $key): void
    {
        if (! array_key_exists($key, EmployeeDocument::flatCatalog())) {
            return;
        }

        $this->activeKey = $key;
        $this->file = null;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        $catalog = EmployeeDocument::flatCatalog();

        if (! $this->activeKey || ! array_key_exists($this->activeKey, $catalog)) {
            return;
        }

        EmployeeDocument::updateOrCreate(
            ['user_id' => Auth::id(), 'type' => $this->activeKey],
            ['title' => $catalog[$this->activeKey]['label'], 'file_path' => $this->file->store('employee-documents', 'public')]
        );

        $this->showForm = false;
        $this->reset(['activeKey', 'file']);
        $this->dispatch('toast', message: 'Document uploaded.', type: 'success');
    }

    public function render()
    {
        $user = Auth::user();
        $documents = EmployeeDocument::where('user_id', $user->id)->get()->keyBy('type');

        return view('livewire.hr.document-index', [
            'documents' => $documents,
            'catalog' => EmployeeDocument::CATALOG,
            'policies' => PolicyDocument::latest()->get(),
            'promotions' => $user->promotions,
            'offerLetter' => $user->offerLetter(),
            'requiredMissing' => collect(EmployeeDocument::requiredKeys())->diff($documents->keys())->count()
                + ($user->hasCompleteBankDetails() ? 0 : 1),
        ]);
    }
}
