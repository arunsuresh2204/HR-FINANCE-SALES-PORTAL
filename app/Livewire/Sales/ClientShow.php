<?php

namespace App\Livewire\Sales;

use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClientShow extends Component
{
    use WithFileUploads;

    public Client $client;

    public bool $showBusinessForm = false;

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

    #[Validate('nullable|file|max:10240')]
    public $project_requirement_file = null;

    public ?int $assigned_to = null;

    public bool $showDeveloperForm = false;

    public ?int $managingProjectId = null;

    public array $developer_ids = [];

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

    public array $tasks = [];

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->agreement_effective_date = $client->agreement_effective_date?->toDateString() ?? '';
        $this->agreement_scope_summary = $client->agreement_scope_summary ?? '';
    }

    public function openBusinessForm(): void
    {
        $this->business_name = $this->client->business_name;
        $this->business_type = $this->client->business_type ?? '';
        $this->business_address = $this->client->business_address ?? '';
        $this->owner_name = $this->client->owner_name ?? '';
        $this->owner_designation = $this->client->owner_designation ?? '';
        $this->owner_contact = $this->client->owner_contact ?? '';
        $this->resetValidation();
        $this->showBusinessForm = true;
    }

    public function saveBusinessDetails(): void
    {
        $this->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:1000',
            'owner_name' => 'nullable|string|max:255',
            'owner_designation' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
        ]);

        $this->client->update([
            'business_name' => $this->business_name,
            'business_type' => $this->business_type ?: null,
            'business_address' => $this->business_address ?: null,
            'owner_name' => $this->owner_name ?: null,
            'owner_designation' => $this->owner_designation ?: null,
            'owner_contact' => $this->owner_contact ?: null,
        ]);

        $this->showBusinessForm = false;
        $this->dispatch('toast', message: 'Business details saved.', type: 'success');
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
        $this->reset(['project_name', 'project_description', 'project_requirement_file']);
        $this->assigned_to = Auth::user()->isManager() || Auth::user()->isSuperAdmin() ? Auth::id() : null;
        $this->resetValidation();
        $this->showProjectForm = true;
    }

    public function submitProject(): void
    {
        $authUser = Auth::user();
        $canManage = $authUser->isManager() || $authUser->isSuperAdmin();

        $this->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string|max:1000',
            'project_requirement_file' => 'nullable|file|max:10240',
            'assigned_to' => $canManage ? 'nullable|exists:users,id' : 'required|exists:users,id',
        ]);

        if ($this->assigned_to) {
            $assignee = User::findOrFail($this->assigned_to);

            if (! $assignee->isManager() && ! $assignee->isSuperAdmin() && ! $assignee->isTeamLead()) {
                $this->addError('assigned_to', 'Projects can only be assigned to a manager, team leader, or an owner.');

                return;
            }
        }

        Project::create([
            'client_id' => $this->client->id,
            'created_by' => Auth::id(),
            'assigned_to' => $this->assigned_to ?: ($canManage ? Auth::id() : null),
            'name' => $this->project_name,
            'description' => $this->project_description,
            'requirement_file' => $this->project_requirement_file?->store('project-requirements', 'public'),
            'status' => 'active',
        ]);

        $this->showProjectForm = false;
        $this->dispatch('toast', message: 'Project created.', type: 'success');
    }

    protected function canManageProject(Project $project): bool
    {
        $authUser = Auth::user();

        return $authUser->isManager() || $authUser->isSuperAdmin() || $project->assigned_to === $authUser->id;
    }

    public function openDeveloperForm(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        if (! $this->canManageProject($project)) {
            return;
        }

        $this->managingProjectId = $projectId;
        $this->developer_ids = $project->developers()->pluck('users.id')->all();
        $this->resetValidation();
        $this->showDeveloperForm = true;
    }

    public function saveDevelopers(): void
    {
        $project = Project::findOrFail($this->managingProjectId);

        if (! $this->canManageProject($project)) {
            return;
        }

        $project->developers()->sync($this->developer_ids);

        $this->showDeveloperForm = false;
        $this->dispatch('toast', message: 'Developers assigned.', type: 'success');
    }

    public function openBillingForm(): void
    {
        $this->reset(['project_id', 'amount', 'milestone_description']);
        $this->currency = 'INR';
        $this->billing_type = 'milestone';
        $this->tasks = [['description' => '', 'hours' => '', 'rate' => '']];
        $this->resetValidation();
        $this->showBillingForm = true;
    }

    public function addTaskRow(): void
    {
        $lastRate = end($this->tasks)['rate'] ?? '';
        $this->tasks[] = ['description' => '', 'hours' => '', 'rate' => $lastRate];
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
                'tasks' => 'required|array|min:1',
                'tasks.*.description' => 'required|string|max:255',
                'tasks.*.hours' => 'required|numeric|min:0.25|max:24',
                'tasks.*.rate' => 'required|numeric|min:0.01',
            ]);

            $amount = array_sum(array_map(
                fn ($task) => (float) $task['hours'] * (float) $task['rate'],
                $this->tasks
            ));

            DB::transaction(function () use ($amount) {
                $billingRequest = BillingRequest::create([
                    'client_id' => $this->client->id,
                    'project_id' => $this->project_id,
                    'created_by' => Auth::id(),
                    'currency' => $this->currency,
                    'billing_type' => 'hourly',
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                foreach ($this->tasks as $task) {
                    $billingRequest->tasks()->create([
                        'task_description' => $task['description'],
                        'hours' => $task['hours'],
                        'rate' => $task['rate'],
                    ]);
                }
            });
        }

        $this->showBillingForm = false;
        $this->dispatch('toast', message: 'Billing request sent to Finance.', type: 'success');
    }

    public function render()
    {
        $authUser = Auth::user();

        return view('livewire.sales.client-show', [
            'projects' => $this->client->projects()->with(['assignedTo', 'developers'])->latest()->get(),
            'billingRequests' => $this->client->billingRequests()->with('tasks', 'project')->latest()->get(),
            'invoices' => $this->client->invoices()->latest()->get(),
            'totalHours' => $this->client->timesheets()->sum('hours'),
            'billableHours' => $this->client->billableHours(),
            'canManageProjects' => $authUser->isManager() || $authUser->isSuperAdmin(),
            'managersAndOwners' => User::role(['manager_engineering', 'team_lead_it', 'super_admin'])->orderBy('name')->get(),
            'developersList' => User::role('programmer')->orderBy('name')->get(),
        ]);
    }
}
