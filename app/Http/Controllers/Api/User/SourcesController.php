<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SourcesController extends Controller
{
    public ?string $endpoint;

    public ?string $token;

    public \Illuminate\Http\Client\PendingRequest $http;

    public $actions = [];

    public function __construct()
    {
        $this->endpoint = config('services.api_gate.sources_data_url');
        $this->token = request()->user()->external_token;
        $this->http = \Http::timeout(120)->retry(100, 100);
    }

    public function index(Request $request, string $source)
    {
        return $this->getInfoByToken($request, $source);
    }

    public function store(Request $request, string $source)
    {
        $login = $request->input('login');

        $response = $this->http->post($this->endpoint.'addAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ]);

        return response()->json($response->json(), 201);
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

    public function switchAuth(Request $request, string $source)
    {
        $currentAuth = $request->input('account.additional.config.services.authMethod');
        $login = $request->input('account.login');
        $phone = $request->input('phone');

        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'phone' => $phone,
        ];

        if ($currentAuth === 'code') {
            $this->http->post($this->endpoint.'disablePhoneAuth', $data)->json();
            $this->actions[] = 'disable-phone-auth';
        }

        if ($currentAuth === 'qr') {
            $this->http->post($this->endpoint.'enablePhoneAuth', $data)->json();
            $this->actions[] = 'enable-phone-auth';
        }

        return $this->getInfo($request, $source);
    }

    public function switchState(Request $request, string $source)
    {
        // $account = $this->getInfo($request, $source);
        $account = $request->input('account');

        if (\Arr::get($account, 'step.value') === 3) { // Выключение TODO: Release logic with telegram
            // Turn on
            $this->setState($request, $source);
        } else {
            // Turn Force Stop
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

    public function getInfoByToken(Request $request, string $source)
    {
        $login = $request->input('login');

        if ($login === 'undefined') {
            return response()->json([], 201);
        }

        return $this->http->post($this->endpoint.'getInfoByToken', [
            'token' => $this->token,
            'source' => $source,
        ])->json();

    }

    public function solveChallenge(Request $request, string $source)
    {
        $login = $request->input('login');
        $code = $request->input('code');

        if ($login === 'undefined') {
            return response()->json([], 201);
        }

        $result = $this->http->post($this->endpoint.'solveChallenge', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'code' => $code,
        ]);

        $result = $result->json();

        if ($result['status'] === 'error' && \Str::contains($result['error']['message'], 'expired')) {
            $result = $this->http->post($this->endpoint.'solveChallenge', [
                'token' => $this->token,
                'source' => $source,
                'login' => $login,
                'code' => $code,
            ]);
        }

        return response()
            ->json($result, 201);

    }

    public function clearSessionAction(Request $request, string $source)
    {
        $this->clearSession($request, $source);

        return $this->getInfo($request, $source);
    }
    public function getInfo(Request $request, string $source)
    {
        $login = $request->input('account.login');

        $account = $this->http->post($this->endpoint.'getInfo', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ])->json();
        $this->actions[] = 'get-info';
        $account['actions'] = $this->actions;

        if (\Arr::get($account, 'step.value') === null) {// This is turn off account
            return $account;
        }

        if (\Arr::get($account, 'step.value') === 2.2) {
            // TODO: need Qr code
            $account['qr_code'] = $this->getQR($request, $source);
        }

        if (\Arr::get($account, 'step.value') === 2.22) {
            // TODO: need Phone code and check telegram
            $account['phone_code'] = $this->getAuthCode($request, $source);
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

    public function setState(Request $request, string $source, $state = true)
    {
        $login = $request->input('account.login');

        $data = [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'setState' => $state,
        ];

        $this->actions[] = 'set-state-'.$state;
        $this->http->post($this->endpoint.'setState', $data)->json();
    }

    public function show($id) {}

    public function update(Request $request, $id) {}

    public function destroy(Request $request, string $source)
    {
        $login = $request->input('login');

        $response = $this->http->post($this->endpoint.'deleteAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ]);
        $this->actions[] = 'delete-account';

        return $this->getInfo($request, $source);
    }
}
