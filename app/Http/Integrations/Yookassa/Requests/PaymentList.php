<?php

namespace App\Http\Integrations\Yookassa\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class PaymentList extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/payments';
    }
}
