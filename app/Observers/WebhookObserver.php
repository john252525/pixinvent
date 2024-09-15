<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Webhook;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Carbon;

class WebhookObserver
{
    public User $user;

    public function __construct()
    {
        $this->user = User::find(1);
    }

    public function created(Webhook $webhook): void
    {

        if (! $transaction = Transaction::where('payment_id', \Arr::get($webhook->payload, 'object.id'))->first()) {
            $message = "*Оплата не найдена*\nYookassaId: ".\Arr::get($webhook->payload, 'object.id')."\nСумма: ".\Arr::get($webhook->payload, 'object.amount.value').' рублей';
            \Notification::route('telegram', -1002431671559)->notify(new TelegramNotification($message));
            return;
        }
        $transaction->update([
            'status' => \Arr::get($webhook->payload, 'object.status'),
            'income_amount' => put_money(\Arr::get($webhook->payload, 'object.income_amount.value')),
            'payment_method' => \Arr::get($webhook->payload, 'object.payment_method'),
            'refunded_amount' => \Arr::get($webhook->payload, 'object.refunded_amount'),
            'metadata' => [
                ...$transaction->metadata,
                ...[
                    'test' => \Arr::get($webhook->payload, 'object.test'),
                    'paid' => \Arr::get($webhook->payload, 'object.paid'),
                    'refundable' => \Arr::get($webhook->payload, 'object.refundable'),
                ]],
        ]);

        $message = $transaction->status === 'canceled'
            ? "*Оплата отменена*\nПользователь ID: ".$transaction->user_id."\nСумма: ".\Arr::get($webhook->payload, 'object.amount.value').' рублей'
            : "*Оплата совершена*\nПользователь ID: ".$transaction->user_id."\nСумма: ".\Arr::get($webhook->payload, 'object.amount.value')." рублей\nКомиссия удержана: ".\Arr::get($webhook->payload, 'object.amount.value') - \Arr::get($webhook->payload, 'object.income_amount.value').' рублей';


        \Notification::route('telegram', -1002431671559)->notify(new TelegramNotification($message));
    }

    public function updated(Webhook $webhook): void {}

    public function deleted(Webhook $webhook): void {}

    public function restored(Webhook $webhook): void {}

    private function formatArray($array, $indent = 0)
    {
        $result = '';
        $space = str_repeat(' ', $indent);
        foreach ($array as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at', 'captured_at'])) {
                $value = (string) Carbon::parse($value)->format('d F H:i');
            }
            if (is_array($value)) {
                $result .= $space.$key.":\n".$this->formatArray($value, $indent + 4);
            } else {
                // Убираем все символы '*'
                $value = str_replace('*', '', $value);
                $result .= $space.$key.': '.($value === true ? 'true' : ($value === false ? 'false' : ($value === null ? 'null' : $value)))."\n";
            }
        }

        return $result;
    }
}
