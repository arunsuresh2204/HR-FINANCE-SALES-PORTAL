<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice->load('client')]);

        return $pdf->stream("{$invoice->invoice_number}.pdf");
    }
}
