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

        $allRows = $this->visibleProjects($authUser)
            ->with(['client', 'developers'])
            ->latest()
            ->get()
            ->map(function (Project $project) {
                $developerRows = $project->developers->map(function (User $developer) use ($project) {
                    $entries = Timesheet::where('user_id', $developer->id)->where('project_id', $project->id);

                    return [
                        'user' => $developer,
                        'hours' => (clone $entries)->sum('hours'),
                        'completed' => (clone $entries)->where('status', 'completed')->count(),
                        'inProgress' => (clone $entries)->where('status', 'in_progress')->count(),
                        'blocked' => (clone $entries)->where('status', 'blocked')->count(),
                        'lastEntry' => (clone $entries)->latest('work_date')->first(),
                    ];
                });

                return [
                    'project' => $project,
                    'totalHours' => $developerRows->sum('hours'),
                    'completedEntries' => $developerRows->sum('completed'),
                    'inProgressEntries' => $developerRows->sum('inProgress'),
                    'blockedEntries' => $developerRows->sum('blocked'),
                    'developers' => $developerRows,
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
