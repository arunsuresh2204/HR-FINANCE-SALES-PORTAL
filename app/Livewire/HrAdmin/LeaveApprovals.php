<?php

namespace App\Livewire\HrAdmin;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LeaveApprovals extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public function approve(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->needsCertificate()) {
            $this->dispatch('toast', message: 'This sick leave needs a medical certificate before it can be approved. Request it from the employee first.', type: 'error');

            return;
        }

        $leaveRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatch('toast', message: 'Leave request approved.', type: 'success');
    }

    public function reject(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatch('toast', message: 'Leave request rejected.', type: 'success');
    }

    public function requestCertificate(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->update(['certificate_requested_at' => now()]);
        $this->dispatch('toast', message: "Certificate request sent to {$leaveRequest->user->name}.", type: 'success');
    }

    public function render()
    {
        $query = LeaveRequest::with('user')->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.hr-admin.leave-approvals', [
            'requests' => $query->paginate(10),
        ]);
    }
}
