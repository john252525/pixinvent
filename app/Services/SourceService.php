<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;

class SourceService
{
    protected ?string $endpoint;

    protected ?string $token;

    /**
     * Timeout duration in seconds.
     */
    protected int $timeout = 3600;

    /**
     * Number of retry attempts.
     */
    protected int $retryTimes = 100;

    /**
     * Delay between retries in milliseconds.
     */
    protected int $retryDelay = 100;

    private PendingRequest $http;

    public array $actions = [];

    public function __construct()
    {
        $this->endpoint = config('services.api_gate.sources_data_url');
        $this->token = request()->user()->external_token;
        $this->http = \Http::timeout($this->timeout)->retry($this->retryTimes, $this->retryDelay);
    }

    public function index(Request $request, string $source)
    {
        return $this->getInfoByToken($request, $source);
    }

    public function store(Request $request, string $source)
    {
        $login = $request->input('account.login');
        $this->actions[] = 'add-account';

        $this->http->post($this->endpoint.'addAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ]);

        $this->setState($request, $source);

        return $this->getInfo($request, $source);
    }

    public function forceStopAction(Request $request, string $source)
    {
        $this->forceStop($request, $source);

        return $this->getInfo($request, $source);
    }

    public function setStateAction(Request $request, string $source)
    {
        $state = $request->input('state');
        $this->setState($request, $source, $state);

        return $this->getInfo($request, $source);
    }

    public function forceStop(Request $request, string $source)
    {
        $login = $request->input('account.login');
        $this->actions[] = 'force-stop';

        $this->http->post($this->endpoint.'forceStop', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json();
    }

    /**
     * @throws ConnectionException
     */
    public function switchAuth(Request $request, string $source)
    {
        $currentAuth = $request->input('account.additional.config.services.authMethod');
        $type = $request->input('type');

        $login = $request->input('account.login');
        $phone = $request->input('phone');

        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'phone' => $phone,
        ];

        if ($type === 'qr') {
            $this->http->post($this->endpoint.'disablePhoneAuth', $data)->json();
            $this->actions[] = 'disable-phone-auth';
        } elseif ($type === 'code') {
            $this->http->post($this->endpoint.'enablePhoneAuth', $data)->json();
            $this->actions[] = 'enable-phone-auth';
        } elseif ($currentAuth === 'qr') {
            $this->http->post($this->endpoint.'disablePhoneAuth', $data)->json();
            $this->actions[] = 'disable-phone-auth';
        } elseif ($currentAuth === 'code') {
            $this->http->post($this->endpoint.'enablePhoneAuth', $data)->json();
            $this->actions[] = 'enable-phone-auth';
        }

        return $this->getInfo($request, $source);
    }

    /**
     * @throws ConnectionException
     */
    public function switchState(Request $request, string $source)
    {
        $account = $request->input('account');
        $state = $request->input('state');

        if ($state || $state === false) {
            $this->setState($request, $source, $state);
        }
        if (\Arr::get($account, 'step.value') === 100) {
            $this->setState($request, $source);
        } elseif (\Arr::get($account, 'step.value') === 200) {
            $this->setState($request, $source, false);
        }

        return $this->getInfo($request, $source);
    }

    public function getQR(Request $request, string $source)
    {
        $login = $request->input('account.login');
        $this->actions[] = 'get-qr';

        return $this->http->post($this->endpoint.'getQr', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json('value');
    }

    /**
     * @throws ConnectionException
     */
    public function updateWebhookUrls(Request $request, string $source)
    {
        $login = $request->input('account.login');
        $webhookUrls = $request->input('account.webhookUrls');

        $this->actions[] = 'update-webhook-urls';

        $this->http->post($this->endpoint.'updateAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'webhookUrls' => $webhookUrls,
        ])->json('value');

        return $this->getInfo($request, $source);
    }

    public function getInfoByToken(Request $request, string $source)
    {
        $accounts = $this->http->post($this->endpoint.'getInfoByToken', [
            'token' => $this->token,
            'source' => $source,
        ])->json();

        $this->actions[] = 'get-info-by-token';
        $accounts['actions'] = $this->actions;

        return $accounts;
    }

    /**
     * @throws ConnectionException
     */
    public function solveChallenge(Request $request, string $source)
    {
        $login = $request->input('login');
        $code = $request->input('code');

        $this->actions[] = 'solve-challenge';

        return $this->http->post($this->endpoint.'solveChallenge', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'code' => $code,
        ])->json();
    }

    public function clearSessionAction(Request $request, string $source)
    {
        $this->clearSession($request, $source);

        return $this->getInfo($request, $source);
    }

    /**
     * @throws ConnectionException
     */
    public function getInfo(Request $request, string $source)
    {
        $login = $request->input('account.login');

        $account = $this->http
            ->post($this->endpoint.'getInfo', [
                'token' => $this->token,
                'source' => $source,
                'login' => $login,
            ])
            ->json();

        $this->actions[] = 'get-info';
        $account['actions'] = $this->actions;

        if (\Arr::get($account, 'step.value') === null) {
            $account['actions'] = $this->actions;

            return $account;
        }

        if (\Arr::get($account, 'step.value') === 2.2) {
            $account['qr_code'] = $this->getQR($request, $source);
            $account['actions'] = $this->actions;
        }

        if (\Arr::get($account, 'step.value') === 2.22) {
            $account['phone_code'] = $this->getAuthCode($request, $source);
            $account['actions'] = $this->actions;
        }

        return $account;
    }

    public function clearSession(Request $request, string $source)
    {
        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $request->input('account.login'),
        ];

        $this->actions[] = 'clear-session';
        $this->http->post($this->endpoint.'clearSession', $data)->json('authCode');
    }

    public function getAuthCode(Request $request, string $source)
    {
        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $request->input('account.login'),
        ];

        $this->actions[] = 'get-auth-code';

        return $this->http->post($this->endpoint.'getAuthCode', $data)->json('authCode');
    }

    public function setState(Request $request, string $source, $state = true): void
    {
        $login = $request->input('account.login');

        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'setState' => $state,
        ];

        $stateAction = $state ? 'on' : 'off';
        $this->actions[] = "set-state-$stateAction";
        $this->http->post($this->endpoint.'setState', $data)->json();
    }

    public function destroy(Request $request, string $source)
    {
        $login = $request->input('account.login');
        $this->actions[] = 'delete-account';

        $this->http->post($this->endpoint.'deleteAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json();

        return $this->index($request, $source);
    }

    private function prepareResponse($response)
    {
        return response()->json($response->json(), 201);
    }
}
