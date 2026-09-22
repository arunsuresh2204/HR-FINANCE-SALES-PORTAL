<?php

namespace App\Http\Controllers;

use App\Models\FinanceSetting;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice->load('client', 'adjustments'),
            'financeSetting' => FinanceSetting::current(),
        ]);

        // The invoice's status, amount paid, and adjustments can all change
        // after it's first downloaded, so the browser must never serve a
        // stale cached copy of this same URL.
        return $pdf->stream("{$invoice->invoice_number}.pdf")
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
