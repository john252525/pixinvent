<?php

namespace App\Http\Integrations\Ukassa;

use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class UkassaConnector extends Connector
{
    use AcceptsJson;

    public function __construct(
        public ?string $ukassa_id = null,
        public ?string $ukassa_secret = null,
        public ?string $idempotence_key = null,
    ) {
        $this->ukassa_id = $ukassa_id ?? config('services.ukassa.id');
        $this->ukassa_secret = $this->ukassa_secret ?? config('services.ukassa.secret');
        $this->idempotence_key = $idempotence_key ?? \Str::uuid();
    }

    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator($this->ukassa_id, $this->ukassa_secret);
    }

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return 'https://api.yookassa.ru/v3';
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Idempotence-Key' => $this->idempotence_key,
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
