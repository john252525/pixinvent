<?php

namespace App\Mail\Driver;

use GuzzleHttp\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class WebhookDriver extends AbstractTransport
{
    public function __construct(
        private string $url
    )
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $data = [
            'from' => mail_from_domain(),
            'to' => collect($email->getTo())->map(function (Address $email) {
                return $email->getAddress();
            })->first(),
            'html' => $email->getHtmlBody(),
        ];

        $client = new Client();

        $client->post($this->url, [
            'json' => $data,
        ]);
    }

    public function __toString(): string
    {
        return 'webhook';
    }
}
