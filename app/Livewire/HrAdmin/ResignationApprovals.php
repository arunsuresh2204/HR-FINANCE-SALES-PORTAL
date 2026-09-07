<?php

namespace App\Livewire\HrAdmin;

use App\Models\OffboardingTask;
use App\Models\Resignation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResignationApprovals extends Component
{
    public const DEFAULT_TASKS = [
        'Return company laptop / hardware',
        'Revoke email & system access',
        'Return ID card',
        'Knowledge transfer completed',
        'Final settlement processed',
    ];

    public function accept(Resignation $resignation): void
    {
        $resignation->update([
            'status' => 'accepted',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $resignation->user->update(['employment_status' => 'on_notice']);

        foreach (self::DEFAULT_TASKS as $task) {
            OffboardingTask::create(['resignation_id' => $resignation->id, 'task' => $task]);
        }

        $this->dispatch('toast', message: 'Resignation accepted. Offboarding checklist created.', type: 'success');
    }

    public function reject(Resignation $resignation): void
    {
        $resignation->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatch('toast', message: 'Resignation rejected.', type: 'success');
    }

    public function toggleTask(OffboardingTask $offboardingTask): void
    {
        $offboardingTask->update(['completed' => ! $offboardingTask->completed]);

        $resignation = $offboardingTask->resignation;
        if ($resignation->offboardingTasks()->where('completed', false)->doesntExist()) {
            $resignation->update(['status' => 'completed']);
            $resignation->user->update(['employment_status' => 'offboarded']);
            $this->dispatch('toast', message: 'All offboarding tasks complete. Employee offboarded.', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.hr-admin.resignation-approvals', [
            'resignations' => Resignation::with(['user', 'offboardingTasks'])->latest()->get(),
        ]);
    }
}
