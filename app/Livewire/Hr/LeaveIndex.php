<?php

namespace App\Livewire\Hr;

use App\Models\Holiday;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class LeaveIndex extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showForm = false;

    #[Validate('required|in:vacation,sick,unpaid,other')]
    public string $type = 'vacation';

    #[Validate('required|date|after_or_equal:today')]
    public string $start_date = '';

    #[Validate('required|date|after_or_equal:start_date')]
    public string $end_date = '';

    #[Validate('nullable|string|max:500')]
    public string $reason = '';

    #[Validate('nullable|file|max:5120|mimes:jpg,jpeg,png,pdf')]
    public $certificate = null;

    public const ANNUAL_ENTITLEMENT = 18;

    public bool $showCertUploadForm = false;

    public ?int $certUploadTargetId = null;

    #[Validate('required|file|max:5120|mimes:jpg,jpeg,png,pdf')]
    public $certUploadFile = null;

    public function openForm(): void
    {
        $this->reset(['type', 'start_date', 'end_date', 'reason', 'certificate']);
        $this->type = 'vacation';
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate([
            'type' => 'required|in:vacation,sick,unpaid,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'certificate' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);

        $days = LeaveRequest::calculateBusinessDays($this->start_date, $this->end_date);

        LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days' => $days,
            'reason' => $this->reason,
            'status' => 'pending',
            'certificate_path' => $this->certificate?->store('leave-certificates', 'public'),
        ]);

        $this->showForm = false;

        if ($this->type === 'sick' && $days >= LeaveRequest::CERTIFICATE_MIN_DAYS && ! $this->certificate) {
            $this->dispatch('toast', message: 'Leave submitted, but a medical certificate is still required for 3+ days of sick leave. Please upload it soon.', type: 'error');
        } else {
            $this->dispatch('toast', message: 'Leave request submitted for approval.', type: 'success');
        }
    }

    public function cancel(LeaveRequest $leaveRequest): void
    {
        if ($leaveRequest->user_id !== Auth::id() || $leaveRequest->status !== 'pending') {
            return;
        }

        $leaveRequest->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Leave request cancelled.', type: 'success');
    }

    public function openCertUpload(int $leaveRequestId): void
    {
        $this->certUploadTargetId = $leaveRequestId;
        $this->certUploadFile = null;
        $this->resetValidation();
        $this->showCertUploadForm = true;
    }

    public function submitCertUpload(): void
    {
        $this->validate(['certUploadFile' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf']);

        $leaveRequest = LeaveRequest::where('id', $this->certUploadTargetId)->where('user_id', Auth::id())->firstOrFail();

        $leaveRequest->update([
            'certificate_path' => $this->certUploadFile->store('leave-certificates', 'public'),
        ]);

        $this->showCertUploadForm = false;
        $this->reset(['certUploadTargetId', 'certUploadFile']);
        $this->dispatch('toast', message: 'Medical certificate uploaded.', type: 'success');
    }

    public function render()
    {
        $userId = Auth::id();
        $usedDays = LeaveRequest::where('user_id', $userId)->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days');

        $previewDays = ($this->start_date && $this->end_date)
            ? LeaveRequest::calculateBusinessDays($this->start_date, $this->end_date)
            : 0;

        $previewHolidays = ($this->start_date && $this->end_date)
            ? Holiday::whereBetween('date', [$this->start_date, $this->end_date])->orderBy('date')->get()
            : collect();

        return view('livewire.hr.leave-index', [
            'requests' => LeaveRequest::where('user_id', $userId)->latest()->paginate(10),
            'usedDays' => $usedDays,
            'remainingDays' => max(self::ANNUAL_ENTITLEMENT - $usedDays, 0),
            'previewDays' => $previewDays,
            'previewHolidays' => $previewHolidays,
            'showCertificateHint' => $this->type === 'sick' && $previewDays >= LeaveRequest::CERTIFICATE_MIN_DAYS && ! $this->certificate,
            'upcomingHolidays' => Holiday::where('date', '>=', now()->toDateString())->orderBy('date')->limit(6)->get(),
        ]);
    }
}
