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
    public array $messages = [];

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

    /**
     * @throws ConnectionException
     */
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

    /**
     * @throws ConnectionException
     */
    public function setStateAction(Request $request, string $source)
    {
        $source === 'telegram'
        ? $this->setTelegramState($request, 'telegram')
        : $this->setState($request, $source);

        return $this->getInfo($request, $source);
    }

    public function forceStop(Request $request, string $source): void
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

    public function getQR(Request $request, string $source)
    {
        $login = $request->input('account.login');

        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ];

        $this->actions[] = 'get-qr';
        return $this->http->post($this->endpoint.'getQr', $data)->json('value');
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
        $login = $request->input('account.login');
        $code = $request->input('code');

        $this->actions[] = 'solve-challenge';

        return $this->http->post($this->endpoint.'solveChallenge', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'code' => $code,
        ])->json();
    }

    /**
     * @throws ConnectionException
     */
    public function twoFactorAuth(Request $request, string $source)
    {
        $login = $request->input('account.login');
        $code = $request->input('code');

        $this->actions[] = 'two-factor-auth';

        return $this->http->post($this->endpoint.'twoFactorAuth', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'code' => $code,
        ])->json();
    }

    /**
     * @throws ConnectionException
     */
    public function sendTelegramCode(Request $request, string $source)
    {
        $login = $request->input('account.login');

        $this->actions[] = 'send-telegram-code';

        $results['forceStop1'] = $this->http->post($this->endpoint.'forceStop', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'setState' => false,
        ])->json();

        /*$results['getNewProxy2'] = $this->http->post($this->endpoint.'getNewProxy', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json();


        $results['clearSession3'] = $this->http->post($this->endpoint.'clearSession', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json();

        $results['enablePhoneAuth4'] = $this->http->post($this->endpoint.'enablePhoneAuth', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json();

        $results['forceStop5'] = $this->http->post($this->endpoint.'forceStop', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'setState' => false,
        ])->json();*/


        $results['setState6'] = $this->http->post($this->endpoint.'setState', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'setState' => true,
        ])->json();


        return [...$this->http->post($this->endpoint.'getInfo', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json(),
            'results' => $results,
        ];
    }

    /**
     * @throws ConnectionException
     */
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
        $action = $request->input('action');

        $account = $this->http
            ->post($this->endpoint.'getInfo', [
                'token' => $this->token,
                'source' => $source,
                'login' => $login,
            ])
            ->json();

        $this->actions[] = 'get-info';
        $account['actions'] = $this->actions;

        if (\Arr::get($account, 'step.value') === null && $action !== 'get-qr-code') {
            return $account;
        }

        if (\Arr::get($account, 'step.value') === 2.2) {
            $account['qr_code'] = $this->getQR($request, $source);
            $account['actions'] = $this->actions;
        }

        if (\Arr::get($account, 'step.value') === 0.1) {
            $this->forceStop($request, $source);
            $this->setStateAction($request, $source);
            $account['qr_code'] = $this->getQR($request, $source);
            $account['actions'] = $this->actions;
        }

        if (\Arr::get($account, 'step.value') === 2.3) {
            $this->forceStop($request, $source);
            $this->setStateAction($request, $source);
            $account['qr_code'] = $this->getQR($request, $source);
            $account['actions'] = $this->actions;
        }

        if (\Arr::get($account, 'step.value') === 2.22) {
            $account['phone_code'] = $this->getAuthCode($request, $source);
            $account['actions'] = $this->actions;
        }

        $account['messages'] = $this->messages;

        return $account;
    }

    /**
     * @throws ConnectionException
     */
    public function clearSession(Request $request, string $source): void
    {
        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $request->input('account.login'),
        ];

        $this->actions[] = 'clear-session';
        $this->http->post($this->endpoint.'clearSession', $data)->json('authCode');
    }

    /**
     * @throws ConnectionException
     */
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

    /**
     * @throws ConnectionException
     */
    public function switchState(Request $request, string $source)
    {
        if ($source === 'telegram') {
            $this->setTelegramState($request, $source);
        } else {
            $this->setState($request, $source);
        }

        return $this->getInfo($request, $source);
    }


    /**
     * @throws ConnectionException
     */
    public function setState(
        Request $request,
        string $source,
    ): void {
        $login = $request->input('account.login');
        $state = $request->input('state') ?? $request->input('account.step', false) === null;

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

    /**
     * @throws ConnectionException
     */
    public function setTelegramState(
        Request $request,
        string $source,
    ): void {
        $login = $request->input('account.login');
        $state = $request->input('state') ?? $request->input('action', false) === 'get-qr-code';
        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'setState' => $state,
            'qrLogin' => true,
        ];
        // dd($data, $request->input('state'), $request->input('account.step', false));
        $stateAction = $state ? 'on' : 'off';
        $this->actions[] = "set-telegram-state-$stateAction";

        $result = $this->http->post($this->endpoint.'setState', $data)->json();

        if (\Arr::get($result, 'status') === 'error') {
            $this->messages[] = \Arr::get($result, 'error.message');
            if (\Arr::get($result, 'error.message') === 'Such account is already exist') {
                $this->forceStop($request, 'telegram');
                $this->setTelegramState($request, 'telegram');
            }
            if (\Arr::get($result, 'error.message') === "'MyTelegramClient' object has no attribute 'task'") {
                $this->forceStop($request, 'telegram');
            }
        }
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
