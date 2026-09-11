<?php

namespace App\Livewire\Work;

use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyProjects extends Component
{
    public bool $showDeveloperForm = false;

    public ?int $managingProjectId = null;

    public array $developer_ids = [];

    protected function canManage(Project $project): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin() || $authUser->isManager() || $project->assigned_to === $authUser->id;
    }

    public function openDeveloperForm(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        if (! $this->canManage($project)) {
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

        if (! $this->canManage($project)) {
            return;
        }

        $project->developers()->sync($this->developer_ids);

        $this->showDeveloperForm = false;
        $this->dispatch('toast', message: 'Developers assigned.', type: 'success');
    }

    public function render()
    {
        $authUser = Auth::user();

        $rows = Project::with(['client', 'developers'])
            ->where('assigned_to', $authUser->id)
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

        return view('livewire.work.my-projects', [
            'rows' => $rows,
            'developersList' => User::role('programmer')->orderBy('name')->get(),
        ]);
    }
}
