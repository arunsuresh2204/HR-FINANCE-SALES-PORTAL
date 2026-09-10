<div>
    <x-page-header title="Leads Pipeline" subtitle="Log cold-outreach contacts and track responses.">
        <x-slot:actions>
            @if (auth()->user()->can('access_sales_clients'))
                <a href="{{ route('sales.clients') }}" wire:navigate class="btn-glass-secondary"><x-icon name="briefcase" class="h-4 w-4" /> Clients</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="glass-card mb-6">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-white/40">Add a Lead</p>
        <form wire:submit="createLead" class="grid grid-cols-1 gap-2 md:grid-cols-6">
            <div class="md:col-span-1">
                <x-text-input wire:model="client_name" type="text" class="mt-0" placeholder="Client name" />
                <x-input-error :messages="$errors->get('client_name')" class="mt-1" />
            </div>
            <div class="md:col-span-1">
                <x-text-input wire:model="country" type="text" class="mt-0" placeholder="Country" />
            </div>
            <div class="md:col-span-2">
                <x-text-input wire:model="requirement" type="text" class="mt-0" placeholder="Requirement / what they need" />
                <x-input-error :messages="$errors->get('requirement')" class="mt-1" />
            </div>
            <div class="md:col-span-1">
                <input wire:model="service_type" list="tech-suggestions" type="text" class="input-glass" placeholder="Technology">
                <datalist id="tech-suggestions">
                    <option value="WordPress">
                    <option value="Web Development">
                    <option value="Mobile App">
                    <option value="Digital Marketing">
                    <option value="Social Media">
                    <option value="SEO">
                    <option value="E-commerce">
                    <option value="Branding">
                    <option value="Other">
                </datalist>
                <x-input-error :messages="$errors->get('service_type')" class="mt-1" />
            </div>
            <div class="md:col-span-1">
                <select wire:model="source" class="input-glass mt-0">
                    <option value="">Source...</option>
                    @foreach ($leadSources as $leadSource)
                        <option value="{{ $leadSource->name }}">{{ $leadSource->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('source')" class="mt-1" />
            </div>
            <div class="md:col-span-4">
                <x-text-input wire:model="contact_link" type="text" class="mt-0" placeholder="Contact (LinkedIn URL, email, phone...)" />
            </div>
            <div class="md:col-span-1">
                <x-text-input wire:model="contacted_date" type="date" class="mt-0" title="Date entered" />
                <x-input-error :messages="$errors->get('contacted_date')" class="mt-1" />
            </div>
            <div class="md:col-span-1">
                <button class="btn-glass-primary w-full justify-center"><x-icon name="plus" class="h-4 w-4" /> Add Lead</button>
            </div>
        </form>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="inline-flex rounded-xl border border-white/10 bg-white/5 p-1">
            <button type="button" wire:click="setRange('day')" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $range === 'day' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}">Day</button>
            <button type="button" wire:click="setRange('week')" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $range === 'week' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}">Week</button>
            <button type="button" wire:click="setRange('month')" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $range === 'month' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}">Month</button>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" wire:click="prevPeriod" class="glass rounded-lg p-1.5 text-white/50 hover:text-white"><x-icon name="arrow-right" class="h-3.5 w-3.5 rotate-180" /></button>
            <span class="min-w-[9rem] text-center text-sm font-semibold text-white">{{ $rangeLabel }}</span>
            <button type="button" wire:click="nextPeriod" class="glass rounded-lg p-1.5 text-white/50 hover:text-white"><x-icon name="arrow-right" class="h-3.5 w-3.5" /></button>
        </div>
        <input wire:model.live="monthPicker" type="month" class="input-glass w-40" title="Jump to a month">
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative max-w-xs flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/30" />
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search leads..." class="input-glass pl-9">
        </div>
        <select wire:model.live="statusFilter" class="input-glass w-44">
            <option value="">All Statuses</option>
            @foreach ($statusOptions as $s)
                <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
        @if ($owners->isNotEmpty())
            <select wire:model.live="ownerFilter" class="input-glass w-48">
                <option value="">Everyone</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Date Entered</th>
                        <th>Client</th>
                        <th>Country</th>
                        <th>Requirement</th>
                        <th>Technology</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Comment</th>
                        <th>Contact</th>
                        @if ($owners->isNotEmpty())
                            <th>Owner</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr wire:key="lead-row-{{ $lead->id }}" onclick="if (!event.target.closest('a, button, select, input')) { Livewire.navigate('{{ route('sales.leads.show', $lead) }}') }" class="cursor-pointer">
                            <td class="whitespace-nowrap text-white/60">{{ $lead->contacted_date?->format('M j, Y') ?? $lead->created_at->format('M j, Y') }}</td>
                            <td class="max-w-[10rem]">
                                <button type="button" wire:click="viewRequirement({{ $lead->id }})" class="block text-left">
                                    <p class="truncate font-medium text-white hover:text-gold-300">{{ $lead->client_name }}</p>
                                    @if ($lead->company_name)
                                        <p class="truncate text-xs text-white/40">{{ $lead->company_name }}</p>
                                    @endif
                                </button>
                            </td>
                            <td class="whitespace-nowrap text-white/60">{{ $lead->country ?? '—' }}</td>
                            <td class="max-w-xs">
                                <p class="line-clamp-2 text-white/70">{{ $lead->requirement }}</p>
                            </td>
                            <td class="whitespace-nowrap"><span class="badge-glass">{{ $lead->service_type }}</span></td>
                            <td class="whitespace-nowrap text-white/60">{{ $lead->source }}</td>
                            <td class="min-w-[9rem]">
                                <select wire:model.live="statuses.{{ $lead->id }}" class="input-glass !py-1.5 text-xs">
                                    @foreach ($statusOptions as $s)
                                        <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="min-w-[10rem]">
                                <input wire:model.blur="comments.{{ $lead->id }}" type="text" class="input-glass !py-1.5 text-xs" placeholder="Add comment...">
                            </td>
                            <td class="max-w-[8rem]">
                                @if ($lead->contact_link)
                                    <a href="{{ Str::startsWith($lead->contact_link, 'http') ? $lead->contact_link : '#' }}" target="_blank" class="block max-w-[8rem] truncate text-xs font-semibold text-gold-300 hover:text-gold-200" title="{{ $lead->contact_link }}">{{ Str::limit($lead->contact_link, 22) }}</a>
                                @else
                                    <span class="text-white/25">—</span>
                                @endif
                            </td>
                            @if ($owners->isNotEmpty())
                                <td class="whitespace-nowrap text-white/60">{{ $lead->salesPerson->name }}</td>
                            @endif
                            <td class="text-right"><a href="{{ route('sales.leads.show', $lead) }}" wire:navigate class="text-xs font-semibold text-gold-300 hover:text-gold-200">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="py-8 text-center text-white/40">No leads yet — add your first one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $leads->links() }}</div>
    </div>

    <x-modal-glass wire-model="showRequirementModal" title="{{ $viewingLead?->client_name }}" max-width="lg">
        @if ($viewingLead)
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-xs text-white/40">Country</p><p class="mt-0.5 text-white">{{ $viewingLead->country ?? '—' }}</p></div>
                    <div><p class="text-xs text-white/40">Technology</p><p class="mt-0.5 text-white">{{ $viewingLead->service_type }}</p></div>
                    <div><p class="text-xs text-white/40">Source</p><p class="mt-0.5 text-white">{{ $viewingLead->source }}</p></div>
                    <div><p class="text-xs text-white/40">Status</p><p class="mt-0.5"><x-status-pill :status="$viewingLead->status" /></p></div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Requirement</p>
                    <p class="mt-1 whitespace-pre-line text-sm text-white/75">{{ $viewingLead->requirement }}</p>
                </div>
                @if ($viewingLead->comment)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Comment</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-white/75">{{ $viewingLead->comment }}</p>
                    </div>
                @endif
                <div class="flex justify-end">
                    <a href="{{ route('sales.leads.show', $viewingLead) }}" wire:navigate class="btn-glass-secondary">Open Full Lead</a>
                </div>
            </div>
        @endif
    </x-modal-glass>
</div>
