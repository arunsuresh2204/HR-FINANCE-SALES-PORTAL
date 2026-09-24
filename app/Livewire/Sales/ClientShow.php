<?php

namespace App\Livewire\Sales;

use App\Models\Attachment;
use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\InvoiceEditRequest;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ClientShow extends Component
{
    use WithFileUploads, WithPagination;

    public Client $client;

    public string $projectSearch = '';

    public bool $showBusinessForm = false;

    #[Validate('required|string|max:255')]
    public string $business_name = '';

    #[Validate('nullable|string|max:255')]
    public string $business_type = '';

    #[Validate('nullable|string|max:1000')]
    public string $business_address = '';

    #[Validate('nullable|string|max:255')]
    public string $owner_name = '';

    #[Validate('nullable|string|max:255')]
    public string $owner_designation = '';

    #[Validate('nullable|string|max:255')]
    public string $owner_contact = '';

    #[Validate('nullable|string|max:30')]
    public string $owner_phone = '';

    #[Validate('nullable|string|max:50')]
    public string $tax_id = '';

    public bool $showAgreementForm = false;

    #[Validate('nullable|file|max:10240')]
    public $agreement_file = null;

    #[Validate('nullable|date')]
    public string $agreement_effective_date = '';

    #[Validate('nullable|string|max:1000')]
    public string $agreement_scope_summary = '';

    public bool $showProjectForm = false;

    #[Validate('required|string|max:255')]
    public string $project_name = '';

    #[Validate('nullable|string|max:1000')]
    public string $project_description = '';

    public string $project_currency = 'INR';

    public bool $projectCurrencyLocked = false;

    public array $project_requirement_files = [];

    public ?int $assigned_to = null;

    public ?int $editingProjectId = null;

    public bool $showDeveloperForm = false;

    public bool $showRequestForm = false;

    public ?int $requestingProjectId = null;

    #[Validate('required|string|max:255')]
    public string $request_title = '';

    #[Validate('nullable|string|max:2000')]
    public string $request_description = '';

    public array $request_attachments = [];

    public bool $showRequestDetail = false;

    public ?int $viewingRequestId = null;

    #[Validate('required|string|max:2000')]
    public string $reply_body = '';

    public array $reply_attachments = [];

    public ?int $managingProjectId = null;

    public array $developer_ids = [];

    public bool $showBillingForm = false;

    public ?int $editingBillingRequestId = null;

    #[Validate('nullable|exists:projects,id')]
    public ?int $project_id = null;

    #[Validate('required|string|size:3')]
    public string $currency = 'INR';

    #[Validate('required|in:milestone,hourly')]
    public string $billing_type = 'milestone';

    #[Validate('required_if:billing_type,milestone|nullable|numeric|min:0.01')]
    public string $amount = '';

    #[Validate('required_if:billing_type,milestone|nullable|string|max:255')]
    public string $milestone_description = '';

    public array $tasks = [];

    public bool $showEditInvoiceForm = false;

    public ?int $editingInvoiceId = null;

    public array $edit_line_items = [];

    public string $edit_due_date = '';

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->agreement_effective_date = $client->agreement_effective_date?->toDateString() ?? '';
        $this->agreement_scope_summary = $client->agreement_scope_summary ?? '';
    }

    public function updatingProjectSearch(): void
    {
        $this->resetPage('projectsPage');
    }

    public function openBusinessForm(): void
    {
        $this->business_name = $this->client->business_name;
        $this->business_type = $this->client->business_type ?? '';
        $this->business_address = $this->client->business_address ?? '';
        $this->owner_name = $this->client->owner_name ?? '';
        $this->owner_designation = $this->client->owner_designation ?? '';
        $this->owner_contact = $this->client->owner_contact ?? '';
        $this->owner_phone = $this->client->owner_phone ?? '';
        $this->tax_id = $this->client->tax_id ?? '';
        $this->resetValidation();
        $this->showBusinessForm = true;
    }

    public function saveBusinessDetails(): void
    {
        $this->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:1000',
            'owner_name' => 'nullable|string|max:255',
            'owner_designation' => 'nullable|string|max:255',
            'owner_contact' => 'nullable|string|max:255',
            'owner_phone' => 'nullable|string|max:30',
            'tax_id' => 'nullable|string|max:50',
        ]);

        $this->client->update([
            'business_name' => $this->business_name,
            'business_type' => $this->business_type ?: null,
            'business_address' => $this->business_address ?: null,
            'owner_name' => $this->owner_name ?: null,
            'owner_designation' => $this->owner_designation ?: null,
            'owner_contact' => $this->owner_contact ?: null,
            'owner_phone' => $this->owner_phone ?: null,
            'tax_id' => $this->tax_id ?: null,
        ]);

        $this->showBusinessForm = false;
        $this->dispatch('toast', message: 'Business details saved.', type: 'success');
    }

    public function saveAgreement(): void
    {
        $this->validate();

        $data = [
            'agreement_effective_date' => $this->agreement_effective_date ?: null,
            'agreement_scope_summary' => $this->agreement_scope_summary,
        ];

        if ($this->agreement_file) {
            $data['agreement_file'] = $this->agreement_file->store('agreements', 'public');
        }

        $this->client->update($data);
        $this->showAgreementForm = false;
        $this->dispatch('toast', message: 'Agreement details saved.', type: 'success');
    }

    public function openProjectForm(): void
    {
        $this->editingProjectId = null;
        $this->reset(['project_name', 'project_description', 'project_requirement_files']);
        $this->project_currency = 'INR';
        $this->projectCurrencyLocked = false;
        $this->assigned_to = Auth::user()->isManager() || Auth::user()->isSuperAdmin() ? Auth::id() : null;
        $this->resetValidation();
        $this->showProjectForm = true;
    }

    public function editProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        if (! $this->canManageClientFinancials()) {
            return;
        }

        $this->editingProjectId = $project->id;
        $this->project_name = $project->name;
        $this->project_description = $project->description ?? '';
        $this->project_requirement_files = [];
        $this->project_currency = $project->currency;
        $this->projectCurrencyLocked = $project->tasks()->whereNotNull('amount')->exists();
        $this->assigned_to = $project->assigned_to;
        $this->resetValidation();
        $this->showProjectForm = true;
    }

    public function deleteProject(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        if (! $this->canManageClientFinancials()) {
            return;
        }

        $project->delete();
        $this->dispatch('toast', message: 'Project deleted.', type: 'success');
    }

    public function submitProject(): void
    {
        $authUser = Auth::user();
        $canManage = $authUser->isManager() || $authUser->isSuperAdmin();

        $editing = $this->editingProjectId ? Project::findOrFail($this->editingProjectId) : null;

        if ($editing && ! $this->canManageClientFinancials()) {
            return;
        }

        $currencyEditable = ! $editing || ! $editing->tasks()->whereNotNull('amount')->exists();

        $this->validate([
            'project_name' => 'required|string|max:255',
            'project_description' => 'nullable|string|max:1000',
            'project_requirement_files' => 'array|max:5',
            'project_requirement_files.*' => 'file|max:10240',
            'assigned_to' => $canManage ? 'nullable|exists:users,id' : 'required|exists:users,id',
            'project_currency' => $currencyEditable ? ['required', 'in:'.implode(',', \App\Support\Currency::codes())] : 'nullable',
        ]);

        if ($this->assigned_to) {
            $assignee = User::findOrFail($this->assigned_to);

            if ($canManage) {
                if (! $assignee->isManager() && ! $assignee->isSuperAdmin() && ! $assignee->isTeamLead()) {
                    $this->addError('assigned_to', 'Projects can only be assigned to a manager, team leader, or an owner.');

                    return;
                }
            } elseif (! $assignee->hasRole('manager_engineering')) {
                $this->addError('assigned_to', 'Projects can only be assigned to the Engineering Manager.');

                return;
            }
        }

        $data = [
            'assigned_to' => $this->assigned_to ?: ($canManage ? Auth::id() : null),
            'name' => $this->project_name,
            'description' => $this->project_description,
        ];

        if ($currencyEditable) {
            $data['currency'] = $this->project_currency;
        }

        if ($editing) {
            $editing->update($data);
            $project = $editing;
        } else {
            $project = Project::create($data + [
                'client_id' => $this->client->id,
                'created_by' => Auth::id(),
                'status' => 'active',
            ]);
        }

        foreach ($this->project_requirement_files as $file) {
            $project->attachments()->create([
                'path' => $file->store('project-requirements', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        $this->editingProjectId = null;
        $this->showProjectForm = false;
        $this->dispatch('toast', message: $editing ? 'Project updated.' : 'Project created.', type: 'success');
    }

    public function deleteProjectAttachment(int $attachmentId): void
    {
        $attachment = Attachment::where('attachable_type', Project::class)->findOrFail($attachmentId);

        if (! $this->canManageClientFinancials()) {
            return;
        }

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        $this->dispatch('toast', message: 'Attachment removed.', type: 'success');
    }

    protected function canManageProject(Project $project): bool
    {
        $authUser = Auth::user();

        return $authUser->isManager() || $authUser->isSuperAdmin() || $project->assigned_to === $authUser->id;
    }

    public function openDeveloperForm(int $projectId): void
    {
        $project = Project::findOrFail($projectId);

        if (! $this->canManageProject($project)) {
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

        if (! $this->canManageProject($project)) {
            return;
        }

        $project->developers()->sync($this->developer_ids);

        $this->showDeveloperForm = false;
        $this->dispatch('toast', message: 'Developers assigned.', type: 'success');
    }

    public function openRequestForm(int $projectId): void
    {
        if (! $this->canManageClientFinancials()) {
            return;
        }

        $this->requestingProjectId = $projectId;
        $this->request_title = '';
        $this->request_description = '';
        $this->request_attachments = [];
        $this->resetValidation();
        $this->showRequestForm = true;
    }

    public function submitRequest(): void
    {
        if (! $this->canManageClientFinancials()) {
            return;
        }

        $project = Project::findOrFail($this->requestingProjectId);

        $this->validate([
            'request_title' => 'required|string|max:255',
            'request_description' => 'nullable|string|max:2000',
            'request_attachments' => 'array|max:5',
            'request_attachments.*' => 'file|max:10240',
        ]);

        $request = $project->requests()->create([
            'created_by' => Auth::id(),
            'title' => $this->request_title,
            'description' => $this->request_description ?: null,
            'status' => 'open',
        ]);

        foreach ($this->request_attachments as $file) {
            $request->attachments()->create([
                'path' => $file->store('project-requests', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        if ($project->assigned_to) {
            Notification::send(
                $project->assignedTo,
                'project_request_created',
                'New request from Sales',
                "{$this->request_title} — {$project->name}",
                route('work.project-show', $project)
            );
        }

        $this->showRequestForm = false;
        $this->dispatch('toast', message: 'Request sent.', type: 'success');
    }

    public function openRequestDetail(int $requestId): void
    {
        $request = ProjectRequest::findOrFail($requestId);

        if (! $this->canManageClientFinancials()) {
            return;
        }

        $this->viewingRequestId = $request->id;
        $this->reply_body = '';
        $this->reply_attachments = [];
        $this->resetValidation();
        $this->showRequestDetail = true;
    }

    public function submitReply(): void
    {
        if (! $this->canManageClientFinancials()) {
            return;
        }

        $request = ProjectRequest::with('project')->findOrFail($this->viewingRequestId);

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

        if ($request->project->assigned_to) {
            Notification::send(
                $request->project->assignedTo,
                'project_request_reply',
                'New reply on request',
                "{$request->title} — {$request->project->name}",
                route('work.project-show', $request->project)
            );
        }

        $this->reply_body = '';
        $this->reply_attachments = [];
        $this->dispatch('toast', message: 'Reply sent.', type: 'success');
    }

    public function openBillingForm(): void
    {
        $this->editingBillingRequestId = null;
        $this->reset(['project_id', 'amount', 'milestone_description']);
        $this->currency = 'INR';
        $this->billing_type = 'milestone';
        $this->tasks = [['description' => '', 'hours' => '', 'rate' => '']];
        $this->resetValidation();
        $this->showBillingForm = true;
    }

    protected function canManageClientFinancials(): bool
    {
        $authUser = Auth::user();

        return $authUser->isSuperAdmin()
            || $authUser->isManager()
            || $authUser->id === $this->client->sales_person_id
            || ($authUser->teamVisibilityFor('access_sales_clients') && $authUser->allDescendants()->contains('id', $this->client->sales_person_id));
    }

    protected function canEditBillingRequest(BillingRequest $billingRequest): bool
    {
        return $billingRequest->status === 'pending'
            && (Auth::id() === $billingRequest->created_by || $this->canManageClientFinancials());
    }

    public function deleteBillingRequest(int $billingRequestId): void
    {
        $billingRequest = BillingRequest::findOrFail($billingRequestId);

        if (! $this->canEditBillingRequest($billingRequest)) {
            return;
        }

        $billingRequest->delete();
        $this->dispatch('toast', message: 'Billing request deleted.', type: 'success');
    }

    public function editBillingRequest(int $billingRequestId): void
    {
        $billingRequest = BillingRequest::with('tasks')->findOrFail($billingRequestId);

        if (! $this->canEditBillingRequest($billingRequest)) {
            return;
        }

        $this->editingBillingRequestId = $billingRequest->id;
        $this->project_id = $billingRequest->project_id;
        $this->currency = $billingRequest->currency;
        $this->billing_type = $billingRequest->billing_type;

        if ($billingRequest->isHourly()) {
            $this->tasks = $billingRequest->tasks->map(fn ($task) => [
                'description' => $task->task_description,
                'hours' => (string) $task->hours,
                'rate' => (string) $task->rate,
            ])->all();
            $this->amount = '';
            $this->milestone_description = '';
        } else {
            $this->amount = (string) $billingRequest->amount;
            $this->milestone_description = $billingRequest->milestone_description ?? '';
            $this->tasks = [['description' => '', 'hours' => '', 'rate' => '']];
        }

        $this->resetValidation();
        $this->showBillingForm = true;
    }

    public function addTaskRow(): void
    {
        $lastRate = end($this->tasks)['rate'] ?? '';
        $this->tasks[] = ['description' => '', 'hours' => '', 'rate' => $lastRate];
    }

    public function removeTaskRow(int $index): void
    {
        if (count($this->tasks) > 1) {
            unset($this->tasks[$index]);
            $this->tasks = array_values($this->tasks);
        }
    }

    public function submitBillingRequest(): void
    {
        $editing = $this->editingBillingRequestId
            ? BillingRequest::findOrFail($this->editingBillingRequestId)
            : null;

        if ($editing && ! $this->canEditBillingRequest($editing)) {
            return;
        }

        if ($this->billing_type === 'milestone') {
            $this->validate([
                'project_id' => 'nullable|exists:projects,id',
                'currency' => ['required', 'in:'.implode(',', \App\Support\Currency::codes())],
                'amount' => 'required|numeric|min:0.01',
                'milestone_description' => 'required|string|max:255',
            ]);

            $data = [
                'client_id' => $this->client->id,
                'project_id' => $this->project_id,
                'currency' => $this->currency,
                'billing_type' => 'milestone',
                'amount' => $this->amount,
                'milestone_description' => $this->milestone_description,
                'status' => 'pending',
            ];

            if ($editing) {
                $editing->tasks()->delete();
                $editing->update($data);
            } else {
                BillingRequest::create($data + ['created_by' => Auth::id()]);
            }
        } else {
            $this->validate([
                'project_id' => 'nullable|exists:projects,id',
                'currency' => ['required', 'in:'.implode(',', \App\Support\Currency::codes())],
                'tasks' => 'required|array|min:1',
                'tasks.*.description' => 'required|string|max:255',
                'tasks.*.hours' => 'required|numeric|min:0.25|max:1000',
                'tasks.*.rate' => 'required|numeric|min:0.01',
            ]);

            $amount = array_sum(array_map(
                fn ($task) => (float) $task['hours'] * (float) $task['rate'],
                $this->tasks
            ));

            DB::transaction(function () use ($amount, $editing) {
                $data = [
                    'client_id' => $this->client->id,
                    'project_id' => $this->project_id,
                    'currency' => $this->currency,
                    'billing_type' => 'hourly',
                    'amount' => $amount,
                    'milestone_description' => null,
                    'status' => 'pending',
                ];

                if ($editing) {
                    $editing->tasks()->delete();
                    $editing->update($data);
                    $billingRequest = $editing;
                } else {
                    $billingRequest = BillingRequest::create($data + ['created_by' => Auth::id()]);
                }

                foreach ($this->tasks as $task) {
                    $billingRequest->tasks()->create([
                        'task_description' => $task['description'],
                        'hours' => $task['hours'],
                        'rate' => $task['rate'],
                    ]);
                }
            });
        }

        $this->editingBillingRequestId = null;
        $this->showBillingForm = false;
        $this->dispatch('toast', message: $editing ? 'Billing request updated.' : 'Billing request sent to Finance.', type: 'success');
    }

    public function markInvoiceSent(int $invoiceId): void
    {
        $invoice = $this->client->invoices()->findOrFail($invoiceId);

        if (! $this->canManageClientFinancials() || $invoice->status !== 'draft') {
            return;
        }

        $invoice->update(['status' => 'sent']);
        $this->dispatch('toast', message: 'Invoice marked as sent.', type: 'success');
    }

    protected function canRequestInvoiceEdit($invoice): bool
    {
        return $this->canManageClientFinancials() && $invoice->isEditable() && ! $invoice->pendingEditRequest;
    }

    public function openEditRequestForm(int $invoiceId): void
    {
        $invoice = $this->client->invoices()->findOrFail($invoiceId);

        if (! $this->canRequestInvoiceEdit($invoice)) {
            return;
        }

        $lineItems = $invoice->line_items ?: [['description' => $invoice->billingRequest?->summary() ?? 'Services rendered', 'amount' => $invoice->amount]];

        $this->editingInvoiceId = $invoice->id;
        $this->edit_line_items = collect($lineItems)->map(fn ($item) => [
            'description' => $item['description'] ?? '',
            'amount' => (string) ($item['amount'] ?? 0),
        ])->all();
        $this->edit_due_date = $invoice->due_date?->toDateString() ?? '';
        $this->resetValidation();
        $this->showEditInvoiceForm = true;
    }

    public function addEditLineItem(): void
    {
        $this->edit_line_items[] = ['description' => '', 'amount' => ''];
    }

    public function removeEditLineItem(int $index): void
    {
        if (count($this->edit_line_items) > 1) {
            unset($this->edit_line_items[$index]);
            $this->edit_line_items = array_values($this->edit_line_items);
        }
    }

    public function submitEditRequest(): void
    {
        $invoice = $this->client->invoices()->findOrFail($this->editingInvoiceId);

        if (! $this->canRequestInvoiceEdit($invoice)) {
            return;
        }

        $this->validate([
            'edit_line_items' => 'required|array|min:1',
            'edit_line_items.*.description' => 'required|string|max:255',
            'edit_line_items.*.amount' => 'required|numeric|min:0.01',
            'edit_due_date' => 'required|date',
        ]);

        $lineItems = collect($this->edit_line_items)->map(fn ($item) => [
            'description' => $item['description'],
            'amount' => (float) $item['amount'],
        ])->values()->all();

        $amount = array_sum(array_column($lineItems, 'amount'));
        $taxPercent = (float) $invoice->tax_percent;
        $tax = $amount * ($taxPercent / 100);

        InvoiceEditRequest::create([
            'invoice_id' => $invoice->id,
            'requested_by' => Auth::id(),
            'line_items' => $lineItems,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'total_amount' => $amount + $tax,
            'due_date' => $this->edit_due_date,
            'status' => 'pending',
        ]);

        $this->editingInvoiceId = null;
        $this->showEditInvoiceForm = false;
        $this->dispatch('toast', message: 'Edit request submitted for finance review.', type: 'success');
    }

    public function render()
    {
        $authUser = Auth::user();

        $projects = $this->client->projects()
            ->with(['assignedTo', 'developers', 'attachments'])
            ->withCount([
                'tasks as total_tasks_count' => fn ($q) => $q->where('cancelled', false),
                'tasks as done_tasks_count' => fn ($q) => $q->where('cancelled', false)->where('status', 'done'),
            ])
            ->when($this->projectSearch !== '', fn ($q) => $q->where('name', 'like', '%'.$this->projectSearch.'%'))
            ->latest()
            ->paginate(5, ['*'], 'projectsPage');

        $projectRequests = ProjectRequest::whereIn('project_id', $projects->pluck('id'))
            ->with(['creator', 'comments.author', 'comments.attachments', 'attachments', 'convertedTask'])
            ->latest()
            ->get()
            ->groupBy('project_id');

        return view('livewire.sales.client-show', [
            'projects' => $projects,
            'allProjects' => $this->client->projects()->with('attachments')->orderBy('name')->get(),
            'projectRequests' => $projectRequests,
            'viewingRequest' => $this->viewingRequestId ? ProjectRequest::with(['creator', 'comments.author', 'comments.attachments', 'attachments', 'convertedTask'])->find($this->viewingRequestId) : null,
            'billingRequests' => $this->client->billingRequests()->with('tasks', 'billedTasks', 'project')->where('status', '!=', 'invoiced')->latest()->paginate(5, ['*'], 'billingRequestsPage'),
            'invoices' => $this->client->invoices()->latest()->paginate(5, ['*'], 'invoicesPage'),
            'canManageClientFinancials' => $this->canManageClientFinancials(),
            'totalHours' => $this->client->timesheets()->sum('hours'),
            'billableHours' => $this->client->billableHours(),
            'canManageProjects' => $authUser->isManager() || $authUser->isSuperAdmin(),
            'managersAndOwners' => User::role(['manager_engineering', 'team_lead_it', 'super_admin'])->orderBy('name')->get(),
            'engineeringManagers' => User::role('manager_engineering')->orderBy('name')->get(),
            'developersList' => User::role('programmer')->orderBy('name')->get(),
        ]);
    }
}
