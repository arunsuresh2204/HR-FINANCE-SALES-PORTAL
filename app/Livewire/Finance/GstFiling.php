<?php

namespace App\Livewire\Finance;

use App\Mail\GstInvoiceBundle;
use App\Models\FinanceSetting;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class GstFiling extends Component
{
    public int $month;

    public int $year;

    public array $selectedIds = [];

    public bool $selectAll = false;

    public string $email = '';

    public bool $sending = false;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function updatedMonth(): void
    {
        $this->resetSelection();
    }

    public function updatedYear(): void
    {
        $this->resetSelection();
    }

    protected function resetSelection(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value ? $this->monthInvoices()->pluck('id')->map(fn ($id) => (string) $id)->all() : [];
    }

    protected function monthInvoices()
    {
        return Invoice::whereYear('created_at', $this->year)
            ->whereMonth('created_at', $this->month)
            ->orderBy('invoice_number')
            ->get();
    }

    public function send(): void
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        if (empty($this->selectedIds)) {
            $this->addError('email', 'Select at least one invoice.');

            return;
        }

        $this->sending = true;

        $invoices = Invoice::with('client', 'adjustments')->whereIn('id', $this->selectedIds)->orderBy('invoice_number')->get();

        $financeSetting = FinanceSetting::current();
        $zipPath = tempnam(sys_get_temp_dir(), 'gst-invoices').'.zip';

        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $usedNames = [];
        foreach ($invoices as $invoice) {
            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'financeSetting' => $financeSetting,
            ]);

            $name = $invoice->invoice_number.'.pdf';
            $suffix = 1;
            while (in_array($name, $usedNames, true)) {
                $name = $invoice->invoice_number.'-'.(++$suffix).'.pdf';
            }
            $usedNames[] = $name;

            $zip->addFromString($name, $pdf->output());
        }

        $zip->close();

        $periodLabel = Carbon::create($this->year, $this->month, 1)->format('F Y');
        $zipFilename = 'gst-invoices-'.Carbon::create($this->year, $this->month, 1)->format('Y-m').'.zip';

        try {
            Mail::to($this->email)->send(new GstInvoiceBundle(
                periodLabel: $periodLabel,
                invoiceCount: $invoices->count(),
                zipPath: $zipPath,
                zipFilename: $zipFilename,
            ));

            $this->dispatch('toast', message: "Sent {$invoices->count()} invoice(s) to {$this->email}.", type: 'success');
            $this->resetSelection();
            $this->email = '';
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'Could not send the email. Check mail settings and try again.', type: 'error');
        } finally {
            @unlink($zipPath);
            $this->sending = false;
        }
    }

    public function render()
    {
        $invoices = $this->monthInvoices()->load('client');

        $selected = $invoices->whereIn('id', $this->selectedIds);

        return view('livewire.finance.gst-filing', [
            'invoices' => $invoices,
            'selectedTotalsByCurrency' => $selected->groupBy('currency')->map(fn ($group) => $group->sum(fn (Invoice $i) => (float) $i->total_amount)),
        ]);
    }
}
