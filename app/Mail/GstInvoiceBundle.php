<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GstInvoiceBundle extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $periodLabel,
        public int $invoiceCount,
        public string $zipPath,
        public string $zipFilename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' — GST Invoices for '.$this->periodLabel,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.gst-invoice-bundle',
        );
    }

    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath($this->zipPath)
                ->as($this->zipFilename)
                ->withMime('application/zip'),
        ];
    }
}
