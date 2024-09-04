<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Http\Integrations\Ukassa\Requests\CreatePayment;
use App\Http\Integrations\Ukassa\UkassaConnector;
use Illuminate\Http\Request;

class UkassaController extends Controller
{
    public function __construct(
        private $ukassa = new UkassaConnector,
    ) {}

    public function create(Request $request)
    {
        $payment = new CreatePayment(
            amount: $request->input('amount'),
            description: 'Test payment',
        );

        $result = $this->ukassa->send($payment);

        return $result->json();
    }
}
