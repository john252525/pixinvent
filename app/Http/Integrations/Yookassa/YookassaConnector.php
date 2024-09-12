<?php

namespace App\Http\Integrations\Yookassa;

use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class YookassaConnector extends Connector
{
    use AcceptsJson;

    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(config('services.yookassa.id'), config('services.yookassa.secret'));
    }

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.yookassa.ru/v3';
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
