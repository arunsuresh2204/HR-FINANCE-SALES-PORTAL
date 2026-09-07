<div>
    <x-page-header title="Daily Marketing Log" subtitle="Track your content and campaign work.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Log Entry</button>
        </x-slot:actions>
    </x-page-header>

    <x-stat-card label="Hours This Week" :value="number_format($weekHours, 1)" icon="megaphone" accent="violet" class="max-w-xs" />

    <div class="glass-panel relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Date</th><th>Platform</th><th>Type</th><th>Client / Product</th><th>Hours</th><th>Notes</th></tr></thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->work_date->format('M j, Y') }}</td>
                            <td class="text-white">{{ $entry->platform }}</td>
                            <td class="capitalize">{{ $entry->task_type }}</td>
                            <td>{{ $entry->is_in_house_product ? 'In-house Product' : ($entry->client->business_name ?? '—') }}</td>
                            <td class="font-semibold text-white">{{ number_format($entry->hours, 1) }}h</td>
                            <td class="max-w-xs truncate">
                                @if ($entry->deliverable_link)
                                    <a href="{{ $entry->deliverable_link }}" target="_blank" class="text-gold-300 hover:text-gold-200">{{ $entry->notes ?: 'View deliverable' }}</a>
                                @else
                                    {{ $entry->notes ?: '—' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No marketing log entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $entries->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="Log Marketing Entry">
        <form wire:submit="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="work_date" value="Date" />
                    <x-text-input wire:model="work_date" id="work_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('work_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="hours" value="Hours" />
                    <x-text-input wire:model="hours" id="hours" type="number" step="0.25" class="mt-0" />
                    <x-input-error :messages="$errors->get('hours')" class="mt-1" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="platform" value="Platform" />
                    <x-text-input wire:model="platform" id="platform" type="text" class="mt-0" placeholder="Instagram, LinkedIn..." />
                    <x-input-error :messages="$errors->get('platform')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="task_type" value="Task Type" />
                    <select wire:model="task_type" id="task_type" class="input-glass">
                        <option value="content">Content</option>
                        <option value="ad">Ad</option>
                        <option value="engagement">Engagement</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-white/70">
                <input wire:model.live="is_in_house_product" type="checkbox" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                This is for the in-house product
            </label>
            @unless ($is_in_house_product)
                <div>
                    <x-input-label for="client_id" value="Client" />
                    <select wire:model="client_id" id="client_id" class="input-glass">
                        <option value="">— Select client —</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->business_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div>
                <x-input-label for="deliverable_link" value="Deliverable Link (optional)" />
                <x-text-input wire:model="deliverable_link" id="deliverable_link" type="url" class="mt-0" placeholder="https://..." />
                <x-input-error :messages="$errors->get('deliverable_link')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="notes" value="Notes" />
                <textarea wire:model="notes" id="notes" rows="2" class="input-glass"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save Entry</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
