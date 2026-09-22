<?php

namespace App\Http\Controllers;

use App\Models\FinanceSetting;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditNotePdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        abort_unless($invoice->adjustment_type === 'credit_note' && $invoice->adjustment_document_number, 404);

        $pdf = Pdf::loadView('pdf.credit-note', [
            'invoice' => $invoice->load('client'),
            'financeSetting' => FinanceSetting::current(),
        ]);

        return $pdf->stream("{$invoice->adjustment_document_number}.pdf");
    }
}
