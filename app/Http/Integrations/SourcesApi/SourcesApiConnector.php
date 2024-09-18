<?php

namespace App\Http\Integrations\SourcesApi;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class SourcesApiConnector extends Connector
{
    use AcceptsJson;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('services.api_gate.sources_data_url');
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}