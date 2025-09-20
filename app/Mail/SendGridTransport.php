<?php

namespace App\Mail;

use Illuminate\Support\Facades\Log;
use SendGrid;
use SendGrid\Mail\Mail as SendGridMail;
use SendGrid\Mail\TypeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SendGridTransport extends AbstractTransport
{
    protected $sendgrid;

    public function __construct()
    {
        parent::__construct(new EventDispatcher());
        $this->sendgrid = new SendGrid(config('mail.mailers.sendgrid.api_key'));
    }

    public function __toString(): string
    {
        return 'sendgrid';
    }

    protected function doSend(SentMessage $message): void
    {
        try {
            $originalMessage = $message->getOriginalMessage();
            $envelope = $message->getEnvelope();
            
            $sendgridMail = new SendGridMail();
            
            // Set from
            $from = $envelope->getSender();
            $sendgridMail->setFrom($from->getAddress(), $from->getName());

            // Set to recipients
            foreach ($envelope->getRecipients() as $recipient) {
                $sendgridMail->addTo($recipient->getAddress(), $recipient->getName());
            }

            // Set subject
            $sendgridMail->setSubject($originalMessage->getSubject());

            // Set content
            if ($originalMessage->getHtmlBody()) {
                $sendgridMail->addContent("text/html", $originalMessage->getHtmlBody());
            }
            
            if ($originalMessage->getTextBody()) {
                $sendgridMail->addContent("text/plain", $originalMessage->getTextBody());
            }

            // Send the email
            $response = $this->sendgrid->send($sendgridMail);
            
            if ($response->statusCode() >= 400) {
                Log::error('SendGrid API Error', [
                    'status_code' => $response->statusCode(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                
                throw new \Exception('SendGrid API Error: ' . $response->body());
            }

            Log::info('Email sent successfully via SendGrid', [
                'status_code' => $response->statusCode(),
                'message_id' => $response->headers()['X-Message-Id'] ?? null
            ]);

        } catch (TypeException $e) {
            Log::error('SendGrid Type Exception', ['error' => $e->getMessage()]);
            throw new \Exception('SendGrid Type Exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('SendGrid Send Exception', ['error' => $e->getMessage()]);
            throw new \Exception('SendGrid Send Exception: ' . $e->getMessage());
        }
    }
}
