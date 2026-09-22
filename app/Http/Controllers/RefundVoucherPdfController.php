<?php

namespace App\Http\Controllers;

use App\Models\FinanceSetting;
use App\Models\Invoice;
use App\Models\InvoiceAdjustment;
use Barryvdh\DomPDF\Facade\Pdf;

class RefundVoucherPdfController extends Controller
{
    public function __invoke(Invoice $invoice, InvoiceAdjustment $adjustment)
    {
        abort_unless($adjustment->invoice_id === $invoice->id && $adjustment->type === 'refund', 404);

        $pdf = Pdf::loadView('pdf.refund-voucher', [
            'invoice' => $invoice->load('client'),
            'adjustment' => $adjustment,
            'financeSetting' => FinanceSetting::current(),
        ]);

        return $pdf->stream("{$adjustment->document_number}.pdf")
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
