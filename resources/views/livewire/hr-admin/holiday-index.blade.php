<div>
    <x-page-header title="Holiday Calendar" subtitle="Kerala public holidays excluded from leave day calculations. Working days are Monday–Friday.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Add Holiday</button>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-6">
        @forelse ($holidays as $year => $yearHolidays)
            <div class="glass-panel relative overflow-hidden p-6">
                <div class="glass-sheen"></div>
                <h2 class="mb-4 text-base font-bold text-white">{{ $year }}</h2>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($yearHolidays as $holiday)
                        <div class="glass-inset flex items-center justify-between p-3">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $holiday->name }}</p>
                                <p class="text-xs text-white/40">{{ $holiday->date->format('l, M j, Y') }}</p>
                            </div>
                            <button wire:click="delete({{ $holiday->id }})" wire:confirm="Remove this holiday?" class="text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="glass-panel relative overflow-hidden p-8 text-center">
                <div class="glass-sheen"></div>
                <p class="text-sm text-white/40">No holidays configured yet.</p>
            </div>
        @endforelse
    </div>

    <x-modal-glass wire-model="showForm" title="Add Holiday">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="date" value="Date" />
                <x-text-input wire:model="date" id="date" type="date" class="mt-0" />
                <x-input-error :messages="$errors->get('date')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="name" value="Holiday Name" />
                <x-text-input wire:model="name" id="name" type="text" class="mt-0" placeholder="e.g. Onam (Thiruvonam)" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Add Holiday</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
