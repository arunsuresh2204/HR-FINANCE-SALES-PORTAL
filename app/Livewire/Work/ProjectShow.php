<?php

namespace App\Livewire\Work;

use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectCredential;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectShow extends Component
{
    use WithFileUploads;

    public const STATUSES = [
        'backlog' => 'Backlog',
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'review' => 'Review',
        'done' => 'Done',
    ];

    public Project $project;

    public string $tab = 'board';

    // Reassign / Manage Developers
    public bool $showReassignForm = false;

    public ?int $reassign_to = null;

    public bool $showDeveloperForm = false;

    public array $developer_ids = [];

    // Task modal (create + non-pricing edit)
    public bool $showTaskModal = false;

    public ?int $editingTaskId = null;

    public string $task_title = '';

    public string $task_description = '';

    public ?int $task_category_id = null;

    public array $task_assignee_ids = [];

    public string $task_start_date = '';

    public string $task_end_date = '';

    public string $task_tags = '';

    public string $task_visibility = 'public';

    public string $task_urgency = 'medium';

    public string $task_pricing_mode = 'fixed';

    public string $task_amount = '';

    public string $task_hours = '';

    public string $task_rate = '';

    // Task detail
    public bool $showTaskDetail = false;

    public ?int $viewingTaskId = null;

    public string $approve_mode = 'fixed';

    public string $approve_amount = '';

    public string $approve_hours = '';

    public string $approve_rate = '';

    // Cancel
    public bool $showCancelForm = false;

    public string $cancel_reason = '';

    // Category
    public bool $showCategoryForm = false;

    public string $category_name = '';

    // Edit task amount (post-approval price adjustment, e.g. a client discount)
    public bool $showEditAmountForm = false;

    public ?int $editingAmountTaskId = null;

    public string $edit_amount_mode = 'fixed';

    public string $edit_amount_value = '';

    public string $edit_amount_hours = '';

    public string $edit_amount_rate = '';

    // Cost Estimate date filter
    public string $cost_filter_preset = 'all';

    public string $cost_filter_from = '';

    public string $cost_filter_to = '';

    // Requests from Sales
    public bool $showRequestDetail = false;

    public ?int $viewingRequestId = null;

    public string $reply_body = '';

    public array $reply_attachments = [];

    public ?int $convertingRequestId = null;

    // Notes
    public string $note_body = '';

    // Credentials
    public bool $showCredentialForm = false;

    public ?int $editingCredentialId = null;

    public string $credential_label = '';

    public string $credential_username = '';

    public string $credential_secret = '';

    public string $credential_notes = '';

    public array $revealedCredentialIds = [];

    // Files
    public array $project_files = [];

    public function mount(Project $project): void
    {
        $authUser = Auth::user();

        $canView = $authUser->isSuperAdmin()
            || $authUser->isManager()
            || $project->assigned_to === $authUser->id
            || $project->developers()->where('users.id', $authUser->id)->exists();

        abort_unless($canView, 403);

        $this->project = $project;
    }

    protected function canManage(): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin() || $authUser->isManager() || $this->project->assigned_to === $authUser->id;
    }

    /**
     * Only the Manager (or super_admin) responds to and converts Sales
     * requests — not a Team Lead, even one this project is assigned to.
     */
    protected function canManageRequests(): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin() || $authUser->isManager();
    }

    /**
     * Everyone with project access can view Credentials; only Manager/Team
     * Lead can add, edit, or remove entries.
     */
    protected function canManageCredentials(): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin() || $authUser->isManager() || $authUser->isTeamLead();
    }

    public function openReassignForm(): void
    {
        if (! $this->canManage()) {
            return;
        }

        $this->reassign_to = null;
        $this->resetValidation();
        $this->showReassignForm = true;
    }

    public function saveReassign(): void
    {
        if (! $this->canManage()) {
            return;
        }

        $this->validate(['reassign_to' => 'required|exists:users,id']);

        $assignee = User::findOrFail($this->reassign_to);

        if (! $assignee->isManager() && ! $assignee->isSuperAdmin() && ! $assignee->isTeamLead()) {
            $this->addError('reassign_to', 'Projects can only be assigned to a manager, team leader, or an owner.');

            return;
        }

        $this->project->update(['assigned_to' => $this->reassign_to]);

        if ($assignee->id !== Auth::id()) {
            Notification::send(
                $assignee,
                'project_reassigned',
                'A project was handed off to you',
                $this->project->name,
                route('work.project-show', $this->project)
            );
        }

        $this->showReassignForm = false;
        $this->dispatch('toast', message: 'Project reassigned.', type: 'success');
    }

    public function openDeveloperForm(): void
    {
        if (! $this->canManage()) {
            return;
        }

        $this->developer_ids = $this->project->developers()->pluck('users.id')->all();
        $this->resetValidation();
        $this->showDeveloperForm = true;
    }

    public function saveDevelopers(): void
    {
        if (! $this->canManage()) {
            return;
        }

        $existingIds = $this->project->developers()->pluck('users.id')->all();
        $this->project->developers()->sync($this->developer_ids);
        $newIds = collect($this->developer_ids)->diff($existingIds)->reject(fn ($id) => $id == Auth::id());

        if ($newIds->isNotEmpty()) {
            Notification::sendToMany(
                User::whereIn('id', $newIds)->get(),
                'project_developer_added',
                'Added to a project',
                $this->project->name,
                route('work.project-show', $this->project)
            );
        }

        $this->showDeveloperForm = false;
        $this->dispatch('toast', message: 'Developers assigned.', type: 'success');
    }

    protected function viewingTask(): ?Task
    {
        return $this->viewingTaskId ? $this->project->tasks()->find($this->viewingTaskId) : null;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function openTaskModal(?int $taskId = null): void
    {
        $authUser = Auth::user();
        $this->editingTaskId = $taskId;
        $this->convertingRequestId = null;
        $this->resetValidation();

        if ($taskId) {
            $task = $this->project->tasks()->findOrFail($taskId);

            if (! $task->canBeSeenBy($authUser)) {
                return;
            }

            $this->task_title = $task->title;
            $this->task_description = $task->description ?? '';
            $this->task_category_id = $task->category_id;
            $this->task_assignee_ids = $task->assignees->pluck('id')->all();
            $this->task_start_date = $task->start_date?->toDateString() ?? '';
            $this->task_end_date = $task->end_date?->toDateString() ?? '';
            $this->task_tags = $task->tags ? implode(', ', $task->tags) : '';
            $this->task_visibility = $task->visibility;
            $this->task_urgency = $task->urgency;
        } else {
            $this->task_title = '';
            $this->task_description = '';
            $this->task_category_id = null;
            $this->task_assignee_ids = [];
            $this->task_start_date = now()->toDateString();
            $this->task_end_date = now()->addDays(5)->toDateString();
            $this->task_tags = '';
            $this->task_visibility = 'public';
            $this->task_urgency = 'medium';
            $this->task_pricing_mode = 'fixed';
            $this->task_amount = '';
            $this->task_hours = '';
            $this->task_rate = '';
        }

        $this->showTaskModal = true;
    }

    /**
     * Notify whoever is newly assigned to a task (skips whoever was already
     * assigned, and never notifies the actor about their own action).
     */
    protected function notifyNewAssignees(Task $task, array $newIds, array $oldIds, User $actor): void
    {
        $newlyAdded = collect($newIds)->diff($oldIds)->reject(fn ($id) => $id == $actor->id);

        if ($newlyAdded->isEmpty()) {
            return;
        }

        Notification::sendToMany(
            User::whereIn('id', $newlyAdded)->get(),
            'task_assigned',
            'You were assigned a task',
            "{$task->title} — {$this->project->name}",
            route('work.project-show', $this->project)
        );
    }

    public function saveTask(): void
    {
        $authUser = Auth::user();
        $isManager = $authUser->isManager() || $authUser->isSuperAdmin();
        $editing = $this->editingTaskId ? $this->project->tasks()->find($this->editingTaskId) : null;

        if ($editing && ! ($editing->canBeSeenBy($authUser) && ($isManager || $authUser->id === $editing->created_by))) {
            return;
        }

        $this->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string|max:2000',
            'task_category_id' => 'nullable|exists:project_categories,id',
            'task_assignee_ids' => 'array',
            'task_assignee_ids.*' => 'integer|exists:users,id',
            'task_start_date' => 'nullable|date',
            'task_end_date' => 'nullable|date|after_or_equal:task_start_date',
            'task_visibility' => 'required|in:public,private',
            'task_urgency' => ['required', 'in:'.implode(',', array_keys(Task::URGENCIES))],
        ]);

        $allowedAssigneeIds = $this->project->assignableUsersFor($authUser)->pluck('id');

        if (collect($this->task_assignee_ids)->diff($allowedAssigneeIds)->isNotEmpty()) {
            $this->addError('task_assignee_ids', 'One or more selected people can\'t be assigned on this project.');

            return;
        }

        $tags = collect(explode(',', $this->task_tags))->map(fn ($t) => trim($t))->filter()->values()->all();

        $data = [
            'title' => $this->task_title,
            'description' => $this->task_description ?: null,
            'category_id' => $this->task_category_id ?: null,
            'start_date' => $this->task_start_date ?: null,
            'end_date' => $this->task_end_date ?: null,
            'tags' => $tags,
            'visibility' => $this->task_visibility,
            'urgency' => $this->task_urgency,
        ];

        if ($editing) {
            $previousAssigneeIds = $editing->assignees()->pluck('users.id')->all();
            $editing->update($data);
            $editing->assignees()->sync($this->task_assignee_ids);
            $this->notifyNewAssignees($editing, $this->task_assignee_ids, $previousAssigneeIds, $authUser);
            $this->showTaskModal = false;
            $this->dispatch('toast', message: 'Task updated.', type: 'success');

            return;
        }

        $pendingApproval = ! $isManager;
        $amount = null;
        $hours = null;
        $rate = null;
        $currency = null;

        if ($isManager) {
            if ($this->task_pricing_mode === 'hourly') {
                $hours = $this->task_hours !== '' ? (float) $this->task_hours : null;
                $rate = $this->task_rate !== '' ? (float) $this->task_rate : null;

                if (($hours !== null && $hours <= 0) || ($rate !== null && $rate <= 0)) {
                    $this->addError('task_hours', 'Hours and rate must be greater than zero.');

                    return;
                }

                $amount = ($hours && $rate) ? $hours * $rate : null;
            } else {
                $amount = $this->task_amount !== '' ? (float) $this->task_amount : null;

                if ($amount !== null && $amount <= 0) {
                    $this->addError('task_amount', 'Amount must be greater than zero.');

                    return;
                }
            }

            if ($amount !== null) {
                $currency = $this->project->currency;
            }
        }

        $task = $this->project->tasks()->create($data + [
            'status' => 'backlog',
            'amount' => $amount,
            'currency' => $currency,
            'hours' => $hours,
            'rate' => $rate,
            'created_by' => $authUser->id,
            'pending_approval' => $pendingApproval,
        ]);

        $task->assignees()->sync($this->task_assignee_ids);
        $this->notifyNewAssignees($task, $this->task_assignee_ids, [], $authUser);

        $this->showTaskModal = false;

        if ($this->convertingRequestId) {
            $request = $this->project->requests()->where('status', 'open')->find($this->convertingRequestId);

            if ($request) {
                $request->update([
                    'status' => 'converted',
                    'converted_task_id' => $task->id,
                    'converted_by' => $authUser->id,
                    'converted_at' => now(),
                ]);

                Notification::send(
                    $request->creator,
                    'project_request_converted',
                    'Request converted to a task',
                    "\"{$request->title}\" is now being worked on.",
                    route('sales.clients.show', $this->project->client_id)
                );
            }

            $this->convertingRequestId = null;
            $this->dispatch('toast', message: 'Request converted to a task.', type: 'success');

            return;
        }

        if ($pendingApproval) {
            Notification::sendToMany(
                User::role(['manager_engineering', 'super_admin'])->get(),
                'task_created',
                'New task requested',
                "{$task->title} on {$this->project->name}",
                route('work.project-show', $this->project)
            );
            $this->dispatch('toast', message: 'Task created — sent to your manager for approval and pricing.', type: 'success');
        } else {
            $this->dispatch('toast', message: 'Task created.', type: 'success');
        }
    }

    public function openTaskDetail(int $taskId): void
    {
        $task = $this->project->tasks()->find($taskId);

        if (! $task || ! $task->canBeSeenBy(Auth::user())) {
            return;
        }

        $this->viewingTaskId = $taskId;
        $this->approve_mode = 'fixed';
        $this->approve_amount = '';
        $this->approve_hours = '';
        $this->approve_rate = '';
        $this->showCancelForm = false;
        $this->cancel_reason = '';
        $this->resetValidation();
        $this->showTaskDetail = true;
    }

    public function setTaskStatus(int $taskId, string $status): void
    {
        $task = $this->project->tasks()->find($taskId);

        if (! $task || ! $task->canBeSeenBy(Auth::user()) || ! array_key_exists($status, self::STATUSES)) {
            return;
        }

        $task->update(['status' => $status]);
    }

    public function toggleTaskAssignee(int $taskId, int $userId): void
    {
        $authUser = Auth::user();
        $task = $this->project->tasks()->find($taskId);

        if (! $task || ! $task->canBeSeenBy($authUser)) {
            return;
        }

        if (! $this->project->assignableUsersFor($authUser)->contains('id', $userId)) {
            return;
        }

        if ($task->assignees()->where('users.id', $userId)->exists()) {
            $task->assignees()->detach($userId);
        } else {
            $task->assignees()->attach($userId);
            $this->notifyNewAssignees($task, [$userId], [], $authUser);
        }
    }

    public function setTaskUrgency(int $taskId, string $urgency): void
    {
        $task = $this->project->tasks()->find($taskId);

        if (! $task || ! $task->canBeSeenBy(Auth::user()) || ! array_key_exists($urgency, Task::URGENCIES)) {
            return;
        }

        $task->update(['urgency' => $urgency]);
    }

    public function setTaskVisibility(int $taskId, string $visibility): void
    {
        $task = $this->project->tasks()->find($taskId);

        if (! $task || ! $task->canBeSeenBy(Auth::user()) || ! in_array($visibility, ['public', 'private'], true)) {
            return;
        }

        $task->update(['visibility' => $visibility]);
    }

    public function approveTask(): void
    {
        $authUser = Auth::user();

        if (! $authUser->isManager() && ! $authUser->isSuperAdmin()) {
            return;
        }

        $task = $this->viewingTask();

        if (! $task || ! $task->pending_approval) {
            return;
        }

        $amount = null;
        $hours = null;
        $rate = null;

        if ($this->approve_mode === 'hourly') {
            $hours = $this->approve_hours !== '' ? (float) $this->approve_hours : null;
            $rate = $this->approve_rate !== '' ? (float) $this->approve_rate : null;

            if (! $hours || $hours <= 0 || ! $rate || $rate <= 0) {
                $this->addError('approve_amount', 'Enter hours and a rate greater than zero.');

                return;
            }

            $amount = $hours * $rate;
        } else {
            $amount = $this->approve_amount !== '' ? (float) $this->approve_amount : null;

            if (! $amount || $amount <= 0) {
                $this->addError('approve_amount', 'Enter an amount greater than zero before approving.');

                return;
            }
        }

        $task->update([
            'amount' => $amount,
            'currency' => $this->project->currency,
            'hours' => $hours,
            'rate' => $rate,
            'pending_approval' => false,
        ]);

        Notification::send(
            $task->creator,
            'task_approved',
            'Task approved',
            "{$task->title} was approved and is ready to start.",
            route('work.project-show', $this->project)
        );

        $this->showTaskDetail = false;
        $this->dispatch('toast', message: 'Task approved and priced.', type: 'success');
    }

    public function openCancelForm(): void
    {
        $task = $this->viewingTask();

        if (! $task || ! $task->canBeCancelledBy(Auth::user())) {
            return;
        }

        $this->cancel_reason = '';
        $this->showCancelForm = true;
    }

    public function confirmCancelTask(): void
    {
        $authUser = Auth::user();
        $task = $this->viewingTask();

        if (! $task || ! $task->canBeCancelledBy($authUser)) {
            return;
        }

        $this->validate(['cancel_reason' => 'required|string|max:500']);

        $task->update([
            'cancelled' => true,
            'cancel_reason' => $this->cancel_reason,
            'cancelled_by' => $authUser->id,
            'cancelled_at' => now(),
            'pending_approval' => false,
        ]);

        Notification::sendToMany(
            $task->assignees->isNotEmpty() ? $task->assignees : collect([$task->creator]),
            'task_cancelled',
            'Task cancelled',
            "\"{$task->title}\" was cancelled: {$this->cancel_reason}",
            route('work.project-show', $this->project)
        );

        $this->showTaskDetail = false;
        $this->showCancelForm = false;
        $this->dispatch('toast', message: 'Task cancelled.', type: 'success');
    }

    public function openCategoryForm(): void
    {
        if (! $this->canManage()) {
            return;
        }

        $this->resetValidation();
        $this->category_name = '';

        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $authUser = Auth::user();

        if (! $this->canManage()) {
            return;
        }

        $this->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $this->project->categories()->create([
            'name' => $this->category_name,
            'created_by' => $authUser->id,
        ]);

        $this->showCategoryForm = false;
        $this->dispatch('toast', message: 'Category added.', type: 'success');
    }

    /**
     * Manager flags a Done, priced task as ready for Sales to pick up and
     * bill — purely a signal to Sales, no billing request is created here.
     * Sales chooses which ready tasks to bundle into an actual billing
     * request from their own Ready to Bill workspace. Can be un-flagged
     * only if Sales hasn't picked it up yet.
     */
    public function toggleReadyToBill(int $taskId): void
    {
        $authUser = Auth::user();

        if (! $authUser->isManager() && ! $authUser->isSuperAdmin()) {
            return;
        }

        $task = $this->project->tasks()->find($taskId);

        if (! $task || $task->cancelled) {
            return;
        }

        if ($task->ready_to_bill) {
            if ($task->activeBillingRequest() !== null) {
                $this->dispatch('toast', message: 'Sales has already picked this up — can\'t un-flag it now.', type: 'error');

                return;
            }

            $task->update(['ready_to_bill' => false]);
            $this->dispatch('toast', message: 'No longer marked ready to bill.', type: 'success');

            return;
        }

        if (! $task->isPriced() || $task->status !== 'done') {
            $this->dispatch('toast', message: 'Only a priced, Done task can be marked ready to bill.', type: 'error');

            return;
        }

        $task->update(['ready_to_bill' => true]);

        Notification::sendToMany(
            User::role(['sales_exec', 'super_admin'])->get(),
            'task_ready_to_bill',
            'Task ready to bill',
            "{$task->title} — {$this->project->name}",
            route('sales.billing')
        );

        $this->dispatch('toast', message: 'Marked ready to bill — Sales has been notified.', type: 'success');
    }

    /**
     * Manager-only price correction for an already-priced task — e.g. a
     * client discount applied before billing. Locked once a billing request
     * for the task has actually been invoiced.
     */
    public function openEditAmountForm(int $taskId): void
    {
        $authUser = Auth::user();

        if (! $authUser->isManager() && ! $authUser->isSuperAdmin()) {
            return;
        }

        $task = $this->project->tasks()->find($taskId);

        if (! $task || ! $task->isPriced() || $task->billingRequests()->where('status', 'invoiced')->exists()) {
            return;
        }

        $this->editingAmountTaskId = $taskId;
        $this->edit_amount_mode = ($task->hours !== null && $task->rate !== null) ? 'hourly' : 'fixed';
        $this->edit_amount_value = $task->amount !== null ? (string) $task->amount : '';
        $this->edit_amount_hours = $task->hours !== null ? (string) $task->hours : '';
        $this->edit_amount_rate = $task->rate !== null ? (string) $task->rate : '';
        $this->resetValidation();
        $this->showEditAmountForm = true;
    }

    public function saveTaskAmount(): void
    {
        $authUser = Auth::user();

        if (! $authUser->isManager() && ! $authUser->isSuperAdmin()) {
            return;
        }

        $task = $this->editingAmountTaskId ? $this->project->tasks()->find($this->editingAmountTaskId) : null;

        if (! $task || $task->billingRequests()->where('status', 'invoiced')->exists()) {
            return;
        }

        $pendingRequests = $task->billingRequests()->where('status', 'pending')->get();

        $amount = null;
        $hours = null;
        $rate = null;

        if ($this->edit_amount_mode === 'hourly') {
            $hours = $this->edit_amount_hours !== '' ? (float) $this->edit_amount_hours : null;
            $rate = $this->edit_amount_rate !== '' ? (float) $this->edit_amount_rate : null;

            if (! $hours || $hours <= 0 || ! $rate || $rate <= 0) {
                $this->addError('edit_amount_value', 'Enter hours and a rate greater than zero.');

                return;
            }

            $amount = $hours * $rate;
        } else {
            $amount = $this->edit_amount_value !== '' ? (float) $this->edit_amount_value : null;

            if (! $amount || $amount <= 0) {
                $this->addError('edit_amount_value', 'Enter an amount greater than zero.');

                return;
            }
        }

        $task->update([
            'amount' => $amount,
            'currency' => $this->project->currency,
            'hours' => $hours,
            'rate' => $rate,
        ]);

        // Recompute each pending billing request's total from its (possibly several) billed tasks.
        foreach ($pendingRequests as $pendingRequest) {
            $pendingRequest->update([
                'amount' => $pendingRequest->billedTasks->sum(fn (Task $t) => $t->fresh()->effectiveAmount()),
            ]);
        }

        $this->showEditAmountForm = false;
        $this->dispatch('toast', message: 'Task amount updated.', type: 'success');
    }

    public function openRequestDetail(int $requestId): void
    {
        if (! $this->canManageRequests()) {
            return;
        }

        $request = $this->project->requests()->find($requestId);

        if (! $request) {
            return;
        }

        $this->viewingRequestId = $requestId;
        $this->reply_body = '';
        $this->reply_attachments = [];
        $this->resetValidation();
        $this->showRequestDetail = true;
    }

    public function submitReply(): void
    {
        if (! $this->canManageRequests()) {
            return;
        }

        $request = $this->project->requests()->find($this->viewingRequestId);

        if (! $request) {
            return;
        }

        $this->validate([
            'reply_body' => 'required|string|max:2000',
            'reply_attachments' => 'array|max:5',
            'reply_attachments.*' => 'file|max:10240',
        ]);

        $comment = $request->comments()->create([
            'user_id' => Auth::id(),
            'body' => $this->reply_body,
        ]);

        foreach ($this->reply_attachments as $file) {
            $comment->attachments()->create([
                'path' => $file->store('project-requests', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        Notification::send(
            $request->creator,
            'project_request_reply',
            'New reply on your request',
            "{$request->title} — {$this->project->name}",
            route('sales.clients.show', $this->project->client_id)
        );

        $this->reply_body = '';
        $this->reply_attachments = [];
        $this->dispatch('toast', message: 'Reply sent.', type: 'success');
    }

    public function convertRequest(int $requestId): void
    {
        if (! $this->canManageRequests()) {
            return;
        }

        $request = $this->project->requests()->where('status', 'open')->find($requestId);

        if (! $request) {
            return;
        }

        $this->openTaskModal();
        $this->task_title = $request->title;
        $this->task_description = $request->description ?? '';
        $this->convertingRequestId = $requestId;
        $this->showRequestDetail = false;
    }

    public function saveNote(): void
    {
        $this->validate(['note_body' => 'required|string|max:2000']);

        $this->project->notes()->create([
            'user_id' => Auth::id(),
            'body' => $this->note_body,
        ]);

        $this->note_body = '';
        $this->dispatch('toast', message: 'Note added.', type: 'success');
    }

    public function deleteNote(int $noteId): void
    {
        $authUser = Auth::user();
        $note = $this->project->notes()->find($noteId);

        if (! $note || ! ($authUser->isManager() || $authUser->isSuperAdmin() || $authUser->isTeamLead() || $note->user_id === $authUser->id)) {
            return;
        }

        $note->delete();
        $this->dispatch('toast', message: 'Note removed.', type: 'success');
    }

    public function openCredentialForm(): void
    {
        if (! $this->canManageCredentials()) {
            return;
        }

        $this->editingCredentialId = null;
        $this->credential_label = '';
        $this->credential_username = '';
        $this->credential_secret = '';
        $this->credential_notes = '';
        $this->resetValidation();
        $this->showCredentialForm = true;
    }

    public function editCredential(int $credentialId): void
    {
        if (! $this->canManageCredentials()) {
            return;
        }

        $credential = $this->project->credentials()->findOrFail($credentialId);

        $this->editingCredentialId = $credential->id;
        $this->credential_label = $credential->label;
        $this->credential_username = $credential->username ?? '';
        $this->credential_secret = $credential->secret;
        $this->credential_notes = $credential->notes ?? '';
        $this->resetValidation();
        $this->showCredentialForm = true;
    }

    public function saveCredential(): void
    {
        if (! $this->canManageCredentials()) {
            return;
        }

        $this->validate([
            'credential_label' => 'required|string|max:255',
            'credential_username' => 'nullable|string|max:255',
            'credential_secret' => 'required|string|max:2000',
            'credential_notes' => 'nullable|string|max:1000',
        ]);

        $data = [
            'label' => $this->credential_label,
            'username' => $this->credential_username ?: null,
            'secret' => $this->credential_secret,
            'notes' => $this->credential_notes ?: null,
        ];

        if ($this->editingCredentialId) {
            $this->project->credentials()->findOrFail($this->editingCredentialId)->update($data);
        } else {
            $this->project->credentials()->create($data + ['created_by' => Auth::id()]);
        }

        $this->showCredentialForm = false;
        $this->dispatch('toast', message: 'Credential saved.', type: 'success');
    }

    public function deleteCredential(int $credentialId): void
    {
        if (! $this->canManageCredentials()) {
            return;
        }

        $this->project->credentials()->where('id', $credentialId)->delete();
        $this->dispatch('toast', message: 'Credential removed.', type: 'success');
    }

    public function toggleRevealCredential(int $credentialId): void
    {
        if (in_array($credentialId, $this->revealedCredentialIds, true)) {
            $this->revealedCredentialIds = array_values(array_diff($this->revealedCredentialIds, [$credentialId]));
        } else {
            $this->revealedCredentialIds[] = $credentialId;
        }
    }

    public function uploadFiles(): void
    {
        $this->validate([
            'project_files' => 'array|max:5',
            'project_files.*' => 'file|max:10240',
        ]);

        foreach ($this->project_files as $file) {
            $this->project->attachments()->create([
                'path' => $file->store('project-files', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        $this->project_files = [];
        $this->dispatch('toast', message: 'File(s) uploaded.', type: 'success');
    }

    public function deleteFile(int $attachmentId): void
    {
        $authUser = Auth::user();
        $attachment = $this->project->attachments()->find($attachmentId);

        if (! $attachment || ! ($authUser->isManager() || $authUser->isSuperAdmin() || $authUser->isTeamLead() || $attachment->uploaded_by === $authUser->id)) {
            return;
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        $this->dispatch('toast', message: 'File removed.', type: 'success');
    }

    public function applyCostPreset(string $preset): void
    {
        $this->cost_filter_preset = $preset;

        match ($preset) {
            'month' => [$this->cost_filter_from, $this->cost_filter_to] = [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'last30' => [$this->cost_filter_from, $this->cost_filter_to] = [now()->subDays(30)->toDateString(), now()->toDateString()],
            'all' => [$this->cost_filter_from, $this->cost_filter_to] = ['', ''],
            default => null,
        };
    }

    public function setCostDateFilter(): void
    {
        $this->cost_filter_preset = 'custom';
    }

    protected function taskInCostRange(Task $task): bool
    {
        if (! $this->cost_filter_from && ! $this->cost_filter_to) {
            return true;
        }

        if (! $task->end_date) {
            return false;
        }

        if ($this->cost_filter_from && $task->end_date->lt(\Illuminate\Support\Carbon::parse($this->cost_filter_from))) {
            return false;
        }

        if ($this->cost_filter_to && $task->end_date->gt(\Illuminate\Support\Carbon::parse($this->cost_filter_to))) {
            return false;
        }

        return true;
    }

    public function render()
    {
        $authUser = Auth::user();
        $isManager = $authUser->isManager() || $authUser->isSuperAdmin();

        $tasks = $this->project->tasks()
            ->with(['category', 'assignees', 'creator', 'canceller', 'billingRequests'])
            ->get()
            ->filter(fn (Task $task) => $task->canBeSeenBy($authUser))
            ->values();

        $board = collect(self::STATUSES)->keys()->mapWithKeys(
            fn ($status) => [$status => $tasks->where('status', $status)->values()]
        );

        $pricedTasks = $tasks->filter(fn (Task $t) => $t->isPriced() && ! $t->cancelled);
        $shownPricedTasks = $pricedTasks->filter(fn (Task $t) => $this->taskInCostRange($t));
        $costFilterActive = (bool) ($this->cost_filter_from || $this->cost_filter_to);

        $canManageRequests = $this->canManageRequests();
        $requests = $canManageRequests
            ? $this->project->requests()->with(['creator', 'comments.author', 'comments.attachments', 'attachments', 'convertedTask'])->latest()->get()
            : collect();

        $categories = $this->project->categories()->get();
        $categorySubtotals = [];

        foreach ($categories as $category) {
            $categoryTasks = $shownPricedTasks->where('category_id', $category->id)->values();
            $category->setRelation('tasks', $categoryTasks);

            $subtotals = [];
            foreach ($categoryTasks as $t) {
                $subtotals[$t->currency] = ($subtotals[$t->currency] ?? 0) + $t->effectiveAmount();
            }
            $categorySubtotals[$category->id] = $subtotals;
        }

        return view('livewire.work.project-show', [
            'tasks' => $tasks,
            'board' => $board,
            'statuses' => self::STATUSES,
            'canManage' => $this->canManage(),
            'isManager' => $isManager,
            'assignableUsers' => $this->project->assignableUsersFor($authUser),
            'categories' => $categories,
            'categorySubtotals' => $categorySubtotals,
            'pricedCount' => $pricedTasks->count(),
            'shownPricedCount' => $shownPricedTasks->count(),
            'costFilterActive' => $costFilterActive,
            'pendingCount' => $isManager ? $tasks->where('pending_approval', true)->count() : 0,
            'viewingTask' => $this->viewingTask(),
            'canManageRequests' => $canManageRequests,
            'requests' => $requests,
            'openRequestCount' => $requests->where('status', 'open')->count(),
            'viewingRequest' => $this->viewingRequestId ? $requests->firstWhere('id', $this->viewingRequestId) : null,
            'notes' => $this->project->notes()->with('author')->get(),
            'canManageCredentials' => $this->canManageCredentials(),
            'credentials' => $this->project->credentials()->with('creator')->get(),
            'files' => $this->project->attachments()->with('uploader')->latest()->get(),
            'developersList' => User::role('programmer')->orderBy('name')->get(),
            'reassignableUsers' => User::role(['manager_engineering', 'team_lead_it', 'super_admin'])
                ->where('id', '!=', $authUser->id)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
