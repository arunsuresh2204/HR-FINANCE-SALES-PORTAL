<x-mail::message>
# GST Invoices — {{ $periodLabel }}

Please find attached {{ $invoiceCount }} {{ Str::plural('invoice', $invoiceCount) }} for **{{ $periodLabel }}**, for monthly GST filing.

The attached ZIP contains one PDF per invoice.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
