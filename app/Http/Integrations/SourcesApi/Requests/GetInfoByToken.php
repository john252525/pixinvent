<?php

namespace App\Http\Integrations\SourcesApi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetInfoByToken extends Request
{
    public function __construct(
        protected ?string $token = null,
        protected string $source = 'all',
        protected bool $skipDetails = true,
    ) {
        $this->token = $token ?? request()->user()->external_token;
    }

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::POST;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/getInfoByToken';
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
