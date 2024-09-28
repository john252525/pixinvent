<?php

namespace App\Http\Integrations\ExternalTokenGate\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ExternalTokenGate extends Request
{
    public function __construct(
        protected ?int $user_id = null,
    ) {
        // TODO: Check request user instance
        $this->user_id = $user_id ?? request()->user()->id;
    }

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '';
    }

    /**
     * The query parameters for the request
     */
    public function defaultQuery(): array
    {
        return [
            'user_id' => $this->user_id,
            'key' => config('services.api_gate.external_token_key'),
        ];
    }
}
