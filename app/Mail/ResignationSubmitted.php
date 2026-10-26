<?php

namespace App\Mail;

use App\Models\Resignation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResignationSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Resignation $resignation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resignation Notice — '.$this->resignation->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.resignation-submitted',
            with: ['resignation' => $this->resignation],
        );
    }
}
