<?php

namespace App\Livewire\Hr;

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

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|in:id_proof,contract,certification,other')]
    public string $type = 'id_proof';

    #[Validate('required|file|max:10240')]
    public $file = null;

    public function openForm(): void
    {
        $this->reset(['title', 'file']);
        $this->type = 'id_proof';
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        EmployeeDocument::create([
            'user_id' => Auth::id(),
            'title' => $this->title,
            'type' => $this->type,
            'file_path' => $this->file->store('employee-documents', 'public'),
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Document uploaded.', type: 'success');
    }

    public function delete(EmployeeDocument $employeeDocument): void
    {
        if ($employeeDocument->user_id !== Auth::id()) {
            return;
        }

        $employeeDocument->delete();
        $this->dispatch('toast', message: 'Document deleted.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr.document-index', [
            'documents' => EmployeeDocument::where('user_id', Auth::id())->latest()->get(),
            'policies' => PolicyDocument::latest()->get(),
        ]);
    }
}
