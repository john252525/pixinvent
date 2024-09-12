<?php

namespace App\Notifications;

use App\Models\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification // implements ShouldQueue
{
    // use Queueable;
    public function __construct(
        public string $webhook,
    ) {}

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        // $url = url('/invoice/');
        return TelegramMessage::create()
            ->content($this->webhook);

        // Optional recipient user id.
        // ->to(-1002431671559)

        // ->line('Your invoice has been *PAID*')
        // ->lineIf(true, 'Amount paid: 1200')

        // Markdown supported.
        // ->content(json_encode($this->webhook));

        // (Optional) Blade template for the content.
        // ->view('notification', ['url' => $url])

        // (Optional) Inline Buttons
        // ->button('View Invoice', $url)
        // ->button('Download Invoice', $url)
        // (Optional) Inline Button with callback. You can handle callback in your bot instance
        // ->buttonWithCallback('Confirm', 'confirm_invoice '. 1);
    }
}
