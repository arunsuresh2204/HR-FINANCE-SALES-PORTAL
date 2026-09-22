<?php

namespace App\Http\Controllers;

use App\Models\FinanceSetting;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class RefundVoucherPdfController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        abort_unless($invoice->adjustment_type === 'refund' && $invoice->adjustment_document_number, 404);

        $pdf = Pdf::loadView('pdf.refund-voucher', [
            'invoice' => $invoice->load('client'),
            'financeSetting' => FinanceSetting::current(),
        ]);

        return $pdf->stream("{$invoice->adjustment_document_number}.pdf");
    }
}
