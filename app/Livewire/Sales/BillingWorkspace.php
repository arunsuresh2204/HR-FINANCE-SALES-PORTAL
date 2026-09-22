<?php

namespace App\Livewire\Sales;

use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BillingWorkspace extends Component
{
    public ?int $viewingClientId = null;

    public ?int $viewingProjectId = null;

    public array $selectedTaskIds = [];

    protected function visibleClients(): Builder
    {
        $authUser = Auth::user();

        if ($authUser->isSuperAdmin() || $authUser->isFinanceAdmin() || $authUser->isManager()) {
            return Client::query();
        }

        if ($authUser->teamVisibilityFor('access_sales_clients')) {
            return Client::whereIn('sales_person_id', $authUser->allDescendants()->pluck('id')->push($authUser->id));
        }

        return Client::where('sales_person_id', $authUser->id);
    }

    /**
     * Ready-to-bill tasks nobody has picked up into a live billing request
     * yet. A rejected billing request doesn't count — the task is free
     * again once that happens.
     */
    protected function unclaimedReadyTasks(): Builder
    {
        return Task::where('ready_to_bill', true)
            ->where('cancelled', false)
            ->whereDoesntHave('billingRequests', fn ($q) => $q->whereIn('status', ['pending', 'invoiced']));
    }

    public function viewClient(int $clientId): void
    {
        if (! $this->visibleClients()->whereKey($clientId)->exists()) {
            return;
        }

        $this->viewingClientId = $clientId;
        $this->viewingProjectId = null;
        $this->selectedTaskIds = [];
    }

    public function backToClients(): void
    {
        $this->viewingClientId = null;
        $this->viewingProjectId = null;
        $this->selectedTaskIds = [];
    }

    public function viewProject(int $projectId): void
    {
        $project = Project::find($projectId);

        if (! $project || $project->client_id !== $this->viewingClientId) {
            return;
        }

        $this->viewingProjectId = $projectId;
        $this->selectedTaskIds = [];
    }

    public function backToProjects(): void
    {
        $this->viewingProjectId = null;
        $this->selectedTaskIds = [];
    }

    public function createBillingRequest(): void
    {
        $authUser = Auth::user();
        $project = $this->viewingProjectId ? Project::with('client')->find($this->viewingProjectId) : null;

        if (! $project || $project->client_id !== $this->viewingClientId || ! $this->visibleClients()->whereKey($project->client_id)->exists()) {
            return;
        }

        if (empty($this->selectedTaskIds)) {
            $this->dispatch('toast', message: 'Select at least one ready task.', type: 'error');

            return;
        }

        $tasks = $this->unclaimedReadyTasks()
            ->where('project_id', $project->id)
            ->whereIn('id', $this->selectedTaskIds)
            ->get();

        if ($tasks->isEmpty()) {
            $this->dispatch('toast', message: 'Select at least one ready task.', type: 'error');

            return;
        }

        $currencies = $tasks->pluck('currency')->unique();

        if ($currencies->count() > 1) {
            $this->dispatch('toast', message: 'Selected tasks use different currencies — bill them separately.', type: 'error');

            return;
        }

        $billingRequest = BillingRequest::create([
            'client_id' => $project->client_id,
            'project_id' => $project->id,
            'created_by' => $authUser->id,
            'currency' => $currencies->first(),
            'billing_type' => 'milestone',
            'amount' => $tasks->sum(fn (Task $t) => $t->effectiveAmount()),
            'milestone_description' => $tasks->count().' '.str('task')->plural($tasks->count()).' — '.$project->name,
            'status' => 'pending',
        ]);

        $billingRequest->billedTasks()->attach($tasks->pluck('id'));

        Notification::sendToMany(
            User::role(['finance_admin', 'super_admin'])->get(),
            'billing_request_ready',
            'New billing request',
            $project->client->business_name.' — '.$project->name,
            route('finance.billing-requests')
        );

        $this->selectedTaskIds = [];
        $this->dispatch('toast', message: 'Billing request sent to Finance.', type: 'success');
    }

    public function render()
    {
        $clientIds = $this->visibleClients()->pluck('id');

        $readyCountsByClient = $this->unclaimedReadyTasks()
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->whereIn('projects.client_id', $clientIds)
            ->selectRaw('projects.client_id as client_id, count(*) as cnt')
            ->groupBy('projects.client_id')
            ->pluck('cnt', 'client_id');

        $clients = Client::whereIn('id', $readyCountsByClient->keys())
            ->get()
            ->map(fn (Client $client) => ['client' => $client, 'readyCount' => $readyCountsByClient[$client->id] ?? 0])
            ->sortByDesc('readyCount')
            ->values();

        $viewingClient = null;
        $projects = collect();
        $viewingProject = null;
        $readyTasks = collect();

        if ($this->viewingClientId && $clientIds->contains($this->viewingClientId)) {
            $viewingClient = Client::find($this->viewingClientId);

            $projectIds = $viewingClient->projects()->pluck('id');

            $readyCountsByProject = $this->unclaimedReadyTasks()
                ->whereIn('project_id', $projectIds)
                ->selectRaw('project_id, count(*) as cnt')
                ->groupBy('project_id')
                ->pluck('cnt', 'project_id');

            $projects = Project::whereIn('id', $readyCountsByProject->keys())
                ->get()
                ->map(fn (Project $project) => ['project' => $project, 'readyCount' => $readyCountsByProject[$project->id] ?? 0])
                ->sortByDesc('readyCount')
                ->values();
        }

        if ($this->viewingProjectId) {
            $viewingProject = Project::find($this->viewingProjectId);

            if ($viewingProject && $viewingProject->client_id === $this->viewingClientId) {
                $readyTasks = $this->unclaimedReadyTasks()
                    ->where('project_id', $this->viewingProjectId)
                    ->with('category')
                    ->orderBy('end_date')
                    ->get();
            } else {
                $viewingProject = null;
            }
        }

        $selectedTasks = $readyTasks->whereIn('id', $this->selectedTaskIds);
        $selectedTotals = $selectedTasks->groupBy('currency')->map(fn ($group) => $group->sum(fn (Task $t) => $t->effectiveAmount()));

        return view('livewire.sales.billing-workspace', [
            'clients' => $clients,
            'viewingClient' => $viewingClient,
            'projects' => $projects,
            'viewingProject' => $viewingProject,
            'readyTasks' => $readyTasks,
            'selectedTotals' => $selectedTotals,
        ]);
    }
}
