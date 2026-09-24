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
            @if ($isManager)
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-white/35">Currency</p>
                    <p class="mt-0.5 font-semibold text-white">{{ $project->currency }}</p>
                </div>
            @endif
        </div>
    </div>

    @if ($project->needsEstimate() && $isManager)
        <div class="glass-card mt-4 border-gold-400/25">
            <p class="text-sm font-semibold text-white">This project is awaiting a cost estimate.</p>
            <p class="mt-1 text-xs text-white/45">Add at least one category to move it to Active and start creating tasks against it.</p>
            @if ($canManage)
                <button wire:click="openCategoryForm" class="btn-glass-primary mt-3 text-xs"><x-icon name="plus" class="h-4 w-4" /> Add Category</button>
            @endif
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-0">
        <div class="flex gap-6 overflow-x-auto no-scrollbar">
            <button wire:click="setTab('board')" class="tab-btn {{ $tab === 'board' ? 'active' : '' }}">
                Board
                @if ($pendingCount > 0)
                    <span class="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-violet-400 px-1 text-[10px] font-bold text-ink-950">{{ $pendingCount }}</span>
                @endif
            </button>
            <button wire:click="setTab('list')" class="tab-btn {{ $tab === 'list' ? 'active' : '' }}">List</button>
            <button wire:click="setTab('notes')" class="tab-btn {{ $tab === 'notes' ? 'active' : '' }}">Notes</button>
            <button wire:click="setTab('credentials')" class="tab-btn {{ $tab === 'credentials' ? 'active' : '' }}">Credentials</button>
            <button wire:click="setTab('files')" class="tab-btn {{ $tab === 'files' ? 'active' : '' }}">Files</button>
            @if ($isManager)
                <button wire:click="setTab('categories')" class="tab-btn {{ $tab === 'categories' ? 'active' : '' }}">Cost Estimate</button>
            @endif
            @if ($canManageRequests)
                <button wire:click="setTab('requests')" class="tab-btn {{ $tab === 'requests' ? 'active' : '' }}">
                    Requests
                    @if ($openRequestCount > 0)
                        <span class="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-violet-400 px-1 text-[10px] font-bold text-ink-950">{{ $openRequestCount }}</span>
                    @endif
                </button>
            @endif
            <button wire:click="setTab('cancelled')" class="tab-btn {{ $tab === 'cancelled' ? 'active' : '' }}">
                Cancelled
                @if ($cancelledTasks->count() > 0)
                    <span class="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-violet-400 px-1 text-[10px] font-bold text-ink-950">{{ $cancelledTasks->count() }}</span>
                @endif
            </button>
        </div>
        <div class="mb-2 flex flex-wrap gap-2">
            @if ($canManage)
                <button wire:click="openReassignForm" class="btn-glass-secondary text-xs"><x-icon name="link" class="h-4 w-4" /> Reassign</button>
                <button wire:click="openDeveloperForm" class="btn-glass-secondary text-xs"><x-icon name="users" class="h-4 w-4" /> Manage Developers</button>
            @endif
            <button wire:click="openTaskModal" class="btn-glass-primary text-xs"><x-icon name="plus" class="h-4 w-4" /> New Task</button>
        </div>
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
                                    <select x-on:click="event.stopPropagation()" x-on:change="$wire.setTaskStatus({{ $task->id }}, $event.target.value)" class="input-glass !w-auto !py-1 !text-xs shrink-0">
                                        @foreach ($statuses as $sKey => $sLabel)
                                            <option value="{{ $sKey }}" @selected($task->status === $sKey) @disabled($task->pending_approval && ! in_array($sKey, ['backlog', 'todo'], true))>{{ $sLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                    @if ($task->cancelled)
                                        <span class="pill border-rose-400/20 bg-rose-400/15 text-rose-300">Cancelled</span>
                                    @endif
                                    @php($urgencyClasses = match ($task->urgency) {
                                        'urgent' => 'border-rose-400/30 bg-rose-400/15 text-rose-300',
                                        'high' => 'border-amber-400/30 bg-amber-400/15 text-amber-300',
                                        'low' => 'border-white/15 bg-white/5 text-white/40',
                                        default => 'border-sky-400/20 bg-sky-400/15 text-sky-300',
                                    })
                                    <span class="pill {{ $urgencyClasses }}">{{ \App\Models\Task::URGENCIES[$task->urgency] ?? 'Medium' }}</span>
                                    @if ($task->category)
                                        <span class="pill border-gold-400/20 bg-gold-400/10 text-gold-300">{{ $task->category->name }}</span>
                                    @elseif (! $task->isPriced())
                                        <span class="pill border-white/15 bg-white/5 text-white/40">non-billable</span>
                                    @endif
                                    @if ($task->visibility === 'private')
                                        <span class="pill border-white/15 bg-white/5 text-white/40">Private</span>
                                    @endif
                                    @if ($task->pending_approval)
                                        <span class="pill border-violet-400/20 bg-violet-400/15 text-violet-300">Pending Approval</span>
                                    @endif
                                    @if ($isManager && $task->amount > 0)
                                        <span class="pill border-white/15 bg-white/10 text-white/70">{{ \App\Support\Currency::format($task->amount, $task->currency) }}</span>
                                    @endif
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs text-white/45">
                                    <span class="truncate">{{ $task->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</span>
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
                <thead><tr><th>Task</th><th>Category</th><th>Amount</th><th>Assignees</th><th>Urgency</th><th>Status</th><th>Due</th></tr></thead>
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
                                @elseif ($task->amount > 0) {{ \App\Support\Currency::format($task->amount, $task->currency) }}
                                @elseif ($task->pending_approval) Pending
                                @else &mdash;
                                @endif
                            </td>
                            <td class="text-white/60">{{ $task->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</td>
                            <td>
                                @php($urgencyClasses = match ($task->urgency) {
                                    'urgent' => 'border-rose-400/30 bg-rose-400/15 text-rose-300',
                                    'high' => 'border-amber-400/30 bg-amber-400/15 text-amber-300',
                                    'low' => 'border-white/15 bg-white/5 text-white/40',
                                    default => 'border-sky-400/20 bg-sky-400/15 text-sky-300',
                                })
                                <span class="pill {{ $urgencyClasses }}">{{ \App\Models\Task::URGENCIES[$task->urgency] ?? 'Medium' }}</span>
                            </td>
                            <td><x-status-pill :status="$task->status" /></td>
                            <td class="text-white/60">{{ $task->end_date?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-white/40">No tasks yet — click New Task to add the first one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- ================= NOTES ================= --}}
    @if ($tab === 'notes')
        <div class="mt-4 space-y-4">
            <form wire:submit="saveNote" class="glass-card space-y-2">
                <textarea wire:model="note_body" rows="2" class="input-glass" placeholder="Add a note for the team&hellip;"></textarea>
                <x-input-error :messages="$errors->get('note_body')" class="mt-1" />
                <div class="flex justify-end">
                    <x-primary-button>Add Note</x-primary-button>
                </div>
            </form>
            <div class="space-y-2">
                @forelse ($notes as $note)
                    <div class="glass-inset flex items-start justify-between gap-3 p-3">
                        <div class="min-w-0">
                            <p class="text-sm text-white/80">{{ $note->body }}</p>
                            <p class="mt-1 text-[11px] text-white/35">{{ $note->author->name }} &middot; {{ $note->created_at->diffForHumans() }}</p>
                        </div>
                        @if ($isManager || auth()->user()->isTeamLead() || $note->user_id === auth()->id())
                            <button wire:click="deleteNote({{ $note->id }})" wire:confirm="Remove this note?" class="shrink-0 text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                        @endif
                    </div>
                @empty
                    <p class="glass-card py-8 text-center text-sm text-white/30">No notes yet.</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ================= CREDENTIALS ================= --}}
    @if ($tab === 'credentials')
        <div class="mt-4 space-y-3">
            @if ($canManageCredentials)
                <div class="flex justify-end">
                    <button wire:click="openCredentialForm" class="btn-glass-secondary text-xs"><x-icon name="plus" class="h-4 w-4" /> Add Credential</button>
                </div>
            @endif
            @forelse ($credentials as $credential)
                <div class="glass-card">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-white">{{ $credential->label }}</p>
                        @if ($canManageCredentials)
                            <div class="flex shrink-0 items-center gap-3 text-xs">
                                <button wire:click="editCredential({{ $credential->id }})" class="font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                                <button wire:click="deleteCredential({{ $credential->id }})" wire:confirm="Remove this credential?" class="font-semibold text-white/40 hover:text-rose-300">Delete</button>
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                        @if ($credential->username)
                            <div><span class="text-white/35">Username:</span> <span class="text-white/70">{{ $credential->username }}</span></div>
                        @endif
                        <div class="flex items-center gap-2">
                            <span class="text-white/35">Secret:</span>
                            <span class="font-mono text-white/70">{{ in_array($credential->id, $revealedCredentialIds) ? $credential->secret : str_repeat('•', 10) }}</span>
                            <button wire:click="toggleRevealCredential({{ $credential->id }})" class="text-[11px] font-semibold text-gold-300 hover:text-gold-200">{{ in_array($credential->id, $revealedCredentialIds) ? 'Hide' : 'Reveal' }}</button>
                        </div>
                    </div>
                    @if ($credential->notes)
                        <p class="mt-2 text-xs text-white/45">{{ $credential->notes }}</p>
                    @endif
                </div>
            @empty
                <p class="glass-card py-8 text-center text-sm text-white/30">No credentials saved yet.</p>
            @endforelse
        </div>
    @endif

    {{-- ================= FILES ================= --}}
    @if ($tab === 'files')
        <div class="mt-4 space-y-4">
            <form wire:submit="uploadFiles" class="glass-card space-y-2">
                <input wire:model="project_files" id="project_files" type="file" multiple class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                <div wire:loading wire:target="project_files" class="text-xs text-white/40">Uploading&hellip;</div>
                <x-input-error :messages="$errors->get('project_files')" class="mt-1" />
                <x-input-error :messages="$errors->get('project_files.*')" class="mt-1" />
                <div class="flex justify-end">
                    <x-primary-button>Upload</x-primary-button>
                </div>
            </form>
            <div class="space-y-2">
                @forelse ($files as $file)
                    <div class="glass-inset flex items-center justify-between gap-3 p-3">
                        <a href="{{ $file->url() }}" target="_blank" class="min-w-0 truncate text-sm font-semibold text-gold-300 hover:text-gold-200">{{ $file->original_name }}</a>
                        <div class="flex shrink-0 items-center gap-3">
                            <p class="text-[11px] text-white/35">{{ $file->uploader->name }} &middot; {{ $file->created_at->diffForHumans() }}</p>
                            @if ($isManager || auth()->user()->isTeamLead() || $file->uploaded_by === auth()->id())
                                <button wire:click="deleteFile({{ $file->id }})" wire:confirm="Remove this file?" class="text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="glass-card py-8 text-center text-sm text-white/30">No files uploaded yet.</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ================= COST ESTIMATE / CATEGORIES ================= --}}
    @if ($tab === 'categories' && $isManager)
        <div class="mt-4 space-y-5">
            @if ($canManage)
                <div class="flex justify-end">
                    <button wire:click="openCategoryForm" class="btn-glass-secondary text-xs"><x-icon name="plus" class="h-4 w-4" /> Add Category</button>
                </div>
            @endif

            @if ($isManager)
                <div class="glass-inset flex flex-wrap items-center gap-3 p-3">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-white/40">Date range</span>
                    <select wire:change="applyCostPreset($event.target.value)" class="input-glass !w-auto !py-1.5 !text-xs">
                        <option value="custom" @selected($cost_filter_preset === 'custom')>Custom range</option>
                        <option value="all" @selected($cost_filter_preset === 'all')>All time</option>
                        <option value="month" @selected($cost_filter_preset === 'month')>This month</option>
                        <option value="last30" @selected($cost_filter_preset === 'last30')>Last 30 days</option>
                    </select>
                    <input type="date" wire:model="cost_filter_from" wire:change="setCostDateFilter" class="input-glass !w-auto !py-1.5 !text-xs">
                    <span class="text-xs text-white/30">to</span>
                    <input type="date" wire:model="cost_filter_to" wire:change="setCostDateFilter" class="input-glass !w-auto !py-1.5 !text-xs">
                    <span class="ml-auto text-xs text-white/45">
                        @if ($costFilterActive)
                            Showing {{ $shownPricedCount }} of {{ $pricedCount }} priced task{{ $pricedCount === 1 ? '' : 's' }} in range
                        @else
                            {{ $pricedCount }} priced task{{ $pricedCount === 1 ? '' : 's' }} total
                        @endif
                    </span>
                </div>
            @endif

            @forelse ($categories as $category)
                <div class="glass-card">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                        <p class="font-bold text-white">{{ $category->name }}</p>
                        @if ($isManager)
                            <span class="text-sm font-bold text-gold-300">
                                @forelse (($categorySubtotals[$category->id] ?? []) as $currencyCode => $sum)
                                    {{ !$loop->first ? ' + ' : '' }}{{ \App\Support\Currency::format($sum, $currencyCode) }}
                                @empty
                                    {{ \App\Support\Currency::format(0, $project->currency) }}
                                @endforelse
                            </span>
                        @endif
                    </div>
                    <div class="space-y-2">
                        @forelse ($category->tasks as $task)
                            <div class="glass-inset flex items-center justify-between gap-3 p-2 text-sm">
                                <span class="min-w-0 flex-1 truncate text-white/80">{{ $task->title }}</span>
                                @if ($isManager)
                                    @php($activeBr = $task->activeBillingRequest())
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="text-white/50">{{ \App\Support\Currency::format($task->effectiveAmount(), $task->currency) }}</span>
                                        @unless ($activeBr && $activeBr->status === 'invoiced')
                                            <button wire:click="openEditAmountForm({{ $task->id }})" class="text-[11px] font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                                        @endunless
                                        @if ($task->cancelled)
                                            <x-status-pill status="cancelled" />
                                        @elseif ($activeBr && $activeBr->status === 'invoiced')
                                            <x-status-pill status="invoiced" />
                                        @elseif ($activeBr && $activeBr->status === 'pending')
                                            <x-status-pill status="billed" />
                                        @elseif ($task->ready_to_bill)
                                            <button wire:click="toggleReadyToBill({{ $task->id }})" class="pill border-violet-400/20 bg-violet-400/15 text-violet-300 hover:bg-violet-400/25" title="Click to un-flag">Ready to Bill</button>
                                        @elseif ($task->status === 'done' && $task->isPriced())
                                            <button wire:click="toggleReadyToBill({{ $task->id }})" class="btn-glass-primary !px-2.5 !py-1 text-[11px]">Mark Ready to Bill</button>
                                        @else
                                            <x-status-pill :status="$task->status" />
                                        @endif
                                    </div>
                                @else
                                    <span class="text-white/50">—</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-white/30">No priced tasks in this range</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="glass-card py-8 text-center text-sm text-white/30">No categories yet{{ $canManage ? ' — click Add Category to start pricing this project.' : '.' }}</p>
            @endforelse
        </div>
    @endif

    {{-- ================= REQUESTS FROM SALES ================= --}}
    @if ($tab === 'requests' && $canManageRequests)
        <div class="mt-4 space-y-2">
            @forelse ($requests as $request)
                <button wire:click="openRequestDetail({{ $request->id }})" class="glass-card block w-full text-left hover:bg-white/5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold text-white">{{ $request->title }}</p>
                        <x-status-pill :status="$request->status" />
                    </div>
                    <p class="mt-1 text-xs text-white/40">From {{ $request->creator->name }} &middot; {{ $request->created_at->format('M j, Y') }}
                        @if ($request->comments->isNotEmpty())
                            &middot; {{ $request->comments->count() }} {{ Str::plural('reply', $request->comments->count()) }}
                        @endif
                    </p>
                </button>
            @empty
                <p class="glass-card py-8 text-center text-sm text-white/30">No requests from Sales yet.</p>
            @endforelse
        </div>
    @endif

    {{-- ================= CANCELLED TASKS ================= --}}
    @if ($tab === 'cancelled')
        <div class="mt-4 space-y-3">
            @forelse ($cancelledTasks as $task)
                <button wire:click="openTaskDetail({{ $task->id }})" wire:key="cancelled-task-{{ $task->id }}" class="glass-card block w-full text-left hover:bg-white/5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-white">{{ $task->title }}</p>
                            <span class="pill border-rose-400/20 bg-rose-400/15 text-rose-300">Cancelled</span>
                        </div>
                        <span class="shrink-0 text-[11px] text-white/35">was: {{ $statuses[$task->status] ?? $task->status }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        @php($urgencyClasses = match ($task->urgency) {
                            'urgent' => 'border-rose-400/30 bg-rose-400/15 text-rose-300',
                            'high' => 'border-amber-400/30 bg-amber-400/15 text-amber-300',
                            'low' => 'border-white/15 bg-white/5 text-white/40',
                            default => 'border-sky-400/20 bg-sky-400/15 text-sky-300',
                        })
                        <span class="pill {{ $urgencyClasses }}">{{ \App\Models\Task::URGENCIES[$task->urgency] ?? 'Medium' }}</span>
                        @if ($task->category)
                            <span class="pill border-gold-400/20 bg-gold-400/10 text-gold-300">{{ $task->category->name }}</span>
                        @elseif (! $task->isPriced())
                            <span class="pill border-white/15 bg-white/5 text-white/40">non-billable</span>
                        @endif
                        @if ($isManager && $task->amount > 0)
                            <span class="pill border-white/15 bg-white/10 text-white/70">{{ \App\Support\Currency::format($task->amount, $task->currency) }}</span>
                        @endif
                        <span class="pill border-white/15 bg-white/5 text-white/40">{{ $task->assignees->pluck('name')->join(', ') ?: 'Unassigned' }}</span>
                    </div>
                    <div class="mt-3 space-y-0.5 border-t border-white/10 pt-2">
                        @if ($task->cancel_reason)
                            <p class="text-xs text-white/50"><span class="font-semibold text-white/75">Reason:</span> {{ $task->cancel_reason }}</p>
                        @endif
                        <p class="text-[11px] text-white/30">Cancelled by {{ $task->canceller->name ?? 'Unknown' }} &middot; {{ $task->cancelled_at?->format('M j, Y') ?? '—' }}</p>
                    </div>
                </button>
            @empty
                <p class="glass-card py-8 text-center text-sm text-white/30">No cancelled tasks on this project.</p>
            @endforelse
        </div>
    @endif

    {{-- ================= NEW / EDIT TASK MODAL ================= --}}
    <x-modal-glass wire-model="showTaskModal" :title="$editingTaskId ? 'Edit Task' : 'New Task — '.$project->name" max-width="xl">
        <form wire:submit="saveTask" class="space-y-4">
            @if ($convertingRequestId)
                <p class="rounded-lg border border-violet-400/25 bg-violet-400/10 px-3 py-2 text-xs font-semibold text-violet-200">Converting a Sales request into this task — review and price it below.</p>
            @endif
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
                    <x-input-label for="task_urgency" value="Urgency" />
                    <select wire:model="task_urgency" id="task_urgency" class="input-glass">
                        @foreach (\App\Models\Task::URGENCIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <x-input-label value="Assignees" />
                <div class="mt-1 max-h-40 space-y-1.5 overflow-y-auto rounded-xl border border-white/10 bg-white/5 p-2">
                    @forelse ($assignableUsers as $user)
                        <label class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-white/80 hover:bg-white/5">
                            <input type="checkbox" wire:model="task_assignee_ids" value="{{ $user->id }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                            {{ $user->name }}{{ $user->id === auth()->id() ? ' (you)' : '' }}
                        </label>
                    @empty
                        <p class="px-2 py-1.5 text-xs text-white/40">Nobody assignable on this project yet.</p>
                    @endforelse
                </div>
                <x-input-error :messages="$errors->get('task_assignee_ids')" class="mt-1" />
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
                        <span class="input-glass !w-24 shrink-0 !cursor-default text-center text-white/50">{{ $project->currency }}</span>
                        @if ($task_pricing_mode === 'fixed')
                            <x-text-input wire:model="task_amount" type="number" min="0" step="0.01" class="mt-0 min-w-0 flex-1" placeholder="e.g. 15000" />
                        @else
                            <x-text-input wire:model="task_hours" type="number" min="0" step="0.25" class="mt-0 min-w-0 flex-1" placeholder="Hours" />
                            <x-text-input wire:model="task_rate" type="number" min="0" step="0.01" class="mt-0 min-w-0 flex-1" placeholder="Rate/hr" />
                        @endif
                    </div>
                    <x-input-error :messages="$errors->get('task_amount')" class="mt-1" />
                    <x-input-error :messages="$errors->get('task_hours')" class="mt-1" />
                </div>
            @elseif (! $editingTaskId)
                <p class="text-xs text-violet-300/80">This task will need your manager's review, please contact manager for approval.</p>
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
                @elseif (! $task->isPriced())
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
                        @elseif ($task->amount > 0)
                            {{ \App\Support\Currency::format($task->amount, $task->currency) }}
                            @if ($task->hours !== null && $task->rate !== null)
                                ({{ $task->hours }}h &times; {{ \App\Support\Currency::format($task->rate, $task->currency) }}/hr)
                            @endif
                        @else &mdash;
                        @endif
                    </p>
                </div>
                <div>
                    <p class="label-glass !mb-1">Urgency</p>
                    <select x-on:change="$wire.setTaskUrgency({{ $task->id }}, $event.target.value)" class="input-glass !w-full !py-1 !text-xs">
                        @foreach (\App\Models\Task::URGENCIES as $uKey => $uLabel)
                            <option value="{{ $uKey }}" @selected($task->urgency === $uKey)>{{ $uLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <p class="label-glass !mb-1">Visibility</p>
                    <select x-on:change="$wire.setTaskVisibility({{ $task->id }}, $event.target.value)" class="input-glass !w-full !py-1 !text-xs">
                        <option value="public" @selected($task->visibility !== 'private')>Public</option>
                        <option value="private" @selected($task->visibility === 'private')>Private</option>
                    </select>
                </div>
                <div>
                    <p class="label-glass !mb-1">Dates</p>
                    <p class="text-white/80">{{ $task->start_date?->format('M j') }} &ndash; {{ $task->end_date?->format('M j') ?? '—' }}</p>
                </div>
            </div>

            <div class="mt-4 border-t border-white/10 pt-4">
                <p class="label-glass">Assignees <span class="normal-case text-white/30">&middot; click to toggle</span></p>
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($assignableUsers as $user)
                        @php($isAssigned = $task->assignees->contains('id', $user->id))
                        <button type="button" wire:click="toggleTaskAssignee({{ $task->id }}, {{ $user->id }})" class="pill {{ $isAssigned ? 'border-gold-400/30 bg-gold-400/15 text-gold-300' : 'border-white/15 bg-white/5 text-white/40 hover:text-white/70' }}">
                            {{ $user->name }}{{ $user->id === auth()->id() ? ' (you)' : '' }}
                        </button>
                    @empty
                        <p class="text-xs text-white/40">Nobody assignable on this project yet.</p>
                    @endforelse
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
                <select x-on:change="$wire.setTaskStatus({{ $task->id }}, $event.target.value)" class="input-glass !w-auto !py-1 !text-xs">
                    @foreach ($statuses as $sKey => $sLabel)
                        <option value="{{ $sKey }}" @selected($task->status === $sKey) @disabled($task->pending_approval && ! in_array($sKey, ['backlog', 'todo'], true))>{{ $sLabel }}</option>
                    @endforeach
                </select>
                @if ($task->pending_approval)
                    <p class="mt-1 text-[11px] text-white/40">Awaiting manager approval — can only move between Backlog and To Do until priced.</p>
                @endif
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
                        <span class="input-glass !w-24 shrink-0 !cursor-default text-center text-white/50">{{ $project->currency }}</span>
                        @if ($approve_mode === 'fixed')
                            <x-text-input wire:model="approve_amount" type="number" min="0" step="0.01" class="mt-0 min-w-0 flex-1" placeholder="e.g. 15000" />
                        @else
                            <x-text-input wire:model="approve_hours" type="number" min="0" step="0.25" class="mt-0 min-w-0 flex-1" placeholder="Hours" />
                            <x-text-input wire:model="approve_rate" type="number" min="0" step="0.01" class="mt-0 min-w-0 flex-1" placeholder="Rate/hr" />
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
                    @if ($task->ready_to_bill)
                        @if ($task->activeBillingRequest())
                            <span class="text-xs text-white/40">Picked up by Sales — {{ $task->activeBillingRequest()->status === 'invoiced' ? 'invoiced' : 'awaiting invoicing' }}.</span>
                        @else
                            <button wire:click="toggleReadyToBill({{ $task->id }})" class="btn-glass-secondary text-xs"><x-icon name="cash" class="h-4 w-4" /> Un-flag Ready to Bill</button>
                        @endif
                    @else
                        <button wire:click="toggleReadyToBill({{ $task->id }})" class="btn-glass-secondary text-xs"><x-icon name="cash" class="h-4 w-4" /> Mark Ready to Bill</button>
                    @endif
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


    {{-- ================= EDIT TASK AMOUNT MODAL ================= --}}
    <x-modal-glass wire-model="showEditAmountForm" title="Edit Task Amount" max-width="sm">
        <form wire:submit="saveTaskAmount" class="space-y-4">
            <p class="text-sm text-white/50">Use this to apply a client discount or correct pricing before this task is invoiced.</p>
            <div class="inline-flex rounded-lg border border-white/10 bg-white/5 p-0.5 text-[11px] font-semibold">
                <button type="button" wire:click="$set('edit_amount_mode', 'fixed')" class="rounded-md px-2.5 py-1 {{ $edit_amount_mode === 'fixed' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Fixed amount</button>
                <button type="button" wire:click="$set('edit_amount_mode', 'hourly')" class="rounded-md px-2.5 py-1 {{ $edit_amount_mode === 'hourly' ? 'bg-gold-400/[0.16] text-gold-300' : 'text-white/55' }}">Hourly</button>
            </div>
            <div class="flex gap-2">
                <span class="input-glass !w-24 shrink-0 !cursor-default text-center text-white/50">{{ $project->currency }}</span>
                @if ($edit_amount_mode === 'fixed')
                    <x-text-input wire:model="edit_amount_value" type="number" min="0" step="0.01" class="mt-0 min-w-0 flex-1" placeholder="e.g. 15000" />
                @else
                    <x-text-input wire:model="edit_amount_hours" type="number" min="0" step="0.25" class="mt-0 min-w-0 flex-1" placeholder="Hours" />
                    <x-text-input wire:model="edit_amount_rate" type="number" min="0" step="0.01" class="mt-0 min-w-0 flex-1" placeholder="Rate/hr" />
                @endif
            </div>
            <x-input-error :messages="$errors->get('edit_amount_value')" class="mt-1" />
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    {{-- ================= CATEGORY MODAL ================= --}}
    <x-modal-glass wire-model="showCategoryForm" title="Add Category" max-width="md">
        <form wire:submit="saveCategory" class="space-y-4">
            <div>
                <x-input-label for="category_name" value="Category name" />
                <x-text-input wire:model="category_name" id="category_name" type="text" class="mt-0" placeholder="e.g. Loyalty points engine" />
                <x-input-error :messages="$errors->get('category_name')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    {{-- ================= REASSIGN MODAL ================= --}}
    <x-modal-glass wire-model="showReassignForm" title="Reassign Project" max-width="sm">
        <form wire:submit="saveReassign" class="space-y-4">
            <div>
                <x-input-label for="reassign_to" value="Hand off to" />
                <select wire:model="reassign_to" id="reassign_to" class="input-glass">
                    <option value="">— Select a manager, team leader, or owner —</option>
                    @foreach ($reassignableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}{{ $user->designation ? ' ('.$user->designation.')' : '' }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-white/35">Once reassigned, this project moves to their My Projects list and leaves yours.</p>
                <x-input-error :messages="$errors->get('reassign_to')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Reassign</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    {{-- ================= MANAGE DEVELOPERS MODAL ================= --}}
    <x-modal-glass wire-model="showDeveloperForm" title="Manage Developers" max-width="sm">
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

    {{-- ================= REQUEST DETAIL MODAL ================= --}}
    <x-modal-glass wire-model="showRequestDetail" :title="$viewingRequest->title ?? 'Request'" max-width="lg">
        @if ($viewingRequest)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-white/40">From {{ $viewingRequest->creator->name }} &middot; {{ $viewingRequest->created_at->format('M j, Y') }}</p>
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

                @if ($viewingRequest->status === 'open')
                    <button wire:click="convertRequest({{ $viewingRequest->id }})" class="btn-glass-primary text-xs"><x-icon name="check" class="h-4 w-4" /> Convert to Task</button>
                @else
                    <div class="rounded-lg border border-emerald-400/20 bg-emerald-400/5 p-3 text-xs text-emerald-200">
                        Converted to a task on {{ $viewingRequest->converted_at?->format('M j, Y') }}.
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

    {{-- ================= CREDENTIAL FORM MODAL ================= --}}
    <x-modal-glass wire-model="showCredentialForm" :title="$editingCredentialId ? 'Edit Credential' : 'Add Credential'" max-width="md">
        <form wire:submit="saveCredential" class="space-y-4">
            <div>
                <x-input-label for="credential_label" value="Label" />
                <x-text-input wire:model="credential_label" id="credential_label" type="text" class="mt-0" placeholder="e.g. Staging server, Google Analytics" />
                <x-input-error :messages="$errors->get('credential_label')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="credential_username" value="Username (optional)" />
                <x-text-input wire:model="credential_username" id="credential_username" type="text" class="mt-0" />
            </div>
            <div>
                <x-input-label for="credential_secret" value="Password / Key" />
                <textarea wire:model="credential_secret" id="credential_secret" rows="2" class="input-glass font-mono"></textarea>
                <x-input-error :messages="$errors->get('credential_secret')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="credential_notes" value="Notes (optional)" />
                <textarea wire:model="credential_notes" id="credential_notes" rows="2" class="input-glass"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
