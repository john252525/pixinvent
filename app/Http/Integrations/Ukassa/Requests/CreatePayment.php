<?php

namespace App\Http\Integrations\Ukassa\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreatePayment extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    public function __construct(
        protected readonly string $amount,
        protected readonly string $description = '',
        protected readonly string $currency = 'RUB',
        protected readonly string $type = 'embedded',
        protected readonly bool $capture = true,
    ) {}

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/payments';
    }

    protected function defaultBody(): array
    {

        return [
            'amount' => [
                'value' => $this->amount,
                'currency' => $this->currency,
            ],
            'confirmation' => [
                'type' => $this->type,
            ],
            'capture' => $this->capture,
            'description' => $this->description,
        ];
    }
}
