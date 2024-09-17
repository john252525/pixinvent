<?php

namespace App\Http\Integrations\SourcesApi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\HasTimeout;

class GetInfoByToken extends Request
{
    use HasTimeout;

    protected int $connectTimeout = 60;

    protected int $requestTimeout = 120;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    public function __construct(
        protected ?string $token = null,
        protected readonly string $source,
        protected readonly bool $skipDetails = false,
    ) {
        $this->token = $token ?? request()->user()->external_token;
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/getInfoByToken';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store,no-cache, must-revalidate, post-check=1, pre-check=1',
            'Accept' => 'application/json',
        ];
    }

    protected function defaultQuery(): array
    {
        return [
            'token' => $this->token,
            'source' => $this->source,
            'skipDetails' => $this->skipDetails,
        ];
    }
}
