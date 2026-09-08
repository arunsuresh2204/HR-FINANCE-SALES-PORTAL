<div>
    <x-page-header :title="$lead->client_name" :subtitle="$lead->company_name ?? 'Individual lead'">
        <x-slot:actions>
            <a href="{{ route('sales.leads') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back to Pipeline</a>
            @if ($lead->status === 'won' && ! $client && auth()->user()->isSalesExec())
                <button wire:click="openConvertForm" class="btn-glass-primary"><x-icon name="briefcase" class="h-4 w-4" /> Convert to Client</button>
            @elseif ($client && auth()->user()->isSalesExec())
                <a href="{{ route('sales.clients.show', $client) }}" wire:navigate class="btn-glass-primary"><x-icon name="briefcase" class="h-4 w-4" /> View Client</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Lead Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-white/40">Country</dt><dd class="mt-0.5 text-white">{{ $lead->country ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Technology</dt><dd class="mt-0.5 text-white">{{ $lead->service_type }}</dd></div>
                    <div><dt class="text-white/40">Contacted On</dt><dd class="mt-0.5 text-white">{{ $lead->contacted_date?->format('M j, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Source</dt><dd class="mt-0.5 text-white">{{ $lead->source }}</dd></div>
                    <div><dt class="text-white/40">Email</dt><dd class="mt-0.5 text-white">{{ $lead->email ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Phone</dt><dd class="mt-0.5 text-white">{{ $lead->phone ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Owner</dt><dd class="mt-0.5 text-white">{{ $lead->salesPerson->name }}</dd></div>
                </dl>
                <div class="mt-4 border-t border-white/10 pt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Requirement</p>
                    <p class="mt-1 text-sm text-white/70">{{ $lead->requirement }}</p>
                </div>
            </div>

            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Response Comment &amp; Contact</h2>
                <form wire:submit="saveDetails" class="space-y-4">
                    <div>
                        <x-input-label for="comment" value="Comment (how they responded, notes for follow-up...)" />
                        <textarea wire:model="comment" id="comment" rows="3" class="input-glass"></textarea>
                        <x-input-error :messages="$errors->get('comment')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="contact_link" value="Contact (LinkedIn URL, email, phone...)" />
                        <x-text-input wire:model="contact_link" id="contact_link" type="text" class="mt-0" />
                        <x-input-error :messages="$errors->get('contact_link')" class="mt-1" />
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Activity Log</h2>
                <form wire:submit="addNote" class="mb-4 flex gap-2">
                    <input wire:model="note" type="text" placeholder="Add a note about this lead..." class="input-glass flex-1">
                    <button class="btn-glass-secondary">Add</button>
                </form>
                <x-input-error :messages="$errors->get('note')" class="mb-2" />
                <div class="space-y-3">
                    @forelse ($lead->activities()->with('user')->latest()->get() as $activity)
                        <div class="glass-inset p-3">
                            <p class="text-sm text-white/75">{{ $activity->note }}</p>
                            <p class="mt-1 text-xs text-white/30">{{ $activity->user->name }} &middot; {{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No activity logged yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-white/40">Update Status</p>
                <select wire:model="status" class="input-glass">
                    @foreach (\App\Models\Lead::STATUSES as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>

                @if ($status === 'won')
                    <div class="mt-3">
                        <x-input-label for="budget" value="Deal Value ($)" />
                        <x-text-input wire:model="budget" id="budget" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('budget')" class="mt-1" />
                    </div>
                @endif

                <button wire:click="updateStatus" class="btn-glass-primary mt-4 w-full">Save Status</button>

                @if ($lead->status === 'won' && ! $client && ! auth()->user()->isSalesExec())
                    <p class="mt-3 text-xs text-white/40">Marked as won — a sales team member will convert this to a client.</p>
                @endif
            </div>

            <div class="glass-card text-center">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Current Status</p>
                <div class="mt-2"><x-status-pill :status="$lead->status" /></div>
                @if ($lead->budget)
                    <p class="mt-3 text-2xl font-extrabold text-gold-300">${{ number_format($lead->budget) }}</p>
                @endif
            </div>
        </div>
    </div>

    <x-modal-glass wire-model="showConvertForm" title="Convert to Client" max-width="xl">
        <form wire:submit="convertToClient" class="space-y-4">
            <div>
                <x-input-label for="business_name" value="Business Name" />
                <x-text-input wire:model="business_name" id="business_name" type="text" class="mt-0" />
                <x-input-error :messages="$errors->get('business_name')" class="mt-1" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="business_type" value="Business Type / Industry" />
                    <x-text-input wire:model="business_type" id="business_type" type="text" class="mt-0" />
                </div>
                <div>
                    <x-input-label for="business_address" value="Business Address" />
                    <x-text-input wire:model="business_address" id="business_address" type="text" class="mt-0" />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-input-label for="owner_name" value="Owner / Decision Maker" />
                    <x-text-input wire:model="owner_name" id="owner_name" type="text" class="mt-0" />
                </div>
                <div>
                    <x-input-label for="owner_designation" value="Designation" />
                    <x-text-input wire:model="owner_designation" id="owner_designation" type="text" class="mt-0" />
                </div>
                <div>
                    <x-input-label for="owner_contact" value="Contact" />
                    <x-text-input wire:model="owner_contact" id="owner_contact" type="text" class="mt-0" />
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Create Client</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
