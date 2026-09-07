<?php

namespace App\Livewire\HrAdmin;

use App\Models\PolicyDocument;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PolicyIndex extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|file|max:10240')]
    public $file = null;

    public function openForm(): void
    {
        $this->reset(['title', 'description', 'file']);
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        PolicyDocument::create([
            'uploaded_by' => Auth::id(),
            'title' => $this->title,
            'description' => $this->description,
            'file_path' => $this->file->store('policies', 'public'),
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Policy document published.', type: 'success');
    }

    public function delete(PolicyDocument $policyDocument): void
    {
        $policyDocument->delete();
        $this->dispatch('toast', message: 'Policy document removed.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr-admin.policy-index', [
            'policies' => PolicyDocument::latest()->get(),
        ]);
    }
}
