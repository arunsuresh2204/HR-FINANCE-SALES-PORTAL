<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LeadPipeline extends Component
{
    public bool $showForm = false;

    public string $salesPersonFilter = '';

    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $client_name = '';

    #[Validate('nullable|string|max:255')]
    public string $company_name = '';

    #[Validate('nullable|string|max:255')]
    public string $country = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:50')]
    public string $phone = '';

    #[Validate('nullable|string|max:50')]
    public string $whatsapp = '';

    #[Validate('required|string|max:2000')]
    public string $requirement = '';

    #[Validate('required|in:web,mobile,social_media,pet_product,other')]
    public string $service_type = 'web';

    #[Validate('required|string|max:255')]
    public string $source = '';

    #[Validate('nullable|date')]
    public string $follow_up_date = '';

    public const STATUSES = ['new', 'contacted', 'proposal_sent', 'negotiation', 'won', 'lost'];

    public function openForm(): void
    {
        $this->reset(['client_name', 'company_name', 'country', 'email', 'phone', 'whatsapp', 'requirement', 'source', 'follow_up_date']);
        $this->service_type = 'web';
        $this->showForm = true;
    }

    public function createLead(): void
    {
        $this->validate();

        Lead::create([
            'sales_person_id' => Auth::id(),
            'client_name' => $this->client_name,
            'company_name' => $this->company_name,
            'country' => $this->country,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'requirement' => $this->requirement,
            'service_type' => $this->service_type,
            'source' => $this->source,
            'status' => 'new',
            'follow_up_date' => $this->follow_up_date ?: null,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Lead created.', type: 'success');
    }

    public function moveStatus(Lead $lead, string $status): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            return;
        }

        $lead->update(['status' => $status]);

        if ($status === 'won') {
            $this->dispatch('toast', message: 'Lead marked as won! Convert it to a client from the lead page.', type: 'success');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Lead::with('salesPerson')->latest();

        if (! $user->isSuperAdmin()) {
            $query->where('sales_person_id', $user->id);
        } elseif ($this->salesPersonFilter) {
            $query->where('sales_person_id', $this->salesPersonFilter);
        }

        if ($this->search) {
            $query->where(fn ($q) => $q->where('client_name', 'like', "%{$this->search}%")->orWhere('company_name', 'like', "%{$this->search}%"));
        }

        $leads = $query->get()->groupBy('status');

        return view('livewire.sales.lead-pipeline', [
            'columns' => collect(self::STATUSES)->mapWithKeys(fn ($status) => [$status => $leads->get($status, collect())]),
            'salesPeople' => $user->isSuperAdmin() ? User::role('sales_exec')->orderBy('name')->get() : collect(),
        ]);
    }
}
