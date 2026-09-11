<?php

namespace App\Livewire\Work;

use App\Models\Timesheet;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class TimesheetIndex extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $tab = 'mine';

    public string $memberFilter = '';

    #[Validate('nullable|string|max:255')]
    public string $project_name = '';

    #[Validate('required|date|before_or_equal:today')]
    public string $work_date = '';

    #[Validate('required|string|max:1000')]
    public string $task_description = '';

    #[Validate('required|numeric|min:0.25|max:24')]
    public string $hours = '';

    #[Validate('required|in:in_progress,completed,blocked')]
    public string $status = 'in_progress';

    #[Validate('required_if:status,blocked|nullable|string|max:500')]
    public string $blocked_reason = '';

    public function openForm(): void
    {
        $this->reset(['project_name', 'task_description', 'hours', 'blocked_reason']);
        $this->work_date = now()->toDateString();
        $this->status = 'in_progress';
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        Timesheet::create([
            'user_id' => Auth::id(),
            'project_name' => $this->project_name,
            'work_date' => $this->work_date,
            'task_description' => $this->task_description,
            'hours' => $this->hours,
            'status' => $this->status,
            'blocked_reason' => $this->status === 'blocked' ? $this->blocked_reason : null,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Timesheet entry logged.', type: 'success');
    }

    public function setTab(string $tab): void
    {
        $canViewTeam = Auth::user()->isManager();
        $this->tab = ($tab === 'team' && $canViewTeam) ? 'team' : 'mine';
        $this->memberFilter = '';
        $this->resetPage();
    }

    public function updatingMemberFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $userId = $user->id;
        $canViewTeam = $user->isManager();

        if ($this->tab === 'team' && $canViewTeam) {
            $team = $user->allDescendants();
            $teamIds = $team->pluck('id');

            $entriesQuery = Timesheet::with('user')->whereIn('user_id', $teamIds)->latest('work_date');

            if ($this->memberFilter && $teamIds->contains((int) $this->memberFilter)) {
                $entriesQuery->where('user_id', $this->memberFilter);
            }

            return view('livewire.work.timesheet-index', [
                'entries' => $entriesQuery->paginate(10),
                'weekHours' => Timesheet::whereIn('user_id', $teamIds)->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours'),
                'monthHours' => Timesheet::whereIn('user_id', $teamIds)->whereMonth('work_date', now()->month)->whereYear('work_date', now()->year)->sum('hours'),
                'canViewTeam' => $canViewTeam,
                'teamMembers' => $team->sortBy('name')->values(),
            ]);
        }

        return view('livewire.work.timesheet-index', [
            'entries' => Timesheet::where('user_id', $userId)->latest('work_date')->paginate(10),
            'weekHours' => Timesheet::where('user_id', $userId)->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours'),
            'monthHours' => Timesheet::where('user_id', $userId)->whereMonth('work_date', now()->month)->whereYear('work_date', now()->year)->sum('hours'),
            'canViewTeam' => $canViewTeam,
            'teamMembers' => collect(),
        ]);
    }
}
