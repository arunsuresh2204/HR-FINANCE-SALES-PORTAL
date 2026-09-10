<?php

namespace App\Livewire\Sales;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Carbon\Carbon;
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

    public string $range = 'week';

    public string $anchorDate = '';

    public string $monthPicker = '';

    public bool $showRequirementModal = false;

    public ?int $viewingLeadId = null;

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

    #[Validate('required|string|max:255|exists:lead_sources,name')]
    public string $source = '';

    #[Validate('nullable|string|max:255')]
    public string $contact_link = '';

    #[Validate('required|date')]
    public string $contacted_date = '';

    public array $statuses = [];

    public array $comments = [];

    public function mount(): void
    {
        $this->anchorDate = now()->toDateString();
        $this->monthPicker = now()->format('Y-m');
        $this->contacted_date = now()->toDateString();
    }

    public function setRange(string $range): void
    {
        $this->range = in_array($range, ['day', 'week', 'month'], true) ? $range : 'week';
        $this->resetPage();
    }

    public function prevPeriod(): void
    {
        $date = Carbon::parse($this->anchorDate);
        $this->anchorDate = match ($this->range) {
            'day' => $date->subDay()->toDateString(),
            'week' => $date->subWeek()->toDateString(),
            default => $date->subMonthNoOverflow()->toDateString(),
        };
        $this->monthPicker = Carbon::parse($this->anchorDate)->format('Y-m');
        $this->resetPage();
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->anchorDate);
        $this->anchorDate = match ($this->range) {
            'day' => $date->addDay()->toDateString(),
            'week' => $date->addWeek()->toDateString(),
            default => $date->addMonthNoOverflow()->toDateString(),
        };
        $this->monthPicker = Carbon::parse($this->anchorDate)->format('Y-m');
        $this->resetPage();
    }

    public function updatedMonthPicker(string $value): void
    {
        if (! $value) {
            return;
        }

        $this->range = 'month';
        $this->anchorDate = Carbon::createFromFormat('Y-m', $value)->startOfMonth()->toDateString();
        $this->resetPage();
    }

    public function viewRequirement(int $leadId): void
    {
        $this->viewingLeadId = $leadId;
        $this->showRequirementModal = true;
    }

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
            'contacted_date' => $this->contacted_date ?: now()->toDateString(),
        ]);

        $this->reset(['client_name', 'company_name', 'country', 'requirement', 'contact_link']);
        $this->contacted_date = now()->toDateString();
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

        $anchor = Carbon::parse($this->anchorDate ?: now());

        if ($this->range === 'day') {
            $rangeStart = $anchor->copy()->startOfDay();
            $rangeEnd = $anchor->copy()->endOfDay();
            $rangeLabel = $rangeStart->format('l, M j, Y');
        } elseif ($this->range === 'week') {
            $rangeStart = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $rangeEnd = $anchor->copy()->endOfWeek(Carbon::SUNDAY);
            $rangeLabel = $rangeStart->format('M j').' – '.$rangeEnd->format('M j, Y');
        } else {
            $rangeStart = $anchor->copy()->startOfMonth();
            $rangeEnd = $anchor->copy()->endOfMonth();
            $rangeLabel = $rangeStart->format('F Y');
        }

        // contacted_date is stored with a time component, so compare against full
        // start/end-of-day bounds rather than bare date strings — otherwise a lead
        // dated exactly on the range's last day gets excluded by string comparison.
        $query->whereBetween('contacted_date', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()]);

        $leads = $query->paginate(25);

        $this->statuses = $leads->pluck('status', 'id')->all();
        $this->comments = $leads->pluck('comment', 'id')->map(fn ($c) => $c ?? '')->all();

        return view('livewire.sales.lead-pipeline', [
            'leads' => $leads,
            'owners' => $user->isSuperAdmin() ? User::role(['sales_exec', 'marketer'])->orderBy('name')->get() : collect(),
            'statusOptions' => Lead::STATUSES,
            'rangeLabel' => $rangeLabel,
            'viewingLead' => $this->viewingLeadId ? Lead::find($this->viewingLeadId) : null,
            'leadSources' => LeadSource::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
