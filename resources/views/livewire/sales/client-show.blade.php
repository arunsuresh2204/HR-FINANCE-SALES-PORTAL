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
                <h2 class="mb-4 text-base font-bold text-white">Business Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-white/40">Business Address</dt><dd class="mt-0.5 text-white">{{ $client->business_address ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Owner / Decision Maker</dt><dd class="mt-0.5 text-white">{{ $client->owner_name ?? '—' }} {{ $client->owner_designation ? '('.$client->owner_designation.')' : '' }}</dd></div>
                    <div><dt class="text-white/40">Contact</dt><dd class="mt-0.5 text-white">{{ $client->owner_contact ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">GSTIN / VAT / Tax ID</dt><dd class="mt-0.5 text-white">{{ $client->tax_id ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Sales Person</dt><dd class="mt-0.5 text-white">{{ $client->salesPerson->name }}</dd></div>
                    <div><dt class="text-white/40">Actual Hours Logged</dt><dd class="mt-0.5 text-white">{{ number_format($totalHours, 1) }}h</dd></div>
                    <div><dt class="text-white/40">Billable Hours (Invoiced)</dt><dd class="mt-0.5 text-white">{{ number_format($billableHours, 1) }}h</dd></div>
                </dl>
            </div>

            <div class="glass-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white">Projects</h2>
                    <button wire:click="openProjectForm" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ New Project</button>
                </div>
                <div class="space-y-2">
                    @forelse ($projects as $project)
                        <div class="glass-inset flex items-center justify-between p-3">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $project->name }}</p>
                                @if ($project->description)
                                    <p class="mt-0.5 text-xs text-white/45">{{ $project->description }}</p>
                                @endif
                            </div>
                            <x-status-pill :status="$project->status" />
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

            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Invoices</h2>
                <div class="space-y-2">
                    @forelse ($invoices as $invoice)
                        <div class="glass-inset flex items-center justify-between p-3">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $invoice->invoice_number }}</p>
                                <p class="text-xs text-white/40">{{ $invoice->money($invoice->total_amount) }} &middot; Due {{ $invoice->due_date?->format('M j, Y') ?? '—' }}</p>
                            </div>
                            <x-status-pill :status="$invoice->status" />
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
                            @if ($br->project)
                                <p class="mt-1 text-[11px] text-white/30">Project: {{ $br->project->name }}</p>
                            @endif
                            <p class="mt-1 text-[11px] text-white/30">{{ $br->created_at->format('M j, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No billing requests yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-modal-glass wire-model="showProjectForm" title="New Project">
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
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Create Project</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showBillingForm" title="New Billing Request" max-width="2xl">
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
                        <option value="INR">INR (₹)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
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
                <x-primary-button>Send to Finance</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
