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
            'requiredMissing' => collect(EmployeeDocument::requiredKeys())->diff($documents->keys())->count(),
        ]);
    }
}
