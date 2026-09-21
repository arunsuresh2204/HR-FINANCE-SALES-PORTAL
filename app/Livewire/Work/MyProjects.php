<?php

namespace App\Livewire\Work;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyProjects extends Component
{
    public string $search = '';

    public string $statusFilter = 'all';

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    protected function visibleProjects(User $authUser)
    {
        if ($authUser->isSuperAdmin() || $authUser->isManager()) {
            return Project::query();
        }

        if ($authUser->isTeamLead()) {
            return Project::where('assigned_to', $authUser->id);
        }

        return Project::where('assigned_to', $authUser->id)
            ->orWhereHas('developers', fn ($q) => $q->where('users.id', $authUser->id));
    }

    public function render()
    {
        $authUser = Auth::user();
        $isManager = $authUser->isManager() || $authUser->isSuperAdmin();

        $allRows = $this->visibleProjects($authUser)
            ->with('client')
            ->latest()
            ->get()
            ->map(function (Project $project) use ($isManager) {
                $entries = Timesheet::where('project_id', $project->id);

                return [
                    'project' => $project,
                    'totalHours' => (clone $entries)->sum('hours'),
                    'blockedEntries' => (clone $entries)->where('status', 'blocked')->count(),
                    'pendingTaskCount' => $isManager ? $project->tasks()->where('pending_approval', true)->count() : 0,
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

        return view('livewire.work.my-projects', [
            'rows' => $rows,
            'assignedProjectsCount' => $allRows->count(),
            'totalHoursAll' => $allRows->sum('totalHours'),
            'blockedEntriesAll' => $allRows->sum('blockedEntries'),
        ]);
    }
}
