<div>
    <x-page-header title="Clients" subtitle="Converted leads and active client accounts.">
        <x-slot:actions>
            <a href="{{ route('sales.leads') }}" wire:navigate class="btn-glass-secondary"><x-icon name="target" class="h-4 w-4" /> Leads Pipeline</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 max-w-sm">
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/30" />
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search clients..." class="input-glass pl-9">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($clients as $client)
            <a href="{{ route('sales.clients.show', $client) }}" wire:navigate class="glass-card-hover">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-400/15 text-sky-300"><x-icon name="briefcase" class="h-5 w-5" /></span>
                    @if ($client->agreement_file)
                        <span class="badge-glass"><x-icon name="document" class="h-3 w-3" /> Agreement on file</span>
                    @endif
                </div>
                <p class="mt-3 font-semibold text-white">{{ $client->business_name }}</p>
                <p class="text-sm text-white/45">{{ $client->business_type ?? 'Client' }}</p>
                <div class="mt-3 flex items-center justify-between text-xs text-white/40">
                    <span>{{ $client->salesPerson->name }}</span>
                    <span class="font-semibold text-gold-300">${{ number_format($client->totalInvoiced(), 0) }} invoiced</span>
                </div>
            </a>
        @empty
            <p class="col-span-full text-sm text-white/40">No clients yet. Convert a won lead to get started.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $clients->links() }}</div>
</div>
