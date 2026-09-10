<div>
    <x-page-header title="My Documents" subtitle="Career progress, offer letter and mandatory records on file." />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="glass-panel relative overflow-hidden p-6 lg:col-span-2">
            <div class="glass-sheen"></div>
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold-400/15 text-gold-300"><x-icon name="briefcase" class="h-5 w-5" /></span>
                <div>
                    <h2 class="text-base font-bold text-white">Career Progress</h2>
                    <p class="text-xs text-white/40">{{ auth()->user()->employmentTypeLabel() }} &middot; {{ auth()->user()->designation ?: 'No designation set' }}{{ auth()->user()->department ? ' · '.auth()->user()->department : '' }}</p>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($promotions as $promo)
                    <div class="glass-inset flex items-start gap-4 p-4">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-400/15 text-emerald-300"><x-icon name="check" class="h-4 w-4" /></span>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-white">{{ $promo->new_designation }}@if ($promo->new_department) <span class="text-white/40">&middot; {{ $promo->new_department }}</span>@endif</p>
                            <p class="mt-0.5 text-xs text-white/40">
                                Effective {{ $promo->effective_date->format('M j, Y') }}
                                @if ($promo->previous_designation)
                                    &middot; promoted from {{ $promo->previous_designation }}
                                @endif
                            </p>
                            @if ($promo->notes)
                                <p class="mt-1.5 text-xs text-white/50">{{ $promo->notes }}</p>
                            @endif
                        </div>
                        @if ($promo->certificate_path)
                            <a href="{{ Storage::url($promo->certificate_path) }}" target="_blank" class="shrink-0 text-xs font-semibold text-gold-300 hover:text-gold-200">Certificate</a>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-white/40">No promotions on record yet. Your career milestones will appear here once HR records them.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-panel relative overflow-hidden p-6">
            <div class="glass-sheen"></div>
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-400/15 text-sky-300"><x-icon name="document" class="h-5 w-5" /></span>
                <h2 class="text-base font-bold text-white">Offer Letter</h2>
            </div>
            <div class="mt-4">
                @if ($offerLetter)
                    <a href="{{ Storage::url($offerLetter->file_path) }}" target="_blank" class="glass-inset flex items-center justify-between p-3 transition hover:bg-white/[0.06]">
                        <span class="text-sm font-medium text-white">{{ $offerLetter->title }}</span>
                        <x-icon name="arrow-right" class="h-4 w-4 text-white/30" />
                    </a>
                @else
                    <p class="text-sm text-white/40">Your offer letter hasn't been uploaded by HR yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="glass-panel relative mt-6 overflow-hidden p-6">
        <div class="glass-sheen"></div>
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Mandatory Document Checklist</h2>
            @if ($requiredMissing > 0)
                <span class="badge-glass !border-rose-400/25 !bg-rose-400/10 !text-rose-200">{{ $requiredMissing }} required document{{ $requiredMissing === 1 ? '' : 's' }} missing</span>
            @else
                <span class="badge-glass !border-emerald-400/25 !bg-emerald-400/10 !text-emerald-200">All required documents submitted</span>
            @endif
        </div>

        <div class="mt-6 space-y-6">
            @foreach ($catalog as $groupKey => $group)
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-white/40">{{ $group['label'] }}</h3>

                    @if ($groupKey === 'banking_statutory')
                        @php $user = auth()->user(); @endphp
                        <div class="glass-inset mb-2 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-medium text-white">Bank Account Details</p>
                                <span class="text-xs {{ $user->hasCompleteBankDetails() ? 'text-white/35' : 'text-rose-300/70' }}">{{ $user->hasCompleteBankDetails() ? 'On file' : 'Required' }}</span>
                            </div>
                            <form wire:submit="saveBankDetails" class="grid gap-2 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="bank_account_holder_name" value="Account Holder Name" class="!text-[11px]" />
                                    <x-text-input wire:model="bank_account_holder_name" id="bank_account_holder_name" type="text" class="mt-0.5 !text-xs" />
                                    <x-input-error :messages="$errors->get('bank_account_holder_name')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="bank_name" value="Bank Name" class="!text-[11px]" />
                                    <x-text-input wire:model="bank_name" id="bank_name" type="text" class="mt-0.5 !text-xs" />
                                    <x-input-error :messages="$errors->get('bank_name')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="bank_account_number" value="Account Number" class="!text-[11px]" />
                                    <x-text-input wire:model="bank_account_number" id="bank_account_number" type="text" class="mt-0.5 !text-xs" />
                                    <x-input-error :messages="$errors->get('bank_account_number')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="bank_ifsc" value="IFSC Code" class="!text-[11px]" />
                                    <x-text-input wire:model="bank_ifsc" id="bank_ifsc" type="text" class="mt-0.5 !text-xs" />
                                    <x-input-error :messages="$errors->get('bank_ifsc')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="bank_branch" value="Branch" class="!text-[11px]" />
                                    <x-text-input wire:model="bank_branch" id="bank_branch" type="text" class="mt-0.5 !text-xs" />
                                    <x-input-error :messages="$errors->get('bank_branch')" class="mt-1" />
                                </div>
                                <div class="flex items-end">
                                    <x-primary-button type="submit" class="!py-1.5 !text-xs">Save Bank Details</x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($group['items'] as $key => $item)
                            @php $doc = $documents->get($key); @endphp
                            <div class="glass-inset flex items-center justify-between p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-white">{{ $item['label'] }}</p>
                                    <p class="text-xs {{ $item['required'] ? 'text-rose-300/70' : 'text-white/35' }}">{{ $item['required'] ? 'Required' : 'Optional' }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-3">
                                    @if ($doc)
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                                        <button wire:click="openForm('{{ $key }}')" class="text-xs font-semibold text-white/50 hover:text-white/80">Replace</button>
                                    @else
                                        <button wire:click="openForm('{{ $key }}')" class="rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-white/80 hover:bg-white/20">Upload</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="glass-panel relative mt-6 overflow-hidden p-6">
        <div class="glass-sheen"></div>
        <h2 class="mb-4 text-base font-bold text-white">Company Policies</h2>
        <div class="space-y-2">
            @forelse ($policies as $policy)
                <a href="{{ Storage::url($policy->file_path) }}" target="_blank" class="glass-inset flex items-center justify-between p-3 transition hover:bg-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-400/15 text-sky-300"><x-icon name="document" class="h-4 w-4" /></span>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $policy->title }}</p>
                            <p class="text-xs text-white/40">{{ $policy->description }}</p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="h-4 w-4 text-white/30" />
                </a>
            @empty
                <p class="text-sm text-white/40">No policy documents published yet.</p>
            @endforelse
        </div>
    </div>

    <x-modal-glass wire-model="showForm" title="Upload Document">
        <form wire:submit="submit" class="space-y-4">
            <p class="text-sm text-white/60">{{ $activeKey ? (\App\Models\EmployeeDocument::flatCatalog()[$activeKey]['label'] ?? '') : '' }}</p>
            <div>
                <x-input-label for="file" value="File" />
                <input wire:model="file" id="file" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80" />
                <x-input-error :messages="$errors->get('file')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Upload</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
