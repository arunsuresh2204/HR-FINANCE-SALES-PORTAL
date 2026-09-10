<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use App\Models\LeadSource;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LeadSourceManagement extends Component
{
    #[Validate('required|string|max:100|unique:lead_sources,name')]
    public string $name = '';

    public bool $showAddForm = false;

    public ?int $editingId = null;

    public string $editingName = '';

    public function openAddForm(): void
    {
        $this->reset(['name']);
        $this->resetValidation();
        $this->showAddForm = true;
    }

    public function addSource(): void
    {
        $this->validate();

        LeadSource::create(['name' => $this->name]);

        $this->reset(['name']);
        $this->showAddForm = false;
        $this->dispatch('toast', message: 'Source added.', type: 'success');
    }

    public function startRename(int $id): void
    {
        $source = LeadSource::findOrFail($id);
        $this->editingId = $id;
        $this->editingName = $source->name;
        $this->resetValidation();
    }

    public function cancelRename(): void
    {
        $this->reset(['editingId', 'editingName']);
    }

    public function saveRename(): void
    {
        $source = LeadSource::findOrFail($this->editingId);

        $this->validate([
            'editingName' => 'required|string|max:100|unique:lead_sources,name,'.$source->id,
        ]);

        $oldName = $source->name;
        $source->update(['name' => $this->editingName]);

        if ($oldName !== $this->editingName) {
            Lead::where('source', $oldName)->update(['source' => $this->editingName]);
        }

        $this->reset(['editingId', 'editingName']);
        $this->dispatch('toast', message: 'Source renamed.', type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $source = LeadSource::findOrFail($id);
        $source->update(['is_active' => ! $source->is_active]);
    }

    public function deleteSource(int $id): void
    {
        $source = LeadSource::findOrFail($id);

        if ($source->leadCount() > 0) {
            $this->dispatch('toast', message: 'Cannot delete a source with leads — deactivate it instead.', type: 'error');

            return;
        }

        $source->delete();
        $this->dispatch('toast', message: 'Source deleted.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.lead-source-management', [
            'sources' => LeadSource::orderBy('name')->get(),
        ]);
    }
}
