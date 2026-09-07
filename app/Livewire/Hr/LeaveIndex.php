<?php

namespace App\Livewire\Hr;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveIndex extends Component
{
    use WithPagination;

    public bool $showForm = false;

    #[Validate('required|in:vacation,sick,unpaid,other')]
    public string $type = 'vacation';

    #[Validate('required|date|after_or_equal:today')]
    public string $start_date = '';

    #[Validate('required|date|after_or_equal:start_date')]
    public string $end_date = '';

    #[Validate('nullable|string|max:500')]
    public string $reason = '';

    public const ANNUAL_ENTITLEMENT = 18;

    public function openForm(): void
    {
        $this->reset(['type', 'start_date', 'end_date', 'reason']);
        $this->type = 'vacation';
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        $days = 0;
        $cursor = \Carbon\Carbon::parse($this->start_date);
        $end = \Carbon\Carbon::parse($this->end_date);
        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $days,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Leave request submitted for approval.', type: 'success');
    }

    public function cancel(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->user_id !== Auth::id() || $leaveRequest->status !== 'pending') {
            return;
        }

        $leaveRequest->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Leave request cancelled.', type: 'success');
    }

    public function render()
    {
        $userId = Auth::id();
        $usedDays = LeaveRequest::where('user_id', $userId)->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days');

        return view('livewire.hr.leave-index', [
            'requests' => LeaveRequest::where('user_id', $userId)->latest()->paginate(10),
            'usedDays' => $usedDays,
            'remainingDays' => max(self::ANNUAL_ENTITLEMENT - $usedDays, 0),
        ]);
    }
}
