<div>
    <x-page-header title="Leads Pipeline" subtitle="Track every lead from first contact to close.">
        <x-slot:actions>
            <a href="{{ route('sales.clients') }}" wire:navigate class="btn-glass-secondary"><x-icon name="briefcase" class="h-4 w-4" /> Clients</a>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> New Lead</button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-5 flex flex-wrap items-center gap-3">
        <div class="relative max-w-xs flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/30" />
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search leads..." class="input-glass pl-9">
        </div>
        @if ($salesPeople->isNotEmpty())
            <select wire:model.live="salesPersonFilter" class="input-glass w-48">
                <option value="">All Sales People</option>
                @foreach ($salesPeople as $sp)
                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($columns as $status => $leads)
            <div class="kanban-column">
                <div class="flex items-center justify-between px-1">
                    <p class="text-sm font-bold text-white"><x-status-pill :status="$status" /></p>
                    <span class="text-xs font-semibold text-white/40">{{ $leads->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach ($leads as $lead)
                        <a href="{{ route('sales.leads.show', $lead) }}" wire:navigate class="kanban-card block">
                            <p class="font-semibold text-white">{{ $lead->client_name }}</p>
                            @if ($lead->company_name)
                                <p class="text-xs text-white/45">{{ $lead->company_name }}</p>
                            @endif
                            <p class="mt-2 line-clamp-2 text-xs text-white/50">{{ $lead->requirement }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="badge-glass">{{ ucwords(str_replace('_', ' ', $lead->service_type)) }}</span>
                                @if ($lead->budget)
                                    <span class="text-xs font-bold text-gold-300">${{ number_format($lead->budget) }}</span>
                                @endif
                            </div>
                            @if ($lead->follow_up_date)
                                <p class="mt-2 text-[11px] text-white/30">Follow up {{ $lead->follow_up_date->format('M j') }}</p>
                            @endif
                        </a>
                    @endforeach
                    @if ($leads->isEmpty())
                        <p class="px-1 text-xs text-white/25">No leads here.</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <x-modal-glass wire-model="showForm" title="New Lead" max-width="xl">
        <form wire:submit="createLead" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="client_name" value="Client / Contact Name" />
                    <x-text-input wire:model="client_name" id="client_name" type="text" class="mt-0" />
                    <x-input-error :messages="$errors->get('client_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="company_name" value="Company Name" />
                    <x-text-input wire:model="company_name" id="company_name" type="text" class="mt-0" />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-input-label for="country" value="Country" />
                    <x-text-input wire:model="country" id="country" type="text" class="mt-0" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input wire:model="email" id="email" type="email" class="mt-0" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone / WhatsApp" />
                    <x-text-input wire:model="phone" id="phone" type="text" class="mt-0" />
                </div>
            </div>
            <div>
                <x-input-label for="requirement" value="Requirement" />
                <textarea wire:model="requirement" id="requirement" rows="3" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('requirement')" class="mt-1" />
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-input-label for="service_type" value="Service Type" />
                    <select wire:model="service_type" id="service_type" class="input-glass">
                        <option value="web">Web</option>
                        <option value="mobile">Mobile</option>
                        <option value="social_media">Social Media</option>
                        <option value="pet_product">Pet Product</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="source" value="Source" />
                    <x-text-input wire:model="source" id="source" type="text" class="mt-0" placeholder="Upwork, Referral..." />
                    <x-input-error :messages="$errors->get('source')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="follow_up_date" value="Follow-up Date" />
                    <x-text-input wire:model="follow_up_date" id="follow_up_date" type="date" class="mt-0" />
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Create Lead</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
