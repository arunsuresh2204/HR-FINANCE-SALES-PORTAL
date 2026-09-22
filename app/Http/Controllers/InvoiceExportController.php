<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesExportDateRange;
use App\Models\FinanceSetting;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class InvoiceExportController extends Controller
{
    use ValidatesExportDateRange;

    public function csv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);

        $invoices = Invoice::with('client', 'adjustments')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $filename = 'invoices-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($invoices) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Invoice Number', 'Invoice Date', 'Due Date', 'Client', 'Client Tax ID',
                'Currency', 'Subtotal', 'Tax %', 'Tax Amount', 'Total Amount',
                'Amount Paid (INR)', 'Balance Due', 'Status', 'Total Credited (invoice currency)',
                'Total Refunded (INR)', 'Total Written Off (invoice currency)', 'Adjustment Count', 'Latest Adjustment Date',
            ]);

            foreach ($invoices as $invoice) {
                $latestAdjustment = $invoice->adjustments->last();

                fputcsv($out, [
                    $invoice->invoice_number,
                    $invoice->created_at->format('Y-m-d'),
                    $invoice->due_date?->format('Y-m-d'),
                    $invoice->client->business_name,
                    $invoice->client->tax_id,
                    $invoice->currency,
                    number_format((float) $invoice->amount, 2, '.', ''),
                    number_format((float) $invoice->tax_percent, 2, '.', ''),
                    number_format((float) $invoice->total_amount - (float) $invoice->amount, 2, '.', ''),
                    number_format((float) $invoice->total_amount, 2, '.', ''),
                    number_format((float) $invoice->amount_paid, 2, '.', ''),
                    number_format($invoice->balanceDue(), 2, '.', ''),
                    $invoice->status,
                    number_format($invoice->totalCredited(), 2, '.', ''),
                    number_format($invoice->totalRefunded(), 2, '.', ''),
                    number_format($invoice->totalWrittenOff(), 2, '.', ''),
                    $invoice->adjustments->count(),
                    $latestAdjustment?->created_at->format('Y-m-d'),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function pdfsZip(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $invoices = Invoice::with('client', 'adjustments')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'No invoices in that date range.');
        }

        $financeSetting = FinanceSetting::current();
        $zipPath = tempnam(sys_get_temp_dir(), 'invoices').'.zip';

        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

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

        $filename = 'invoices-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.zip';

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}
