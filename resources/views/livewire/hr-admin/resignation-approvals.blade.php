<div>
    <x-page-header title="Resignations & Offboarding" subtitle="Review resignation notices and track offboarding checklists." />

    <div class="space-y-4">
        @forelse ($resignations as $r)
            <div class="glass-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-white">{{ $r->user->name }}</p>
                        <p class="text-sm text-white/50">Notice: {{ $r->notice_date->format('M j, Y') }} &middot; Last day: {{ $r->last_working_date->format('M j, Y') }}</p>
                        @if ($r->reason)
                            <div class="rich-text-content mt-2 text-sm text-white/60">{!! $r->reason !!}</div>
                        @endif
                        @if ($r->cc_email)
                            <p class="mt-1 text-xs text-white/35">CC'd to {{ $r->cc_email }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-pill :status="$r->status" />
                        @if ($r->status === 'pending')
                            <button wire:click="accept({{ $r->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Accept</button>
                            <button wire:click="reject({{ $r->id }})" class="rounded-lg bg-rose-400/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-400/25">Reject</button>
                        @endif
                    </div>
                </div>

                @if ($r->offboardingTasks->isNotEmpty())
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Offboarding Checklist</p>
                        <div class="space-y-1.5">
                            @foreach ($r->offboardingTasks as $task)
                                <label class="flex items-center gap-2 text-sm {{ $task->completed ? 'text-white/40 line-through' : 'text-white/75' }}">
                                    <input type="checkbox" wire:click="toggleTask({{ $task->id }})" @checked($task->completed) class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                                    {{ $task->task }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (in_array($r->status, ['accepted', 'completed'], true))
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Exit Documents</p>
                        <p class="mb-3 text-[11px] text-white/35">Visible to {{ $r->user->name }} once offboarding is fully complete.</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="glass-inset flex items-center justify-between gap-2 p-2.5 text-xs">
                                <div>
                                    <p class="font-semibold text-white/80">Experience Certificate</p>
                                    <p class="text-white/40">{{ $r->experience_certificate_published_at ? 'Published '.$r->experience_certificate_published_at->format('M j, Y') : 'Not yet published' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($r->experience_certificate_published_at)
                                        <a href="{{ route('resignations.experience-certificate', $r) }}" target="_blank" class="font-semibold text-gold-300 hover:text-gold-200">View</a>
                                    @endif
                                    <button type="button" wire:click="openDocumentForm({{ $r->id }}, 'experience_certificate')" class="font-semibold text-white/50 hover:text-white">{{ $r->experience_certificate_published_at ? 'Edit' : 'Generate' }}</button>
                                </div>
                            </div>
                            <div class="glass-inset flex items-center justify-between gap-2 p-2.5 text-xs">
                                <div>
                                    <p class="font-semibold text-white/80">Relieving Letter</p>
                                    <p class="text-white/40">{{ $r->relieving_letter_published_at ? 'Published '.$r->relieving_letter_published_at->format('M j, Y') : 'Not yet published' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($r->relieving_letter_published_at)
                                        <a href="{{ route('resignations.relieving-letter', $r) }}" target="_blank" class="font-semibold text-gold-300 hover:text-gold-200">View</a>
                                    @endif
                                    <button type="button" wire:click="openDocumentForm({{ $r->id }}, 'relieving_letter')" class="font-semibold text-white/50 hover:text-white">{{ $r->relieving_letter_published_at ? 'Edit' : 'Generate' }}</button>
                                </div>
                            </div>
                            <div class="glass-inset flex items-center justify-between gap-2 p-2.5 text-xs">
                                <div>
                                    <p class="font-semibold text-white/80">Form 16 <span class="text-white/35">(if applicable)</span></p>
                                    <p class="text-white/40">{{ $r->form16_path ? 'Uploaded' : 'Not uploaded' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($r->form16_path)
                                        <a href="{{ Storage::url($r->form16_path) }}" target="_blank" class="font-semibold text-gold-300 hover:text-gold-200">View</a>
                                        <button type="button" wire:click="deleteExitFile({{ $r->id }}, 'form16')" wire:confirm="Remove this file?" class="font-semibold text-white/40 hover:text-rose-300"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                                    @endif
                                    <button type="button" wire:click="openFileUploadForm({{ $r->id }}, 'form16')" class="font-semibold text-white/50 hover:text-white">{{ $r->form16_path ? 'Replace' : 'Upload' }}</button>
                                </div>
                            </div>
                            <div class="glass-inset flex items-center justify-between gap-2 p-2.5 text-xs">
                                <div>
                                    <p class="font-semibold text-white/80">UAN / PF Document <span class="text-white/35">(if applicable)</span></p>
                                    <p class="text-white/40">{{ $r->uan_pf_path ? 'Uploaded' : 'Not uploaded' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($r->uan_pf_path)
                                        <a href="{{ Storage::url($r->uan_pf_path) }}" target="_blank" class="font-semibold text-gold-300 hover:text-gold-200">View</a>
                                        <button type="button" wire:click="deleteExitFile({{ $r->id }}, 'uan_pf')" wire:confirm="Remove this file?" class="font-semibold text-white/40 hover:text-rose-300"><x-icon name="trash" class="h-3.5 w-3.5" /></button>
                                    @endif
                                    <button type="button" wire:click="openFileUploadForm({{ $r->id }}, 'uan_pf')" class="font-semibold text-white/50 hover:text-white">{{ $r->uan_pf_path ? 'Replace' : 'Upload' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-white/40">No resignation notices on file.</p>
        @endforelse
        @if ($resignations->hasPages())
            <div class="glass-card">{{ $resignations->links() }}</div>
        @endif
    </div>

    <x-modal-glass wire-model="showDocumentForm" :title="$documentType === 'experience_certificate' ? 'Experience Certificate' : 'Relieving Letter'" max-width="2xl">
        <div class="space-y-4">
            <p class="text-xs text-white/40">This is a starting draft &mdash; review and edit it before publishing. The employee will see this in their Exit Documents page as soon as you publish.</p>
            <div wire:key="exit-doc-editor-{{ $documentModalInstance }}">
                <x-rich-editor model="document_content" :value="$document_content" placeholder="Document text…" />
            </div>
            <x-input-error :messages="$errors->get('document_content')" class="mt-1" />
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <button type="button" wire:click="publishDocument" class="btn-glass-primary text-xs">Save &amp; Publish to Employee</button>
            </div>
        </div>
    </x-modal-glass>

    <x-modal-glass wire-model="showFileUploadForm" :title="$fileUploadType === 'form16' ? 'Upload Form 16' : 'Upload UAN / PF Document'">
        <form wire:submit="saveFileUpload" class="space-y-4">
            <div>
                <x-input-label value="File" for="exit_document_file" />
                <input wire:model="exit_document_file" id="exit_document_file" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                <div wire:loading wire:target="exit_document_file" class="mt-1 text-xs text-white/40">Uploading&hellip;</div>
                <x-input-error :messages="$errors->get('exit_document_file')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
