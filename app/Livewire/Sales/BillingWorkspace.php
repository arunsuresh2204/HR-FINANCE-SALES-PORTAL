<?php

namespace App\Livewire\Sales;

use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BillingWorkspace extends Component
{
    public ?int $viewingClientId = null;

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
        $this->selectedTaskIds = [];
    }

    public function backToClients(): void
    {
        $this->viewingClientId = null;
        $this->selectedTaskIds = [];
    }

    /**
     * Tasks across a client's projects can only be bundled into one billing
     * request when they share a currency — a bundle can't be split across
     * two invoices in two currencies. Toggling in a task that would break
     * that is blocked outright, with a warning, rather than letting the
     * mismatch happen and only catching it at submit time.
     */
    public function toggleTaskSelection(int $taskId): void
    {
        if (in_array($taskId, $this->selectedTaskIds, true)) {
            $this->selectedTaskIds = array_values(array_diff($this->selectedTaskIds, [$taskId]));

            return;
        }

        if (! $this->viewingClientId) {
            return;
        }

        $task = $this->unclaimedReadyTasks()
            ->whereKey($taskId)
            ->whereHas('project', fn ($q) => $q->where('client_id', $this->viewingClientId))
            ->first();

        if (! $task) {
            return;
        }

        if ($this->selectedTaskIds) {
            $currentCurrency = Task::whereKey($this->selectedTaskIds)->value('currency');

            if ($currentCurrency && $currentCurrency !== $task->currency) {
                $this->dispatch(
                    'toast',
                    message: "Can't bundle a {$task->currency} task with your current {$currentCurrency} selection — different currencies can't share one invoice. Bill it separately.",
                    type: 'error'
                );

                return;
            }
        }

        $this->selectedTaskIds[] = $taskId;
    }

    public function createBillingRequest(): void
    {
        $authUser = Auth::user();
        $client = $this->viewingClientId ? Client::find($this->viewingClientId) : null;

        if (! $client || ! $this->visibleClients()->whereKey($client->id)->exists()) {
            return;
        }

        if (empty($this->selectedTaskIds)) {
            $this->dispatch('toast', message: 'Select at least one ready task.', type: 'error');

            return;
        }

        $tasks = $this->unclaimedReadyTasks()
            ->whereHas('project', fn ($q) => $q->where('client_id', $client->id))
            ->whereIn('id', $this->selectedTaskIds)
            ->with('project')
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

        $projects = $tasks->pluck('project')->unique('id')->values();
        $projectsLabel = $projects->count() === 1 ? $projects->first()->name : $projects->count().' projects — '.$projects->pluck('name')->implode(', ');

        $billingRequest = BillingRequest::create([
            'client_id' => $client->id,
            'project_id' => $projects->count() === 1 ? $projects->first()->id : null,
            'created_by' => $authUser->id,
            'currency' => $currencies->first(),
            'billing_type' => 'milestone',
            'amount' => $tasks->sum(fn (Task $t) => $t->effectiveAmount()),
            'milestone_description' => $tasks->count().' '.str('task')->plural($tasks->count()).' — '.$projectsLabel,
            'status' => 'pending',
        ]);

        $billingRequest->billedTasks()->attach($tasks->pluck('id'));

        Notification::sendToMany(
            User::role(['finance_admin', 'super_admin'])->get(),
            'billing_request_ready',
            'New billing request',
            $client->business_name.' — '.$projectsLabel,
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
        $tasksByProject = collect();

        if ($this->viewingClientId && $clientIds->contains($this->viewingClientId)) {
            $viewingClient = Client::find($this->viewingClientId);

            $readyTasks = $this->unclaimedReadyTasks()
                ->whereHas('project', fn ($q) => $q->where('client_id', $this->viewingClientId))
                ->with(['project', 'category'])
                ->orderBy('end_date')
                ->get();

            $tasksByProject = $readyTasks->groupBy(fn (Task $task) => $task->project->name)
                ->map(fn ($tasks) => $tasks->sortBy('end_date')->values());
        }

        $allReadyTasks = $tasksByProject->flatten(1);
        $selectedTasks = $allReadyTasks->whereIn('id', $this->selectedTaskIds);
        $selectedTotals = $selectedTasks->groupBy('currency')->map(fn ($group) => $group->sum(fn (Task $t) => $t->effectiveAmount()));
        $selectedCurrency = $selectedTasks->pluck('currency')->unique()->first();

        return view('livewire.sales.billing-workspace', [
            'clients' => $clients,
            'viewingClient' => $viewingClient,
            'tasksByProject' => $tasksByProject,
            'selectedTotals' => $selectedTotals,
            'selectedCurrency' => $selectedCurrency,
        ]);
    }
}
