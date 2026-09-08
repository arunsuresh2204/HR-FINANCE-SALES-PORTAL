<div>
    <x-page-header title="Leads Pipeline" subtitle="Log cold-outreach contacts and track responses.">
        <x-slot:actions>
            @if (auth()->user()->isSalesExec())
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
                <x-text-input wire:model="source" type="text" class="mt-0" placeholder="Source (Upwork, FK...)" />
                <x-input-error :messages="$errors->get('source')" class="mt-1" />
            </div>
            <div class="md:col-span-5">
                <x-text-input wire:model="contact_link" type="text" class="mt-0" placeholder="Contact (LinkedIn URL, email, phone...)" />
            </div>
            <div class="md:col-span-1">
                <button class="btn-glass-primary w-full justify-center"><x-icon name="plus" class="h-4 w-4" /> Add Lead</button>
            </div>
        </form>
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
                        <th>Date</th>
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
                        <tr wire:key="lead-row-{{ $lead->id }}">
                            <td class="whitespace-nowrap text-white/60">{{ $lead->contacted_date?->format('M j') ?? $lead->created_at->format('M j') }}</td>
                            <td class="max-w-[10rem]">
                                <p class="truncate font-medium text-white">{{ $lead->client_name }}</p>
                                @if ($lead->company_name)
                                    <p class="truncate text-xs text-white/40">{{ $lead->company_name }}</p>
                                @endif
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
</div>
