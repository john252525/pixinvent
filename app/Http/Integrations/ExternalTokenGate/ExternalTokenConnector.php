<?php

namespace App\Http\Integrations\ExternalTokenGate;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class ExternalTokenConnector extends Connector
{
    use AcceptsJson;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('services.api_gate.external_token_url');
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
