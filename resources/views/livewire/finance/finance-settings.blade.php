<div>
    <x-page-header title="Finance Settings" subtitle="Signature, authorized signer, GSTIN, and accounting exports." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="glass-card">
            <h2 class="text-base font-bold text-white">Invoice Details</h2>
            <p class="mt-1 text-xs text-white/40">Signature, authorized signer, and GSTIN shown on invoice PDFs.</p>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div>
                    <x-input-label value="GSTIN" for="gstin" />
                    <x-text-input wire:model="gstin" id="gstin" type="text" class="mt-0" placeholder="Not yet registered &mdash; add once GST registration is obtained" />
                    <p class="mt-1 text-xs text-white/40">Leave blank until GST registration is obtained (currently not required, as revenue is under &#8377;20L). Once set, it appears on all invoices.</p>
                    <x-input-error :messages="$errors->get('gstin')" class="mt-1" />
                </div>
                <div>
                    <x-input-label value="Signature Image" for="signature" />
                    @if ($setting->signature_path)
                        <img src="{{ Storage::url($setting->signature_path) }}" alt="Signature" class="mt-2 h-16 bg-white/90 rounded px-2 py-1">
                    @endif
                    <input wire:model="signature" id="signature" type="file" accept="image/*" class="mt-2 input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                    <div wire:loading wire:target="signature" class="mt-1 text-xs text-white/40">Uploading&hellip;</div>
                    <x-input-error :messages="$errors->get('signature')" class="mt-1" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Authorized Signer Name" for="signer_name" />
                        <x-text-input wire:model="signer_name" id="signer_name" type="text" class="mt-0" />
                        <x-input-error :messages="$errors->get('signer_name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Designation" for="signer_designation" />
                        <x-text-input wire:model="signer_designation" id="signer_designation" type="text" class="mt-0" placeholder="e.g. Director" />
                        <x-input-error :messages="$errors->get('signer_designation')" class="mt-1" />
                    </div>
                </div>
                <div class="flex justify-end pt-2">
                    <x-primary-button>Save Settings</x-primary-button>
                </div>
            </form>
        </div>

        <div class="glass-card">
            <h2 class="text-base font-bold text-white">Accounting Exports</h2>
            <p class="mt-1 text-xs text-white/40">Pick a date range, then download whichever records you need for your books.</p>

            <form method="GET" target="_blank">
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="From" for="export_from" />
                        <input id="export_from" name="from" type="date" required value="{{ now()->startOfYear()->toDateString() }}" class="input-glass mt-0">
                    </div>
                    <div>
                        <x-input-label value="To" for="export_to" />
                        <input id="export_to" name="to" type="date" required value="{{ now()->toDateString() }}" class="input-glass mt-0">
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    <div class="glass-inset flex flex-wrap items-center justify-between gap-3 p-3">
                        <div>
                            <p class="text-sm font-semibold text-white">Invoices</p>
                            <p class="text-xs text-white/40">Invoice register, or the PDFs themselves.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" formaction="{{ route('finance.invoices.export.csv') }}" class="btn-glass-secondary"><x-icon name="document" class="h-4 w-4" /> CSV</button>
                            <button type="submit" formaction="{{ route('finance.invoices.export.pdfs') }}" class="btn-glass-secondary"><x-icon name="document" class="h-4 w-4" /> PDFs (ZIP)</button>
                        </div>
                    </div>

                    <div class="glass-inset flex flex-wrap items-center justify-between gap-3 p-3">
                        <div>
                            <p class="text-sm font-semibold text-white">Expenses</p>
                            <p class="text-xs text-white/40">Approved employee claims + operational expenses.</p>
                        </div>
                        <button type="submit" formaction="{{ route('finance.expenses.export.csv') }}" class="btn-glass-secondary"><x-icon name="document" class="h-4 w-4" /> CSV</button>
                    </div>

                    <div class="glass-inset flex flex-wrap items-center justify-between gap-3 p-3">
                        <div>
                            <p class="text-sm font-semibold text-white">Payroll</p>
                            <p class="text-xs text-white/40">Itemized salary runs (drafts excluded).</p>
                        </div>
                        <button type="submit" formaction="{{ route('finance.payroll.export.csv') }}" class="btn-glass-secondary"><x-icon name="document" class="h-4 w-4" /> CSV</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
