<?php

namespace App\Livewire\Sales;

use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClientShow extends Component
{
    use WithFileUploads;

    public Client $client;

    public bool $showAgreementForm = false;

    #[Validate('nullable|file|max:10240')]
    public $agreement_file = null;

    #[Validate('nullable|date')]
    public string $agreement_effective_date = '';

    #[Validate('nullable|string|max:1000')]
    public string $agreement_scope_summary = '';

    public bool $showProjectForm = false;

    #[Validate('required|string|max:255')]
    public string $project_name = '';

    #[Validate('nullable|string|max:1000')]
    public string $project_description = '';

    public bool $showBillingForm = false;

    #[Validate('nullable|exists:projects,id')]
    public ?int $project_id = null;

    #[Validate('required|in:INR,USD,EUR')]
    public string $currency = 'INR';

    #[Validate('required|in:milestone,hourly')]
    public string $billing_type = 'milestone';

    #[Validate('required_if:billing_type,milestone|nullable|numeric|min:0.01')]
    public string $amount = '';

    #[Validate('required_if:billing_type,milestone|nullable|string|max:255')]
    public string $milestone_description = '';

    #[Validate('required_if:billing_type,hourly|nullable|numeric|min:0.01')]
    public string $hourly_rate = '';

    public array $tasks = [];

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->agreement_effective_date = $client->agreement_effective_date?->toDateString() ?? '';
        $this->agreement_scope_summary = $client->agreement_scope_summary ?? '';
    }

    public function saveAgreement(): void
    {
        $this->validate();

        $data = [
            'agreement_effective_date' => $this->agreement_effective_date ?: null,
            'agreement_scope_summary' => $this->agreement_scope_summary,
        ];

        if ($this->agreement_file) {
            $data['agreement_file'] = $this->agreement_file->store('agreements', 'public');
        }

        $this->client->update($data);
        $this->showAgreementForm = false;
        $this->dispatch('toast', message: 'Agreement details saved.', type: 'success');
    }

    public function openProjectForm(): void
    {
        $this->reset(['project_name', 'project_description']);
        $this->resetValidation();
        $this->showProjectForm = true;
    }

    public function submitProject(): void
    {
        $this->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string|max:1000',
        ]);

        Project::create([
            'client_id' => $this->client->id,
            'created_by' => Auth::id(),
            'name' => $this->project_name,
            'description' => $this->project_description,
            'status' => 'active',
        ]);

        $this->showProjectForm = false;
        $this->dispatch('toast', message: 'Project created.', type: 'success');
    }

    public function openBillingForm(): void
    {
        $this->reset(['project_id', 'amount', 'milestone_description', 'hourly_rate']);
        $this->currency = 'INR';
        $this->billing_type = 'milestone';
        $this->tasks = [['description' => '', 'hours' => '']];
        $this->resetValidation();
        $this->showBillingForm = true;
    }

    public function addTaskRow(): void
    {
        $this->tasks[] = ['description' => '', 'hours' => ''];
    }

    public function removeTaskRow(int $index): void
    {
        if (count($this->tasks) > 1) {
            unset($this->tasks[$index]);
            $this->tasks = array_values($this->tasks);
        }
    }

    public function submitBillingRequest(): void
    {
        if ($this->billing_type === 'milestone') {
            $this->validate([
                'project_id' => 'nullable|exists:projects,id',
                'currency' => 'required|in:INR,USD,EUR',
                'amount' => 'required|numeric|min:0.01',
                'milestone_description' => 'required|string|max:255',
            ]);

            BillingRequest::create([
                'client_id' => $this->client->id,
                'project_id' => $this->project_id,
                'created_by' => Auth::id(),
                'currency' => $this->currency,
                'billing_type' => 'milestone',
                'amount' => $this->amount,
                'milestone_description' => $this->milestone_description,
                'status' => 'pending',
            ]);
        } else {
            $this->validate([
                'project_id' => 'nullable|exists:projects,id',
                'currency' => 'required|in:INR,USD,EUR',
                'hourly_rate' => 'required|numeric|min:0.01',
                'tasks' => 'required|array|min:1',
                'tasks.*.description' => 'required|string|max:255',
                'tasks.*.hours' => 'required|numeric|min:0.25|max:24',
            ]);

            $totalHours = array_sum(array_column($this->tasks, 'hours'));
            $amount = $totalHours * (float) $this->hourly_rate;

            DB::transaction(function () use ($amount) {
                $billingRequest = BillingRequest::create([
                    'client_id' => $this->client->id,
                    'project_id' => $this->project_id,
                    'created_by' => Auth::id(),
                    'currency' => $this->currency,
                    'billing_type' => 'hourly',
                    'amount' => $amount,
                    'hourly_rate' => $this->hourly_rate,
                    'status' => 'pending',
                ]);

                foreach ($this->tasks as $task) {
                    $billingRequest->tasks()->create([
                        'task_description' => $task['description'],
                        'hours' => $task['hours'],
                    ]);
                }
            });
        }

        $this->showBillingForm = false;
        $this->dispatch('toast', message: 'Billing request sent to Finance.', type: 'success');
    }

    public function render()
    {
        return view('livewire.sales.client-show', [
            'projects' => $this->client->projects()->latest()->get(),
            'billingRequests' => $this->client->billingRequests()->with('tasks', 'project')->latest()->get(),
            'invoices' => $this->client->invoices()->latest()->get(),
            'totalHours' => $this->client->timesheets()->sum('hours'),
            'billableHours' => $this->client->billableHours(),
        ]);
    }
}
