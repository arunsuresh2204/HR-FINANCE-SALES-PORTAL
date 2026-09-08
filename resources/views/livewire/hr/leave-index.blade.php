<div>
    <x-page-header title="Time Off" subtitle="Request leave and track your balance.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Request Leave</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Annual Entitlement" :value="\App\Livewire\Hr\LeaveIndex::ANNUAL_ENTITLEMENT" icon="calendar" accent="sky" />
        <x-stat-card label="Days Used (Approved)" :value="$usedDays" icon="calendar" accent="gold" />
        <x-stat-card label="Days Remaining" :value="$remainingDays" icon="calendar" accent="emerald" />
    </div>

    <div class="glass-panel relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Certificate</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="font-medium capitalize text-white">{{ $req->type }}</td>
                            <td>{{ $req->start_date->format('M j') }} – {{ $req->end_date->format('M j, Y') }}</td>
                            <td>{{ $req->days }}</td>
                            <td class="max-w-xs truncate">{{ $req->reason ?: '—' }}</td>
                            <td>
                                @if ($req->hasCertificate())
                                    <a href="{{ Storage::url($req->certificate_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                                @elseif ($req->requiresCertificate())
                                    <button wire:click="openCertUpload({{ $req->id }})" class="text-xs font-semibold text-rose-300 hover:text-rose-200">
                                        <x-icon name="upload" class="mr-0.5 inline h-3 w-3" />Upload
                                    </button>
                                @else
                                    <span class="text-white/25">—</span>
                                @endif
                            </td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td class="text-right">
                                @if ($req->status === 'pending')
                                    <button wire:click="cancel({{ $req->id }})" wire:confirm="Cancel this leave request?" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Cancel</button>
                                @endif
                            </td>
                        </tr>
                        @if ($req->needsCertificate())
                            <tr>
                                <td colspan="7" class="!py-2.5">
                                    <div class="flex items-center justify-between gap-3 rounded-lg border border-rose-400/20 bg-rose-400/[0.06] px-3 py-2 text-xs text-rose-200">
                                        <span class="flex items-center gap-1.5">
                                            <x-icon name="receipt" class="h-3.5 w-3.5 shrink-0" />
                                            @if ($req->certificate_requested_at)
                                                HR has requested a medical certificate for this leave. Please upload it{{ $req->status === 'pending' ? ' before it can be approved' : '' }}.
                                            @else
                                                Medical certificate required for sick leave of {{ \App\Models\LeaveRequest::CERTIFICATE_MIN_DAYS }}+ days.
                                            @endif
                                        </span>
                                        <button wire:click="openCertUpload({{ $req->id }})" class="shrink-0 font-semibold text-rose-100 underline hover:text-white">Upload now</button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-white/40">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $requests->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="Request Leave">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="type" value="Leave Type" />
                <select wire:model.live="type" id="type" class="input-glass">
                    <option value="vacation">Vacation</option>
                    <option value="sick">Sick</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="other">Other</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-1" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="start_date" value="Start Date" />
                    <x-text-input wire:model.live="start_date" id="start_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="end_date" value="End Date" />
                    <x-text-input wire:model.live="end_date" id="end_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                </div>
            </div>
            @if ($previewDays > 0)
                <p class="text-xs text-white/40">{{ $previewDays }} working {{ Str::plural('day', $previewDays) }} requested.</p>
            @endif
            <div>
                <x-input-label for="reason" value="Reason (optional)" />
                <textarea wire:model="reason" id="reason" rows="3" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('reason')" class="mt-1" />
            </div>

            @if ($type === 'sick')
                <div>
                    <x-input-label for="certificate" value="Medical Certificate {{ $previewDays >= \App\Models\LeaveRequest::CERTIFICATE_MIN_DAYS ? '(required for 3+ days)' : '(optional)' }}" />
                    <input wire:model="certificate" id="certificate" type="file" accept=".jpg,.jpeg,.png,.pdf" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                    <x-input-error :messages="$errors->get('certificate')" class="mt-1" />
                    <div wire:loading wire:target="certificate" class="mt-1 text-xs text-white/40">Uploading...</div>

                    @if ($showCertificateHint)
                        <p class="mt-2 flex items-start gap-1.5 rounded-lg border border-amber-400/25 bg-amber-400/10 px-3 py-2 text-xs text-amber-200">
                            <x-icon name="receipt" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            You can still submit without it, but a medical certificate is required for sick leave of {{ \App\Models\LeaveRequest::CERTIFICATE_MIN_DAYS }}+ days. HR may ask you to upload one before approving.
                        </p>
                    @endif
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Submit Request</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showCertUploadForm" title="Upload Medical Certificate">
        <form wire:submit="submitCertUpload" class="space-y-4">
            <div>
                <x-input-label for="certUploadFile" value="Certificate File" />
                <input wire:model="certUploadFile" id="certUploadFile" type="file" accept=".jpg,.jpeg,.png,.pdf" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                <x-input-error :messages="$errors->get('certUploadFile')" class="mt-1" />
                <div wire:loading wire:target="certUploadFile" class="mt-1 text-xs text-white/40">Uploading...</div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Upload</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
