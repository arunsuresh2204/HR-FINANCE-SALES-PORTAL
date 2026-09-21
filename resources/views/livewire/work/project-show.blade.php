<div>
    <x-page-header :title="$project->name" :subtitle="$project->client->business_name">
        <x-slot:actions>
            <a href="{{ route('work.my-projects') }}" class="btn-glass-secondary text-xs">&larr; My Projects</a>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-card">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                @if ($project->description)
                    <p class="text-sm text-white/50">{{ $project->description }}</p>
                @endif
            </div>
            <x-status-pill :status="$project->status" />
        </div>
        <div class="mt-4 flex flex-wrap gap-x-8 gap-y-2 border-t border-white/10 pt-4 text-sm">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-white/35">Assigned to</p>
                <p class="mt-0.5 font-semibold text-white">{{ $project->assignedTo->name ?? 'Unassigned' }}</p>
            </div>
            @if ($isManager || auth()->user()->isTeamLead())
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-white/35">Developers</p>
                    <p class="mt-0.5 font-semibold text-white">{{ $project->developers->pluck('name')->join(', ') ?: 'None yet' }}</p>
                </div>
            @endif
        </div>
    </div>

    @if ($project->needsEstimate())
        <div class="glass-card mt-4 border-gold-400/25">
            <p class="text-sm font-semibold text-white">This project is awaiting a cost estimate.</p>
            <p class="mt-1 text-xs text-white/45">Add at least one category with an estimated amount to move it to Active and start creating tasks against it.</p>
            @if ($canManage)
                <button wire:click="openCategoryForm" class="btn-glass-primary mt-3 text-xs"><x-icon name="plus" class="h-4 w-4" /> Add Category</button>
            @endif
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-0">
        <div class="flex gap-6 overflow-x-auto">
            <button wire:click="setTab('board')" class="tab-btn {{ $tab === 'board' ? 'active' : '' }}">
                Board
                @if ($pendingCount > 0)
                    <span class="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-violet-400 px-1 text-[10px] font-bold text-ink-950">{{ $pendingCount }}</span>
                @endif
            </button>
            <button wire:click="setTab('list')" class="tab-btn {{ $tab === 'list' ? 'active' : '' }}">List</button>
            <button wire:click="setTab('categories')" class="tab-btn {{ $tab === 'categories' ? 'active' : '' }}">Cost Estimate</button>
        </div>
        <button wire:click="openTaskModal" class="btn-glass-primary mb-2 text-xs"><x-icon name="plus" class="h-4 w-4" /> New Task</button>
    </div>

    @if ($pendingCount > 0)
        <div class="mt-4 flex items-center gap-2 rounded-lg border border-violet-400/30 bg-violet-400/10 px-3 py-2 text-xs font-semibold text-violet-200">
            <x-icon name="bell" class="h-4 w-4 shrink-0" />
            {{ $pendingCount }} new task{{ $pendingCount > 1 ? 's' : '' }} requested — needs your review &amp; pricing
        </div>
    @endif

    {{-- ================= BOARD ================= --}}
    @if ($tab === 'board')
        <div class="mt-4 overflow-x-auto pb-2">
            <div class="flex gap-4">
                @foreach ($statuses as $key => $label)
                    <div class="kanban-column">
                        <div class="flex items-center justify-between px-1">
                            <p class="text-xs font-bold uppercase tracking-wide text-white/50">{{ $label }}</p>
                            <span class="text-xs text-white/30">{{ $board[$key]->count() }}</span>
                        </div>
                        @forelse ($board[$key] as $task)
                            @php
                                $weeksLate = $task->weeksLate();
                                $tint = match (true) {
                                    $task->cancelled => 'opacity-55',
                                    $weeksLate >= 4 => '!bg-rose-400/[0.32] !border-rose-400/70',
                                    $weeksLate === 3 => '!bg-rose-400/[0.22] !border-rose-400/55',
                                    $weeksLate === 2 => '!bg-rose-400/[0.13] !border-rose-400/40',
                                    $weeksLate === 1 => '!bg-rose-400/[0.06] !border-rose-400/25',
                                    default => '',
                                };
                            @endphp
                            <div wire:click="openTaskDetail({{ $task->id }})" wire:key="task-card-{{ $task->id }}" class="kanban-card {{ $tint }}">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-white {{ $task->cancelled ? 'line-through' : '' }}">{{ $task->title }}</p>
                                    <select onclick="event.stopPropagation()" onchange="$wire.setTaskStatus({{ $task->id }}, this.value)" class="input-glass !w-auto !py-1 !text-xs shrink-0">
                                        @foreach ($statuses as $sKey => $sLabel)
                                            <option value="{{ $sKey }}" @selected($task->status === $sKey)>{{ $sLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    @if ($task->cancelled)
                                        <span class="pill border-rose-400/20 bg-rose-400/15 text-rose-300">Cancelled</span>
                                    @endif
                                    @if ($task->category)
                                        <span class="pill border-gold-400/20 bg-gold-400/10 text-gold-300">{{ $task->category->name }}</span>
                                    @else
                                        <span class="pill border-white/15 bg-white/5 text-white/40">non-billable</span>
                                    @endif
                                    @if ($task->visibility === 'private')
                                        <span class="pill border-white/15 bg-white/5 text-white/40">Private</span>
                                    @endif
                                    @if ($task->pending_approval)
                                        <span class="pill border-violet-400/20 bg-violet-400/15 text-violet-300">Pending Approval</span>
                                    @endif
                                    @if ($isManager && $task->amount)
                                        <span class="pill border-white/15 bg-white/10 text-white/70">{{ \App\Support\Currency::format($task->amount, $task->currency) }}</span>
                                    @endif
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs text-white/45">
                                    <span>{{ $task->assignee->name ?? 'Unassigned' }}</span>
                                    <span class="{{ $weeksLate > 0 ? 'font-semibold text-rose-300' : '' }}">
                                        {{ $task->end_date?->format('M j') ?? '—' }}
                                        @if ($weeksLate > 0)
                                            &middot; {{ $weeksLate }}w late
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="px-1 text-xs text-white/25">No tasks</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================= LIST ================= --}}
    @if ($tab === 'list')
        <div class="glass-card mt-4 overflow-x-auto !p-0">
            <table class="table-glass">
                <thead><tr><th>Task</th><th>Category</th><th>Amount</th><th>Assignee</th><th>Status</th><th>Due</th></tr></thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr wire:click="openTaskDetail({{ $task->id }})" wire:key="task-row-{{ $task->id }}" class="cursor-pointer {{ $task->cancelled ? 'opacity-55' : '' }}">
                            <td class="font-medium text-white {{ $task->cancelled ? 'line-through' : '' }}">
                                {{ $task->title }}
                                @if ($task->cancelled)
                                    <span class="pill ml-1 border-rose-400/20 bg-rose-400/15 text-rose-300">Cancelled</span>
                                @endif
                                @if ($task->visibility === 'private')
                                    <span class="pill ml-1 border-white/15 bg-white/5 text-white/40">Private</span>
                                @endif
                            </td>
                            <td class="text-white/60">{{ $task->category->name ?? '—' }}</td>
                            <td class="text-white/60">
                                @if (! $isManager) &mdash;
                                @elseif ($task->amount) {{ \App\Support\Currency::format($task->amount, $task->currency) }}
                                @elseif ($task->pending_approval) Pending
                                @else &mdash;
                                @endif
                            </td>
                            <td class="text-white/60">{{ $task->assignee->name ?? 'Unassigned' }}</td>
                            <td><x-status-pill :status="$task->status" /></td>
                            <td class="text-white/60">{{ $task->end_date?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No tasks yet — click New Task to add the first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ================= COST ESTIMATE / CATEGORIES ================= --}}
    @if ($tab === 'categories')
        <div class="mt-4 space-y-5">
            @if ($canManage)
                <div class="flex justify-end">
                    <button wire:click="openCategoryForm" class="btn-glass-secondary text-xs"><x-icon name="plus" class="h-4 w-4" /> Add Category</button>
                </div>
            @endif
            @forelse ($categories as $category)
                <div class="glass-card">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                        <p class="font-bold text-white">{{ $category->name }}</p>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-gold-300">{{ \App\Support\Currency::format($category->estimatedTotal(), $category->currency) }}</span>
                            @if ($canManage)
                                <button wire:click="openCategoryForm({{ $category->id }})" class="text-white/40 hover:text-white"><x-icon name="pencil" class="h-4 w-4" /></button>
                                <button wire:click="deleteCategory({{ $category->id }})" wire:confirm="Delete this category? Tasks in it become non-billable." class="text-white/40 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-2">
                        @forelse ($category->tasks as $task)
                            <div class="glass-inset flex items-center justify-between p-2 text-sm">
                                <span class="text-white/80">{{ $task->title }}</span>
                                <span class="text-white/50">{{ $isManager && $task->amount ? \App\Support\Currency::format($task->amount, $task->currency) : '—' }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-white/30">No tasks in this category yet</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="glass-card py-8 text-center text-sm text-white/30">No categories yet{{ $canManage ? ' — click Add Category to start pricing this project.' : '.' }}</p>
            @endforelse
        </div>
    @endif

    {{-- ================= NEW / EDIT TASK MODAL ================= --}}
    <x-modal-glass wire-model="showTaskModal" :title="$editingTaskId ? 'Edit Task' : 'New Task — '.$project->name" max-width="xl">
        <form wire:submit="saveTask" class="space-y-4">
            <div>
                <x-input-label for="task_title" value="Title" />
                <x-text-input wire:model="task_title" id="task_title" type="text" class="mt-0" />
                <x-input-error :messages="$errors->get('task_title')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="task_description" value="Description" />
                <textarea wire:model="task_description" id="task_description" rows="3" class="input-glass"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="task_start_date" value="Start date" />
                    <input wire:model="task_start_date" id="task_start_date" type="date" class="input-glass">
                </div>
                <div>
                    <x-input-label for="task_end_date" value="End date" />
                    <input wire:model="task_end_date" id="task_end_date" type="date" class="input-glass">
                    <x-input-error :messages="$errors->get('task_end_date')" class="mt-1" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="task_category_id" value="Category" />
                    <select wire:model="task_category_id" id="task_category_id" class="input-glass">
                        <option value="">— Non-billable —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="task_assignee_id" value="Assignee" />
                    <select wire:model="task_assignee_id" id="task_assignee_id" class="input-glass">
                        <option value="">Unassigned</option>
                        @foreach ($assignableUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}{{ $user->id === auth()->id() ? ' (you)' : '' }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('task_assignee_id')" class="mt-1" />
                </div>
            </div>

            <div>
                <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                    <x-input-label value="Visibility" class="!mb-0" />
                </div>
                <div class="inline-flex rounded-lg border border-white/10 bg-white/5 p-0.5 text-[11px] font-semibold">
                    <button type="button" wire:click="$set('task_visibility', 'public')" class="rounded-md px-2.5 py-1 {{ $task_visibility === 'public' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Public</button>
                    <button type="button" wire:click="$set('task_visibility', 'private')" class="rounded-md px-2.5 py-1 {{ $task_visibility === 'private' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Private</button>
                </div>
                <p class="mt-1 text-xs text-white/40">Public tasks are visible to every developer on this project. Private tasks are visible only to you.</p>
            </div>

            @if (! $editingTaskId && $isManager)
                <div>
                    <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                        <x-input-label value="Pricing (optional)" class="!mb-0" />
                        <div class="inline-flex rounded-lg border border-white/10 bg-white/5 p-0.5 text-[11px] font-semibold">
                            <button type="button" wire:click="$set('task_pricing_mode', 'fixed')" class="rounded-md px-2.5 py-1 {{ $task_pricing_mode === 'fixed' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Fixed amount</button>
                            <button type="button" wire:click="$set('task_pricing_mode', 'hourly')" class="rounded-md px-2.5 py-1 {{ $task_pricing_mode === 'hourly' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Hourly</button>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select wire:model="task_currency" class="input-glass !w-24 shrink-0">
                            @foreach (\App\Support\Currency::options() as $code => $symbol)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </select>
                        @if ($task_pricing_mode === 'fixed')
                            <x-text-input wire:model="task_amount" type="number" class="mt-0" placeholder="e.g. 15000" />
                        @else
                            <x-text-input wire:model="task_hours" type="number" step="0.25" class="mt-0" placeholder="Hours" />
                            <x-text-input wire:model="task_rate" type="number" class="mt-0" placeholder="Rate/hr" />
                        @endif
                    </div>
                </div>
            @elseif (! $editingTaskId)
                <p class="text-xs text-violet-300/80">This task will need your manager's review — they'll set the price before it can be sent to Sales.</p>
            @endif

            <div>
                <x-input-label value="Tags (comma separated)" />
                <x-text-input wire:model="task_tags" type="text" class="mt-0" placeholder="frontend, payments" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>{{ $editingTaskId ? 'Save' : 'Create Task' }}</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    {{-- ================= TASK DETAIL MODAL ================= --}}
    <x-modal-glass wire-model="showTaskDetail" :title="$viewingTask?->title ?? 'Task'" max-width="lg">
        @if ($viewingTask)
            @php($task = $viewingTask)
            <div class="flex flex-wrap items-center gap-2">
                @if ($task->category)
                    <span class="pill border-gold-400/20 bg-gold-400/10 text-gold-300">{{ $task->category->name }}</span>
                @else
                    <span class="pill border-white/15 bg-white/5 text-white/40">non-billable</span>
                @endif
            </div>

            @if ($task->pending_approval && ! $task->cancelled)
                <div class="mt-3"><span class="pill border-violet-400/20 bg-violet-400/15 text-violet-300">Pending Manager Approval</span></div>
            @endif

            @if ($task->cancelled)
                <div class="mt-3 rounded-lg border border-rose-400/25 bg-rose-400/10 px-3 py-2 text-xs">
                    <p class="font-semibold text-rose-200">Task cancelled @if($task->canceller) — by {{ $task->canceller->name }} @endif</p>
                    <p class="mt-0.5 text-rose-200/80">{{ $task->cancel_reason }}</p>
                </div>
            @endif

            @if ($task->description)
                <p class="mt-4 text-sm leading-relaxed text-white/70">{{ $task->description }}</p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-4 border-t border-white/10 pt-4 text-sm">
                <div>
                    <p class="label-glass !mb-1">Amount</p>
                    <p class="font-semibold text-gold-300">
                        @if (! $isManager) &mdash;
                        @elseif ($task->amount)
                            {{ \App\Support\Currency::format($task->amount, $task->currency) }}
                            @if ($task->hours !== null && $task->rate !== null)
                                ({{ $task->hours }}h &times; {{ \App\Support\Currency::format($task->rate, $task->currency) }}/hr)
                            @endif
                        @else &mdash;
                        @endif
                    </p>
                </div>
                <div>
                    <p class="label-glass !mb-1">Assignee</p>
                    <select onchange="$wire.setTaskAssignee({{ $task->id }}, this.value || null)" class="input-glass !w-full !py-1 !text-xs">
                        <option value="">Unassigned</option>
                        @foreach ($assignableUsers as $user)
                            <option value="{{ $user->id }}" @selected($task->assignee_id === $user->id)>{{ $user->name }}{{ $user->id === auth()->id() ? ' (you)' : '' }}</option>
                        @endforeach
                        @if ($task->assignee && ! $assignableUsers->contains('id', $task->assignee_id))
                            <option value="{{ $task->assignee_id }}" selected>{{ $task->assignee->name }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <p class="label-glass !mb-1">Visibility</p>
                    <select onchange="$wire.setTaskVisibility({{ $task->id }}, this.value)" class="input-glass !w-full !py-1 !text-xs">
                        <option value="public" @selected($task->visibility !== 'private')>Public</option>
                        <option value="private" @selected($task->visibility === 'private')>Private</option>
                    </select>
                </div>
                <div>
                    <p class="label-glass !mb-1">Dates</p>
                    <p class="text-white/80">{{ $task->start_date?->format('M j') }} &ndash; {{ $task->end_date?->format('M j') ?? '—' }}</p>
                </div>
            </div>

            @if ($task->tags)
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @foreach ($task->tags as $tag)
                        <span class="pill border-white/15 bg-white/10 text-white/55">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 border-t border-white/10 pt-4">
                <label class="label-glass">Status</label>
                <select onchange="$wire.setTaskStatus({{ $task->id }}, this.value)" class="input-glass !w-auto !py-1 !text-xs">
                    @foreach ($statuses as $sKey => $sLabel)
                        <option value="{{ $sKey }}" @selected($task->status === $sKey)>{{ $sLabel }}</option>
                    @endforeach
                </select>
            </div>

            @if ($isManager && $task->pending_approval && ! $task->cancelled)
                <div class="mt-4 border-t border-white/10 pt-4">
                    <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                        <label class="label-glass !mb-0">Approve &amp; Set Price</label>
                        <div class="inline-flex rounded-lg border border-white/10 bg-white/5 p-0.5 text-[11px] font-semibold">
                            <button type="button" wire:click="$set('approve_mode', 'fixed')" class="rounded-md px-2.5 py-1 {{ $approve_mode === 'fixed' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Fixed amount</button>
                            <button type="button" wire:click="$set('approve_mode', 'hourly')" class="rounded-md px-2.5 py-1 {{ $approve_mode === 'hourly' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Hourly</button>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select wire:model="approve_currency" class="input-glass !w-24 shrink-0">
                            @foreach (\App\Support\Currency::options() as $code => $symbol)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </select>
                        @if ($approve_mode === 'fixed')
                            <x-text-input wire:model="approve_amount" type="number" class="mt-0" placeholder="e.g. 15000" />
                        @else
                            <x-text-input wire:model="approve_hours" type="number" step="0.25" class="mt-0" placeholder="Hours" />
                            <x-text-input wire:model="approve_rate" type="number" class="mt-0" placeholder="Rate/hr" />
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('approve_amount')" class="mt-1" />
                    <div class="mt-3 flex justify-end">
                        <button wire:click="approveTask" class="btn-glass-primary text-xs">Approve Task</button>
                    </div>
                </div>
            @endif

            @if ($isManager && $task->status === 'done' && $task->isPriced() && ! $task->cancelled)
                <div class="mt-4 border-t border-white/10 pt-4 text-right">
                    <button wire:click="openNotifySalesForm" class="btn-glass-secondary text-xs"><x-icon name="cash" class="h-4 w-4" /> Notify Sales</button>
                </div>
            @endif

            @if (! $task->cancelled && $task->canBeCancelledBy(auth()->user()))
                @if (! $showCancelForm)
                    <div class="mt-4 border-t border-white/10 pt-4 text-right">
                        <button wire:click="openCancelForm" class="text-xs font-semibold text-rose-300/80 hover:text-rose-300">Cancel this task</button>
                    </div>
                @else
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <label class="label-glass">Reason for cancelling</label>
                        <textarea wire:model="cancel_reason" rows="2" class="input-glass" placeholder="e.g. Client dropped this requirement from scope"></textarea>
                        <x-input-error :messages="$errors->get('cancel_reason')" class="mt-1" />
                        <div class="mt-3 flex justify-end gap-2">
                            <x-secondary-button type="button" wire:click="$set('showCancelForm', false)">Back</x-secondary-button>
                            <button wire:click="confirmCancelTask" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-500 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-rose-400">Confirm Cancel</button>
                        </div>
                    </div>
                @endif
            @endif

            <div class="mt-5 flex justify-end border-t border-white/10 pt-4">
                <x-secondary-button type="button" @click="show = false">Close</x-secondary-button>
            </div>
        @endif
    </x-modal-glass>

    {{-- ================= NOTIFY SALES MODAL ================= --}}
    <x-modal-glass wire-model="showNotifySalesForm" title="Notify Sales" max-width="sm">
        <form wire:submit="sendToSales" class="space-y-4">
            <p class="text-sm text-white/50">Sales will see this in their Billing Requests inbox and take it to the client.</p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label value="Currency" />
                    <select wire:model="notify_currency" class="input-glass">
                        @foreach (\App\Support\Currency::options() as $code => $symbol)
                            <option value="{{ $code }}">{{ $code }} ({{ $symbol }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Amount" />
                    <x-text-input wire:model="notify_amount" type="number" class="mt-0" />
                    <x-input-error :messages="$errors->get('notify_amount')" class="mt-1" />
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Send to Sales</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    {{-- ================= CATEGORY MODAL ================= --}}
    <x-modal-glass wire-model="showCategoryForm" :title="$editingCategoryId ? 'Edit Category' : 'Add Category'" max-width="md">
        <form wire:submit="saveCategory" class="space-y-4">
            <div>
                <x-input-label for="category_name" value="Category name" />
                <x-text-input wire:model="category_name" id="category_name" type="text" class="mt-0" placeholder="e.g. Loyalty points engine" />
                <x-input-error :messages="$errors->get('category_name')" class="mt-1" />
            </div>
            <div>
                <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                    <x-input-label value="Estimated cost (optional)" class="!mb-0" />
                    <div class="inline-flex rounded-lg border border-white/10 bg-white/5 p-0.5 text-[11px] font-semibold">
                        <button type="button" wire:click="$set('category_pricing_mode', 'fixed')" class="rounded-md px-2.5 py-1 {{ $category_pricing_mode === 'fixed' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Fixed amount</button>
                        <button type="button" wire:click="$set('category_pricing_mode', 'hourly')" class="rounded-md px-2.5 py-1 {{ $category_pricing_mode === 'hourly' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Hourly</button>
                    </div>
                </div>
                <div class="flex gap-2">
                    <select wire:model="category_currency" class="input-glass !w-24 shrink-0">
                        @foreach (\App\Support\Currency::options() as $code => $symbol)
                            <option value="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                    @if ($category_pricing_mode === 'fixed')
                        <x-text-input wire:model="category_estimated_amount" type="number" class="mt-0" placeholder="Amount" />
                    @else
                        <x-text-input wire:model="category_estimated_hours" type="number" step="0.25" class="mt-0" placeholder="Hours" />
                        <x-text-input wire:model="category_estimated_rate" type="number" class="mt-0" placeholder="Rate/hr" />
                    @endif
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
