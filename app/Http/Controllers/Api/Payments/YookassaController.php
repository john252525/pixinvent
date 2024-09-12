<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Integrations\Yookassa\Requests\CreatePayment;
use App\Http\Integrations\Yookassa\YookassaConnector;
use Illuminate\Http\Request;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;

class YookassaController extends Controller
{
    public function __construct(
        private $yookassa = new YookassaConnector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws \JsonException
     */
    public function create(Request $request)
    {
        $idempotence_key = (string) \Str::uuid();
        $amount = $request->input('amount');

        $transaction = $request->user()->transactions()->create([
            'service' => 'yookassa',
            'status' => 'created',
            'amount' => put_money($amount),
        ]);

        $payment = new CreatePayment(
            amount: $amount,
            idempotence_key: $idempotence_key,
            metadata: [
                'user_id' => $request->user()->id,
                'idempotence_key' => $idempotence_key,
            ],
            description: 'Test payment'
        );

        $send = $this->yookassa->send($payment);

        if (! $send->ok()) {
            return response()->json([
                'error' => $send->getSenderException()->getMessage(),
                'status' => $send->getSenderException()->getCode(),
            ], 422);
        }

        $result = $send->json();
        $transaction->update([
            'status' => $result['status'],
            'payment_id' => $result['id'],
            'metadata' => [
                ...$result['metadata'],
                'test' => $result['test'],
                'paid' => $result['paid'],
                'refundable' => $result['refundable'],
            ],
        ]);

        return response()->json(\Arr::get($result, 'confirmation'), 201);
    }
}
