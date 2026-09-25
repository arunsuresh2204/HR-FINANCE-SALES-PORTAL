<?php

namespace App\Livewire\Work;

use App\Models\Notification;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MyProjects extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function visibleProjects(User $authUser)
    {
        if ($authUser->isSuperAdmin() || $authUser->isManager()) {
            return Project::query();
        }

        return Project::where('assigned_to', $authUser->id)
            ->orWhereHas('developers', fn ($q) => $q->where('users.id', $authUser->id))
            ->orWhereHas('tasks', function ($q) use ($authUser) {
                $q->where('cancelled', false)
                    ->where('status', '!=', 'done')
                    ->whereHas('assignees', fn ($q2) => $q2->where('users.id', $authUser->id));
            });
    }

    public function render()
    {
        $authUser = Auth::user();
        $isManager = $authUser->isManager() || $authUser->isSuperAdmin();

        $unreadCounts = Notification::where('user_id', $authUser->id)
            ->whereNull('read_at')
            ->get()
            ->groupBy('url')
            ->map->count();

        $allRows = $this->visibleProjects($authUser)
            ->with(['client', 'attachments'])
            ->latest()
            ->get()
            ->map(function (Project $project) use ($isManager, $unreadCounts) {
                $entries = Timesheet::where('project_id', $project->id);

                return [
                    'project' => $project,
                    'totalHours' => (clone $entries)->sum('hours'),
                    'blockedEntries' => (clone $entries)->where('status', 'blocked')->count(),
                    'pendingTaskCount' => $isManager ? $project->tasks()->where('pending_approval', true)->count() : 0,
                    'unreadCount' => $unreadCounts->get(route('work.project-show', $project), 0),
                ];
            });

        $rows = $allRows->filter(function (array $row) {
            $project = $row['project'];

            if ($this->statusFilter === 'awaiting_estimate') {
                if (! $project->needsEstimate()) {
                    return false;
                }
            } elseif ($this->statusFilter !== 'all' && $project->status !== $this->statusFilter) {
                return false;
            }

            if ($this->search !== '') {
                $needle = strtolower($this->search);
                $haystack = strtolower($project->name.' '.$project->client->business_name);

                if (! str_contains($haystack, $needle)) {
                    return false;
                }
            }

            return true;
        })->values();

        $perPage = 5;
        $page = $this->getPage();
        $pagedRows = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('livewire.work.my-projects', [
            'rows' => $pagedRows,
            'assignedProjectsCount' => $allRows->count(),
            'totalHoursAll' => $allRows->sum('totalHours'),
            'blockedEntriesAll' => $allRows->sum('blockedEntries'),
        ]);
    }
}
