<?php

namespace App\Livewire\Work;

use App\Models\Client;
use App\Models\MarketingLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class MarketingLogIndex extends Component
{
    use WithPagination;

    public bool $showForm = false;

    #[Validate('nullable|exists:clients,id')]
    public ?int $client_id = null;

    #[Validate('required|boolean')]
    public bool $is_in_house_product = false;

    #[Validate('required|date|before_or_equal:today')]
    public string $work_date = '';

    #[Validate('required|string|max:255')]
    public string $platform = '';

    #[Validate('required|in:content,ad,engagement,other')]
    public string $task_type = 'content';

    #[Validate('required|numeric|min:0.25|max:24')]
    public string $hours = '';

    #[Validate('nullable|string|max:1000')]
    public string $notes = '';

    #[Validate('nullable|url')]
    public string $deliverable_link = '';

    public function openForm(): void
    {
        $this->reset(['client_id', 'is_in_house_product', 'platform', 'hours', 'notes', 'deliverable_link']);
        $this->work_date = now()->toDateString();
        $this->task_type = 'content';
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        MarketingLog::create([
            'user_id' => Auth::id(),
            'client_id' => $this->client_id ?: null,
            'work_date' => $this->work_date,
            'platform' => $this->platform,
            'task_type' => $this->task_type,
            'is_in_house_product' => $this->is_in_house_product,
            'hours' => $this->hours,
            'notes' => $this->notes,
            'deliverable_link' => $this->deliverable_link ?: null,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Marketing log saved.', type: 'success');
    }

    public function render()
    {
        $userId = Auth::id();

        return view('livewire.work.marketing-log-index', [
            'entries' => MarketingLog::where('user_id', $userId)->latest('work_date')->paginate(10),
            'clients' => Client::orderBy('business_name')->get(),
            'weekHours' => MarketingLog::where('user_id', $userId)->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours'),
        ]);
    }
}
