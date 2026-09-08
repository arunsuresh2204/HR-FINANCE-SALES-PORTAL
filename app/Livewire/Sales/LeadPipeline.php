<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class LeadPipeline extends Component
{
    use WithPagination;

    public string $ownerFilter = '';

    public string $statusFilter = '';

    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $client_name = '';

    #[Validate('nullable|string|max:255')]
    public string $company_name = '';

    #[Validate('nullable|string|max:255')]
    public string $country = '';

    #[Validate('required|string|max:2000')]
    public string $requirement = '';

    #[Validate('required|string|max:100')]
    public string $service_type = '';

    #[Validate('required|string|max:255')]
    public string $source = '';

    #[Validate('nullable|string|max:255')]
    public string $contact_link = '';

    public array $statuses = [];

    public array $comments = [];

    public function createLead(): void
    {
        $this->validate();

        Lead::create([
            'sales_person_id' => Auth::id(),
            'client_name' => $this->client_name,
            'company_name' => $this->company_name ?: null,
            'country' => $this->country ?: null,
            'requirement' => $this->requirement,
            'service_type' => $this->service_type,
            'source' => $this->source,
            'contact_link' => $this->contact_link ?: null,
            'status' => 'pending',
            'contacted_date' => now()->toDateString(),
        ]);

        $this->reset(['client_name', 'company_name', 'country', 'requirement', 'contact_link']);
        $this->resetValidation();
        $this->resetPage();
        $this->dispatch('toast', message: 'Lead added.', type: 'success');
    }

    public function updatedStatuses($value, $key): void
    {
        if (! in_array($value, Lead::STATUSES, true)) {
            return;
        }

        $lead = Lead::find($key);

        if (! $lead || (! Auth::user()->isSuperAdmin() && $lead->sales_person_id !== Auth::id())) {
            return;
        }

        $lead->update(['status' => $value]);

        if ($value === 'won') {
            $this->dispatch('toast', message: 'Lead marked as won! Convert it to a client from the lead page.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Status updated.', type: 'success');
        }
    }

    public function updatedComments($value, $key): void
    {
        $lead = Lead::find($key);

        if (! $lead || (! Auth::user()->isSuperAdmin() && $lead->sales_person_id !== Auth::id())) {
            return;
        }

        $lead->update(['comment' => $value]);
    }

    public function render()
    {
        $user = Auth::user();

        $query = Lead::with('salesPerson')->latest('contacted_date');

        if (! $user->isSuperAdmin()) {
            $query->where('sales_person_id', $user->id);
        } elseif ($this->ownerFilter) {
            $query->where('sales_person_id', $this->ownerFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(fn ($q) => $q->where('client_name', 'like', "%{$this->search}%")
                ->orWhere('company_name', 'like', "%{$this->search}%")
                ->orWhere('requirement', 'like', "%{$this->search}%"));
        }

        $leads = $query->paginate(25);

        $this->statuses = $leads->pluck('status', 'id')->all();
        $this->comments = $leads->pluck('comment', 'id')->map(fn ($c) => $c ?? '')->all();

        return view('livewire.sales.lead-pipeline', [
            'leads' => $leads,
            'owners' => $user->isSuperAdmin() ? User::role(['sales_exec', 'marketer'])->orderBy('name')->get() : collect(),
            'statusOptions' => Lead::STATUSES,
        ]);
    }
}
