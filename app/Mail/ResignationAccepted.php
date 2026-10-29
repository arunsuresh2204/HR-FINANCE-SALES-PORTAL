<?php

namespace App\Mail;

use App\Models\Resignation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResignationAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Resignation $resignation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Resignation Has Been Accepted — '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.resignation-accepted',
            with: ['resignation' => $this->resignation],
        );
    }
}
