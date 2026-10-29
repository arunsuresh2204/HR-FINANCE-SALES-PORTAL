<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

/**
 * Sends mail through PHP's native mail() function rather than SMTP or a
 * local sendmail binary. mail() still depends on the server's own outbound
 * mail setup (sendmail_path in php.ini on Linux, or the SMTP/smtp_port ini
 * directives on Windows) — this transport doesn't talk to a mail server
 * itself, it just hands the message to whatever PHP is configured to use.
 */
class PhpMailTransport extends AbstractTransport
{
    public function __toString(): string
    {
        return 'phpmail://default';
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $toAddresses = $email->getTo();

        if (empty($toAddresses)) {
            throw new TransportException('Cannot send a message with PHP mail(): no "To" recipient.');
        }

        $to = implode(', ', array_map(fn (Address $address) => $address->toString(), $toAddresses));
        $subject = (string) $email->getSubject();

        $headers = $email->getPreparedHeaders();
        $headers->remove('To');
        $headers->remove('Subject');

        $body = $email->getBody();

        foreach ($body->getPreparedHeaders()->all() as $header) {
            $headers->add($header);
        }

        $headersString = rtrim($headers->toString(), "\r\n");

        if (! $this->dispatch($to, $subject, $body->bodyToString(), $headersString)) {
            throw new TransportException('PHP mail() returned false — the message was not accepted for delivery. Check the server\'s sendmail_path / local mail transfer agent configuration.');
        }
    }

    /**
     * Isolated so it can be stubbed in tests without a working local MTA.
     */
    protected function dispatch(string $to, string $subject, string $body, string $headers): bool
    {
        return @mail($to, $subject, $body, $headers);
    }
}
