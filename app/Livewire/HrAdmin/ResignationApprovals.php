<?php

namespace App\Livewire\HrAdmin;

use App\Mail\ResignationAccepted;
use App\Models\OffboardingTask;
use App\Models\Resignation;
use App\Support\Html;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ResignationApprovals extends Component
{
    use WithFileUploads, WithPagination;

    public const DEFAULT_TASKS = [
        'Return company laptop / hardware',
        'Revoke email & system access',
        'Return ID card',
        'Knowledge transfer completed',
        'Final settlement processed',
    ];

    // Experience Certificate / Relieving Letter editor modal
    public bool $showDocumentForm = false;

    public ?int $editingResignationId = null;

    public string $documentType = '';

    public string $document_content = '';

    // Bumped every time the document modal opens — forces the wire:ignore'd
    // rich-text editor to remount so it re-seeds with the right content.
    public int $documentModalInstance = 0;

    // Form 16 / UAN-PF upload modal — these are real tax/government filings,
    // not something this app can generate, so HR just attaches the file.
    public bool $showFileUploadForm = false;

    public ?int $uploadingResignationId = null;

    public string $fileUploadType = '';

    public $exit_document_file = null;

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

        $message = 'Resignation accepted. Offboarding checklist created.';

        if ($resignation->user->personal_email) {
            try {
                Mail::to($resignation->user->personal_email)->send(new ResignationAccepted($resignation));
            } catch (\Throwable $e) {
                report($e);
                $message .= ' The confirmation email to their personal address could not be sent.';
            }
        }

        $this->dispatch('toast', message: $message, type: 'success');
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

    public function openDocumentForm(int $resignationId, string $type): void
    {
        if (! in_array($type, ['experience_certificate', 'relieving_letter'], true)) {
            return;
        }

        $resignation = Resignation::with('user')->findOrFail($resignationId);

        $this->editingResignationId = $resignationId;
        $this->documentType = $type;
        $this->documentModalInstance++;

        $existing = $resignation->{"{$type}_content"};

        $this->document_content = $existing ?: ($type === 'experience_certificate'
            ? $resignation->draftExperienceCertificateContent()
            : $resignation->draftRelievingLetterContent());

        $this->resetValidation();
        $this->showDocumentForm = true;
    }

    public function publishDocument(): void
    {
        $this->validate([
            'document_content' => 'nullable|string|max:20000',
        ]);

        $resignation = Resignation::findOrFail($this->editingResignationId);

        $resignation->update([
            "{$this->documentType}_content" => Html::sanitize($this->document_content),
            "{$this->documentType}_published_at" => now(),
        ]);

        $this->showDocumentForm = false;
        $this->dispatch('toast', message: 'Document published to the employee.', type: 'success');
    }

    public function openFileUploadForm(int $resignationId, string $type): void
    {
        if (! in_array($type, ['form16', 'uan_pf'], true)) {
            return;
        }

        $this->uploadingResignationId = $resignationId;
        $this->fileUploadType = $type;
        $this->exit_document_file = null;
        $this->resetValidation();
        $this->showFileUploadForm = true;
    }

    public function saveFileUpload(): void
    {
        $this->validate([
            'exit_document_file' => 'required|file|max:10240',
        ]);

        $resignation = Resignation::findOrFail($this->uploadingResignationId);
        $column = "{$this->fileUploadType}_path";

        if ($resignation->{$column}) {
            Storage::disk('public')->delete($resignation->{$column});
        }

        $resignation->update([
            $column => $this->exit_document_file->store('exit-documents', 'public'),
        ]);

        $this->showFileUploadForm = false;
        $this->exit_document_file = null;
        $this->dispatch('toast', message: 'File uploaded.', type: 'success');
    }

    public function deleteExitFile(int $resignationId, string $type): void
    {
        if (! in_array($type, ['form16', 'uan_pf'], true)) {
            return;
        }

        $resignation = Resignation::findOrFail($resignationId);
        $column = "{$type}_path";

        if ($resignation->{$column}) {
            Storage::disk('public')->delete($resignation->{$column});
            $resignation->update([$column => null]);
            $this->dispatch('toast', message: 'File removed.', type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.hr-admin.resignation-approvals', [
            'resignations' => Resignation::with(['user', 'offboardingTasks'])->latest()->paginate(5),
        ]);
    }
}
