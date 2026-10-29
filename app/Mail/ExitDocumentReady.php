<?php

namespace App\Mail;

use App\Models\Resignation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExitDocumentReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Resignation $resignation,
        public string $documentLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->documentLabel.' Available — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.exit-document-ready',
            with: [
                'resignation' => $this->resignation,
                'documentLabel' => $this->documentLabel,
            ],
        );
    }
}
