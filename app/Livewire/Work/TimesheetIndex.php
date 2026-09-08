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

    public function openForm(): void
    {
        $this->reset(['project_name', 'task_description', 'hours']);
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
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Timesheet entry logged.', type: 'success');
    }

    public function render()
    {
        $userId = Auth::id();

        return view('livewire.work.timesheet-index', [
            'entries' => Timesheet::where('user_id', $userId)->latest('work_date')->paginate(10),
            'weekHours' => Timesheet::where('user_id', $userId)->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours'),
            'monthHours' => Timesheet::where('user_id', $userId)->whereMonth('work_date', now()->month)->whereYear('work_date', now()->year)->sum('hours'),
        ]);
    }
}
