<?php

namespace App\Livewire\Sales;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LeadShow extends Component
{
    public Lead $lead;

    public string $status = '';

    public string $budget = '';

    #[Validate('nullable|string|max:2000')]
    public string $comment = '';

    #[Validate('nullable|string|max:255')]
    public string $contact_link = '';

    #[Validate('required|string|max:1000')]
    public string $note = '';

    public bool $showConvertForm = false;

    #[Validate('required|string|max:255')]
    public string $business_name = '';

    #[Validate('nullable|string|max:255')]
    public string $business_type = '';

    #[Validate('nullable|string|max:1000')]
    public string $business_address = '';

    #[Validate('nullable|string|max:255')]
    public string $owner_name = '';

    #[Validate('nullable|string|max:255')]
    public string $owner_designation = '';

    #[Validate('nullable|string|max:255')]
    public string $owner_contact = '';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
        $this->status = $lead->status;
        $this->budget = $lead->budget ? (string) $lead->budget : '';
        $this->comment = $lead->comment ?? '';
        $this->contact_link = $lead->contact_link ?? '';
    }

    public function updateStatus(): void
    {
        $this->validate(['status' => 'required|in:'.implode(',', Lead::STATUSES)]);

        $data = ['status' => $this->status];

        if ($this->status === 'won') {
            $this->validate(['budget' => 'required|numeric|min:0']);
            $data['budget'] = $this->budget;
        }

        $this->lead->update($data);

        if ($this->status === 'won') {
            $this->lead->convertToClient();
            $this->dispatch('toast', message: 'Lead marked as won and converted to a client!', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Lead status updated.', type: 'success');
        }
    }

    public function saveDetails(): void
    {
        $this->validate([
            'comment' => 'nullable|string|max:2000',
            'contact_link' => 'nullable|string|max:255',
        ]);

        $this->lead->update([
            'comment' => $this->comment ?: null,
            'contact_link' => $this->contact_link ?: null,
        ]);

        $this->dispatch('toast', message: 'Details saved.', type: 'success');
    }

    public function addNote(): void
    {
        $this->validate(['note' => 'required|string|max:1000']);

        $this->lead->activities()->create([
            'user_id' => Auth::id(),
            'note' => $this->note,
        ]);

        $this->reset('note');
        $this->dispatch('toast', message: 'Note added.', type: 'success');
    }

    public function openConvertForm(): void
    {
        $this->business_name = $this->lead->company_name ?: $this->lead->client_name;
        $this->owner_name = $this->lead->client_name;
        $this->owner_contact = $this->lead->email ?: $this->lead->phone;
        $this->showConvertForm = true;
    }

    public function convertToClient(): void
    {
        if (! Auth::user()->isSalesExec()) {
            return;
        }

        $this->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:1000',
            'owner_name' => 'nullable|string|max:255',
            'owner_designation' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
        ]);

        $client = Client::create([
            'lead_id' => $this->lead->id,
            'sales_person_id' => $this->lead->sales_person_id,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'business_address' => $this->business_address,
            'owner_name' => $this->owner_name,
            'owner_designation' => $this->owner_designation,
            'owner_contact' => $this->owner_contact,
        ]);

        $this->dispatch('toast', message: 'Lead converted to client!', type: 'success');
        $this->redirect(route('sales.clients.show', $client), navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.lead-show', [
            'client' => $this->lead->client,
        ]);
    }
}
