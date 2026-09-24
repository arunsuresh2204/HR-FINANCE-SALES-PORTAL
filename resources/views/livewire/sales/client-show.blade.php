<div>
    <x-page-header :title="$client->business_name" :subtitle="$client->business_type ?? 'Client'">
        <x-slot:actions>
            <a href="{{ route('sales.clients') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back</a>
            <button wire:click="openProjectForm" class="btn-glass-secondary"><x-icon name="briefcase" class="h-4 w-4" /> New Project</button>
            <button wire:click="openBillingForm" class="btn-glass-primary"><x-icon name="cash" class="h-4 w-4" /> New Billing Request</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white">Business Details</h2>
                    <button wire:click="openBusinessForm" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                </div>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-white/40">Business Type</dt><dd class="mt-0.5 text-white">{{ $client->business_type ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Business Address</dt><dd class="mt-0.5 text-white">{{ $client->business_address ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Owner / Decision Maker</dt><dd class="mt-0.5 text-white">{{ $client->owner_name ?? '—' }} {{ $client->owner_designation ? '('.$client->owner_designation.')' : '' }}</dd></div>
                    <div><dt class="text-white/40">Contact</dt><dd class="mt-0.5 text-white">{{ $client->owner_contact ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Phone Number</dt><dd class="mt-0.5 text-white">{{ $client->owner_phone ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">GSTIN / VAT / Tax ID</dt><dd class="mt-0.5 text-white">{{ $client->tax_id ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Sales Person</dt><dd class="mt-0.5 text-white">{{ $client->salesPerson->name }}</dd></div>
                    <div><dt class="text-white/40">Actual Hours Logged</dt><dd class="mt-0.5 text-white">{{ number_format($totalHours, 1) }}h</dd></div>
                    <div><dt class="text-white/40">Billable Hours (Invoiced)</dt><dd class="mt-0.5 text-white">{{ number_format($billableHours, 1) }}h</dd></div>
                </dl>

                @if ($showBusinessForm)
                    <form wire:submit="saveBusinessDetails" class="mt-4 space-y-4 border-t border-white/10 pt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="business_name" value="Business Name" />
                                <x-text-input wire:model="business_name" id="business_name" type="text" class="mt-0" />
                                <x-input-error :messages="$errors->get('business_name')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="business_type" value="Business Type" />
                                <x-text-input wire:model="business_type" id="business_type" type="text" class="mt-0" placeholder="e.g. E-commerce, Restaurant" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="business_address" value="Business Address" />
                            <textarea wire:model="business_address" id="business_address" rows="2" class="input-glass"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="owner_name" value="Owner / Decision Maker" />
                                <x-text-input wire:model="owner_name" id="owner_name" type="text" class="mt-0" />
                            </div>
                            <div>
                                <x-input-label for="owner_designation" value="Designation" />
                                <x-text-input wire:model="owner_designation" id="owner_designation" type="text" class="mt-0" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="owner_contact" value="Contact (Email / Phone)" />
                                <x-text-input wire:model="owner_contact" id="owner_contact" type="text" class="mt-0" />
                            </div>
                            <div>
                                <x-input-label for="owner_phone" value="Phone Number" />
                                <x-text-input wire:model="owner_phone" id="owner_phone" type="tel" class="mt-0" placeholder="e.g. +91 98765 43210" />
                                <x-input-error :messages="$errors->get('owner_phone')" class="mt-1" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="tax_id" value="GSTIN / VAT / Tax ID" />
                            <x-text-input wire:model="tax_id" id="tax_id" type="text" class="mt-0" placeholder="e.g. 32ABBCS6427Q1ZY" />
                        </div>
                        <div class="flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showBusinessForm', false)">Cancel</x-secondary-button>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="glass-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white">Projects</h2>
                    <button wire:click="openProjectForm" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ New Project</button>
                </div>
                <div class="space-y-2">
                    @forelse ($projects as $project)
                        <div class="glass-inset p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-white">{{ $project->name }}</p>
                                <div class="flex items-center gap-2">
                                    @if ($project->total_tasks_count > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[11px] font-semibold text-white/60">
                                            <span class="h-1.5 w-10 overflow-hidden rounded-full bg-white/10">
                                                <span class="block h-full rounded-full bg-emerald-400" style="width: {{ round($project->done_tasks_count / $project->total_tasks_count * 100) }}%"></span>
                                            </span>
                                            {{ $project->done_tasks_count }}/{{ $project->total_tasks_count }} tasks done
                                        </span>
                                    @endif
                                    <x-status-pill :status="$project->status" />
                                </div>
                            </div>
                            @if ($project->description)
                                <p class="mt-0.5 text-xs text-white/45">{{ $project->description }}</p>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-white/40">
                                <span>Assigned to: {{ $project->assignedTo->name ?? '—' }}</span>
                                <span>Developers: {{ $project->developers->pluck('name')->join(', ') ?: 'None assigned' }}</span>
                                <span>Currency: {{ $project->currency }}</span>
                                @if ($canManageProjects || $project->assigned_to === auth()->id())
                                    <button wire:click="openDeveloperForm({{ $project->id }})" class="font-semibold text-gold-300 hover:text-gold-200">Assign Developers</button>
                                @endif
                                @if ($canManageClientFinancials)
                                    <button wire:click="editProject({{ $project->id }})" class="font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                                    <button wire:click="deleteProject({{ $project->id }})" wire:confirm="Delete this project? This can't be undone." class="font-semibold text-white/40 hover:text-rose-300">Delete</button>
                                @endif
                            </div>
                            @if ($project->attachments->isNotEmpty())
                                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px]">
                                    @foreach ($project->attachments as $attachment)
                                        <a href="{{ $attachment->url() }}" target="_blank" class="font-semibold text-gold-300 hover:text-gold-200">{{ $attachment->original_name }}</a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-3 border-t border-white/10 pt-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-white/35">Requests</p>
                                    @if ($canManageClientFinancials)
                                        <button wire:click="openRequestForm({{ $project->id }})" class="text-[11px] font-semibold text-gold-300 hover:text-gold-200">+ New Request</button>
                                    @endif
                                </div>
                                <div class="mt-1.5 space-y-1.5">
                                    @forelse (($projectRequests[$project->id] ?? []) as $request)
                                        <button wire:click="openRequestDetail({{ $request->id }})" class="glass-inset flex w-full items-center justify-between gap-2 p-2 text-left hover:bg-white/10">
                                            <span class="truncate text-xs text-white/70">{{ $request->title }}</span>
                                            <span class="flex shrink-0 items-center gap-2">
                                                @if ($request->comments->isNotEmpty())
                                                    <span class="text-[10px] text-white/35">{{ $request->comments->count() }} {{ Str::plural('reply', $request->comments->count()) }}</span>
                                                @endif
                                                <x-status-pill :status="$request->status" />
                                            </span>
                                        </button>
                                    @empty
                                        <p class="text-xs text-white/35">No requests raised for this project yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No projects created yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="glass-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white">Agreement</h2>
                    <button wire:click="$set('showAgreementForm', true)" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                </div>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-white/40">Effective Date</dt><dd class="mt-0.5 text-white">{{ $client->agreement_effective_date?->format('M j, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Signed File</dt><dd class="mt-0.5">
                        @if ($client->agreement_file)
                            <a href="{{ Storage::url($client->agreement_file) }}" target="_blank" class="text-gold-300 hover:text-gold-200">View Agreement</a>
                        @else
                            <span class="text-white/40">Not uploaded</span>
                        @endif
                    </dd></div>
                </dl>
                @if ($client->agreement_scope_summary)
                    <p class="mt-3 text-sm text-white/60">{{ $client->agreement_scope_summary }}</p>
                @endif

                @if ($showAgreementForm)
                    <form wire:submit="saveAgreement" class="mt-4 space-y-4 border-t border-white/10 pt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="agreement_effective_date" value="Effective Date" />
                                <x-text-input wire:model="agreement_effective_date" id="agreement_effective_date" type="date" class="mt-0" />
                            </div>
                            <div>
                                <x-input-label for="agreement_file" value="Signed Agreement File" />
                                <input wire:model="agreement_file" id="agreement_file" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                            </div>
                        </div>
                        <div>
                            <x-input-label for="agreement_scope_summary" value="Scope Summary" />
                            <textarea wire:model="agreement_scope_summary" id="agreement_scope_summary" rows="2" class="input-glass"></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showAgreementForm', false)">Cancel</x-secondary-button>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="glass-card" id="invoices">
                <h2 class="mb-4 text-base font-bold text-white">Invoices</h2>
                <div class="space-y-2">
                    @forelse ($invoices as $invoice)
                        <div class="glass-inset p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $invoice->invoice_number }}</p>
                                    <p class="text-xs text-white/40">{{ $invoice->money($invoice->total_amount) }} &middot; Due {{ $invoice->due_date?->format('M j, Y') ?? '—' }}</p>
                                </div>
                                <x-status-pill :status="$invoice->status" />
                            </div>
                            @if ($canManageClientFinancials)
                                <div class="mt-2 flex flex-wrap items-center gap-3">
                                    @if ($invoice->status === 'draft')
                                        <button wire:click="markInvoiceSent({{ $invoice->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Mark as Sent</button>
                                    @endif
                                    @if ($invoice->pendingEditRequest)
                                        <span class="badge-glass !border-gold-400/25 !bg-gold-400/10 !text-gold-200 !text-[10px]">Edit Requested &middot; awaiting finance review</span>
                                    @elseif ($invoice->isEditable())
                                        <button wire:click="openEditRequestForm({{ $invoice->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Request Edit</button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No invoices yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white/50">Billing Requests</h2>
                <div class="space-y-3">
                    @forelse ($billingRequests as $br)
                        <div class="glass-inset p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-white">{{ \App\Support\Currency::format($br->amount, $br->currency) }}</p>
                                <x-status-pill :status="$br->status" />
                            </div>
                            <p class="mt-1 text-xs text-white/45">{{ $br->summary() }}</p>
                            @if (! $br->isFromTask() && $br->projectsLabel())
                                <p class="mt-1 text-[11px] text-white/30">Project: {{ $br->projectsLabel() }}</p>
                            @endif
                            <div class="mt-1 flex items-center justify-between">
                                <p class="text-[11px] text-white/30">{{ $br->created_at->format('M j, Y') }}</p>
                                @if ($br->status === 'pending' && (auth()->id() === $br->created_by || $canManageClientFinancials))
                                    <div class="flex gap-3">
                                        @unless ($br->isFromTask())
                                            <button wire:click="editBillingRequest({{ $br->id }})" class="text-[11px] font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                                        @endunless
                                        <button wire:click="deleteBillingRequest({{ $br->id }})" wire:confirm="{{ $br->isFromTask() ? 'Delete this billing request? The tasks in it become available to bill again.' : 'Delete this billing request? This can\'t be undone.' }}" class="text-[11px] font-semibold text-white/40 hover:text-rose-300">Delete</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No billing requests yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-modal-glass wire-model="showProjectForm" :title="$editingProjectId ? 'Edit Project' : 'New Project'">
        <form wire:submit="submitProject" class="space-y-4">
            <div>
                <x-input-label for="project_name" value="Project Name" />
                <x-text-input wire:model="project_name" id="project_name" type="text" class="mt-0" placeholder="e.g. Website Revamp" />
                <x-input-error :messages="$errors->get('project_name')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="project_description" value="Description (optional)" />
                <textarea wire:model="project_description" id="project_description" rows="2" class="input-glass"></textarea>
            </div>
            <div>
                <x-input-label for="project_currency" value="Currency" />
                @if ($projectCurrencyLocked)
                    <p class="input-glass !cursor-default text-white/50">{{ $project_currency }}</p>
                    <p class="mt-1 text-xs text-white/35">Locked — this project already has priced tasks, so its currency can't be changed.</p>
                @else
                    <select wire:model="project_currency" id="project_currency" class="input-glass">
                        @foreach (\App\Support\Currency::options() as $code => $symbol)
                            <option value="{{ $code }}">{{ $code }} ({{ $symbol }})</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-white/35">Every task and category under this project is priced in this currency — it locks once the project has a priced task.</p>
                    <x-input-error :messages="$errors->get('project_currency')" class="mt-1" />
                @endif
            </div>
            <div>
                <x-input-label for="project_requirement_files" value="Requirement Files (optional)" />
                @if ($editingProjectId && ($editingProject = $projects->firstWhere('id', $editingProjectId)) && $editingProject->attachments->isNotEmpty())
                    <div class="mb-2 space-y-1">
                        @foreach ($editingProject->attachments as $attachment)
                            <div class="flex items-center justify-between gap-2 text-xs">
                                <a href="{{ $attachment->url() }}" target="_blank" class="truncate text-gold-300 hover:text-gold-200">{{ $attachment->original_name }}</a>
                                <button type="button" wire:click="deleteProjectAttachment({{ $attachment->id }})" wire:confirm="Remove this attachment?" class="shrink-0 text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input wire:model="project_requirement_files" id="project_requirement_files" type="file" multiple class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                <div wire:loading wire:target="project_requirement_files" class="mt-1 text-xs text-white/40">Uploading&hellip;</div>
                <x-input-error :messages="$errors->get('project_requirement_files')" class="mt-1" />
                <x-input-error :messages="$errors->get('project_requirement_files.*')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="assigned_to" :value="$canManageProjects ? 'Assign To (optional)' : 'Assign To (Engineering Manager)'" />
                <select wire:model="assigned_to" id="assigned_to" class="input-glass">
                    @if ($canManageProjects)
                        <option value="">— Keep assigned to me —</option>
                        @foreach ($managersAndOwners as $mgr)
                            <option value="{{ $mgr->id }}">{{ $mgr->name }}{{ $mgr->designation ? ' ('.$mgr->designation.')' : '' }}</option>
                        @endforeach
                    @else
                        <option value="">— Select the Engineering Manager —</option>
                        @foreach ($engineeringManagers as $mgr)
                            <option value="{{ $mgr->id }}">{{ $mgr->name }}{{ $mgr->designation ? ' ('.$mgr->designation.')' : '' }}</option>
                        @endforeach
                    @endif
                </select>
                @unless ($canManageProjects)
                    <p class="mt-1 text-xs text-white/35">The Engineering Manager will hand this off to a team leader and developers.</p>
                @endunless
                <x-input-error :messages="$errors->get('assigned_to')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>{{ $editingProjectId ? 'Save Changes' : 'Create Project' }}</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showDeveloperForm" title="Assign Developers" max-width="sm">
        <form wire:submit="saveDevelopers" class="space-y-4">
            <div class="max-h-64 space-y-2 overflow-y-auto">
                @forelse ($developersList as $dev)
                    <label class="glass-inset flex items-center gap-2 p-3 text-sm text-white/80">
                        <input type="checkbox" wire:model="developer_ids" value="{{ $dev->id }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                        {{ $dev->name }}
                    </label>
                @empty
                    <p class="text-sm text-white/40">No developers on file yet.</p>
                @endforelse
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showBillingForm" :title="$editingBillingRequestId ? 'Edit Billing Request' : 'New Billing Request'" max-width="2xl">
        <form wire:submit="submitBillingRequest" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="project_id" value="Project (optional)" />
                    <select wire:model="project_id" id="project_id" class="input-glass">
                        <option value="">— No specific project —</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="currency" value="Currency" />
                    <select wire:model="currency" id="currency" class="input-glass">
                        @foreach (\App\Support\Currency::options() as $code => $symbol)
                            <option value="{{ $code }}">{{ $code }} ({{ $symbol }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <x-input-label value="Billing Type" />
                <div class="mt-1 grid grid-cols-2 gap-3">
                    <label class="glass-inset flex cursor-pointer items-center gap-2 p-3 text-sm {{ $billing_type === 'milestone' ? 'ring-1 ring-gold-400/50' : '' }}">
                        <input type="radio" wire:model.live="billing_type" value="milestone" class="text-gold-400 focus:ring-gold-400/40">
                        <span>Project / Milestone</span>
                    </label>
                    <label class="glass-inset flex cursor-pointer items-center gap-2 p-3 text-sm {{ $billing_type === 'hourly' ? 'ring-1 ring-gold-400/50' : '' }}">
                        <input type="radio" wire:model.live="billing_type" value="hourly" class="text-gold-400 focus:ring-gold-400/40">
                        <span>Hourly-based Task</span>
                    </label>
                </div>
            </div>

            @if ($billing_type === 'milestone')
                <div>
                    <x-input-label for="amount" value="Amount" />
                    <x-text-input wire:model="amount" id="amount" type="number" step="0.01" class="mt-0" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="milestone_description" value="Milestone Description" />
                    <x-text-input wire:model="milestone_description" id="milestone_description" type="text" class="mt-0" placeholder="e.g. 50% advance" />
                    <x-input-error :messages="$errors->get('milestone_description')" class="mt-1" />
                </div>
            @else
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <x-input-label value="Tasks" class="mb-0" />
                        <button type="button" wire:click="addTaskRow" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ Add Task</button>
                    </div>
                    <div class="grid grid-cols-[1fr_6rem_6rem_1.5rem] gap-2 px-0.5 text-xs font-semibold uppercase tracking-wide text-white/30">
                        <span>Task</span><span>Hours</span><span>Rate</span><span></span>
                    </div>
                    <div class="mt-1 space-y-2">
                        @foreach ($tasks as $index => $task)
                            <div class="grid grid-cols-[1fr_6rem_6rem_1.5rem] items-start gap-2" wire:key="task-row-{{ $index }}">
                                <div>
                                    <x-text-input wire:model="tasks.{{ $index }}.description" type="text" class="mt-0" placeholder="Task description" />
                                    <x-input-error :messages="$errors->get('tasks.'.$index.'.description')" class="mt-1" />
                                </div>
                                <div>
                                    <x-text-input wire:model.live.debounce.400ms="tasks.{{ $index }}.hours" type="number" step="0.25" class="mt-0" placeholder="Hours" />
                                    <x-input-error :messages="$errors->get('tasks.'.$index.'.hours')" class="mt-1" />
                                </div>
                                <div>
                                    <x-text-input wire:model.live.debounce.400ms="tasks.{{ $index }}.rate" type="number" step="0.01" class="mt-0" placeholder="Rate" />
                                    <x-input-error :messages="$errors->get('tasks.'.$index.'.rate')" class="mt-1" />
                                </div>
                                @if (count($tasks) > 1)
                                    <button type="button" wire:click="removeTaskRow({{ $index }})" class="mt-2 text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                                @else
                                    <span></span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-white/40">Each task can have its own rate — e.g. senior vs. junior work on the same request.</p>
                    @php
                        $totalTaskHours = collect($tasks)->sum(fn ($t) => (float) ($t['hours'] ?? 0));
                        $estimatedAmount = collect($tasks)->sum(fn ($t) => (float) ($t['hours'] ?? 0) * (float) ($t['rate'] ?? 0));
                    @endphp
                    <p class="mt-1 text-xs text-white/40">
                        {{ number_format($totalTaskHours, 2) }} total hours &middot; Estimated amount: {{ \App\Support\Currency::format($estimatedAmount, $currency) }}
                    </p>
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>{{ $editingBillingRequestId ? 'Save Changes' : 'Send to Finance' }}</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showEditInvoiceForm" title="Request Invoice Edit" max-width="2xl">
        <form wire:submit="submitEditRequest" class="space-y-4">
            <p class="rounded-lg border border-gold-400/20 bg-gold-400/5 p-3 text-xs text-white/60">These changes won't apply immediately &mdash; they're sent to the Finance team for review and approval.</p>
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <x-input-label value="Line Items" class="mb-0" />
                    <button type="button" wire:click="addEditLineItem" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ Add Line Item</button>
                </div>
                <div class="space-y-2">
                    @foreach ($edit_line_items as $index => $item)
                        <div class="glass-inset flex items-start gap-2 p-2.5" wire:key="edit-invoice-line-item-{{ $index }}">
                            <div class="flex-1">
                                <x-text-input wire:model="edit_line_items.{{ $index }}.description" type="text" class="mt-0" placeholder="Description" />
                            </div>
                            <div class="w-32 shrink-0">
                                <x-text-input wire:model="edit_line_items.{{ $index }}.amount" type="number" step="0.01" class="mt-0" placeholder="Amount" />
                            </div>
                            @if (count($edit_line_items) > 1)
                                <button type="button" wire:click="removeEditLineItem({{ $index }})" class="mt-2 shrink-0 text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('edit_line_items')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="edit_due_date" value="Due Date" />
                <x-text-input wire:model="edit_due_date" id="edit_due_date" type="date" class="mt-0" />
                <x-input-error :messages="$errors->get('edit_due_date')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Submit Request</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showRequestForm" title="New Request" max-width="lg">
        <form wire:submit="submitRequest" class="space-y-4">
            <p class="text-xs text-white/45">Raise something for the Engineering Manager to review — a client ask, a bug report, a change request. They'll respond here, and convert it into a task once it's ready to work on.</p>
            <div>
                <x-input-label for="request_title" value="Title" />
                <x-text-input wire:model="request_title" id="request_title" type="text" class="mt-0" placeholder="e.g. Client wants a dark mode toggle" />
                <x-input-error :messages="$errors->get('request_title')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="request_description" value="Details (optional)" />
                <textarea wire:model="request_description" id="request_description" rows="4" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('request_description')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="request_attachments" value="Attachments (optional)" />
                <input wire:model="request_attachments" id="request_attachments" type="file" multiple class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                <div wire:loading wire:target="request_attachments" class="mt-1 text-xs text-white/40">Uploading&hellip;</div>
                <x-input-error :messages="$errors->get('request_attachments')" class="mt-1" />
                <x-input-error :messages="$errors->get('request_attachments.*')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Send Request</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showRequestDetail" :title="$viewingRequest->title ?? 'Request'" max-width="lg">
        @if ($viewingRequest)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-white/40">Raised by {{ $viewingRequest->creator->name }} on {{ $viewingRequest->created_at->format('M j, Y') }}</p>
                    <x-status-pill :status="$viewingRequest->status" />
                </div>
                @if ($viewingRequest->description)
                    <p class="glass-inset p-3 text-sm text-white/70">{{ $viewingRequest->description }}</p>
                @endif
                @if ($viewingRequest->attachments->isNotEmpty())
                    <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs">
                        @foreach ($viewingRequest->attachments as $attachment)
                            <a href="{{ $attachment->url() }}" target="_blank" class="font-semibold text-gold-300 hover:text-gold-200">{{ $attachment->original_name }}</a>
                        @endforeach
                    </div>
                @endif

                @if ($viewingRequest->status === 'converted')
                    <div class="rounded-lg border border-emerald-400/20 bg-emerald-400/5 p-3 text-xs text-emerald-200">
                        Converted to a task by {{ $viewingRequest->convertedBy->name ?? '—' }} on {{ $viewingRequest->converted_at?->format('M j, Y') }}.
                    </div>
                @endif

                <div class="space-y-2 border-t border-white/10 pt-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-white/35">Conversation</p>
                    @forelse ($viewingRequest->comments as $comment)
                        <div class="glass-inset p-2.5">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-white">{{ $comment->author->name }}</p>
                                <p class="text-[10px] text-white/35">{{ $comment->created_at->format('M j, g:i A') }}</p>
                            </div>
                            <p class="mt-1 text-xs text-white/65">{{ $comment->body }}</p>
                            @if ($comment->attachments->isNotEmpty())
                                <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1">
                                    @foreach ($comment->attachments as $attachment)
                                        <a href="{{ $attachment->url() }}" target="_blank" class="text-[11px] font-semibold text-gold-300 hover:text-gold-200">{{ $attachment->original_name }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-white/35">No replies yet.</p>
                    @endforelse
                </div>

                <form wire:submit="submitReply" class="space-y-2 border-t border-white/10 pt-3">
                    <textarea wire:model="reply_body" rows="2" class="input-glass" placeholder="Write a reply&hellip;"></textarea>
                    <x-input-error :messages="$errors->get('reply_body')" class="mt-1" />
                    <input wire:model="reply_attachments" id="reply_attachments" type="file" multiple class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                    <div wire:loading wire:target="reply_attachments" class="text-xs text-white/40">Uploading&hellip;</div>
                    <x-input-error :messages="$errors->get('reply_attachments')" class="mt-1" />
                    <x-input-error :messages="$errors->get('reply_attachments.*')" class="mt-1" />
                    <div class="flex justify-end">
                        <x-primary-button>Reply</x-primary-button>
                    </div>
                </form>
            </div>
        @endif
    </x-modal-glass>
</div>
