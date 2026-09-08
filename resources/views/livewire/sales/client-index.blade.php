<div>
    <x-page-header title="Clients" subtitle="Converted leads and active client accounts.">
        <x-slot:actions>
            <a href="{{ route('sales.leads') }}" wire:navigate class="btn-glass-secondary"><x-icon name="target" class="h-4 w-4" /> Leads Pipeline</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="relative max-w-sm flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/30" />
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search clients..." class="input-glass pl-9">
        </div>
        <div class="inline-flex rounded-xl border border-white/10 bg-white/5 p-1">
            <button type="button" wire:click="setView('grid')" class="rounded-lg p-1.5 transition {{ $view === 'grid' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}" title="Grid view"><x-icon name="grid" class="h-4 w-4" /></button>
            <button type="button" wire:click="setView('list')" class="rounded-lg p-1.5 transition {{ $view === 'list' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}" title="List view"><x-icon name="list" class="h-4 w-4" /></button>
        </div>
    </div>

    @if ($view === 'grid')
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
    @else
        <div class="glass-panel relative overflow-hidden">
            <div class="glass-sheen"></div>
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Type</th>
                            <th>Sales Person</th>
                            <th>Agreement</th>
                            <th>Invoiced</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr wire:key="client-row-{{ $client->id }}">
                                <td class="font-medium text-white">{{ $client->business_name }}</td>
                                <td class="text-white/60">{{ $client->business_type ?? '—' }}</td>
                                <td class="text-white/60">{{ $client->salesPerson->name }}</td>
                                <td>
                                    @if ($client->agreement_file)
                                        <span class="badge-glass"><x-icon name="document" class="h-3 w-3" /> On file</span>
                                    @else
                                        <span class="text-white/25">—</span>
                                    @endif
                                </td>
                                <td class="font-semibold text-gold-300">${{ number_format($client->totalInvoiced(), 0) }}</td>
                                <td class="text-right"><a href="{{ route('sales.clients.show', $client) }}" wire:navigate class="text-xs font-semibold text-gold-300 hover:text-gold-200">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-white/40">No clients yet. Convert a won lead to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    <div class="mt-4">{{ $clients->links() }}</div>
</div>
