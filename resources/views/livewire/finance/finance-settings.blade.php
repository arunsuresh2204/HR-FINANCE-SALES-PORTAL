<div>
    <x-page-header title="Finance Settings" subtitle="Signature, authorized signer, and GSTIN shown on invoice PDFs." />

    <div class="glass-card max-w-xl">
        <form wire:submit="save" class="space-y-4">
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
            <div class="flex justify-end pt-2">
                <x-primary-button>Save Settings</x-primary-button>
            </div>
        </form>
    </div>
</div>
