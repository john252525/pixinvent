<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SourcesController extends Controller
{
    public ?string $endpoint;

    public ?string $token;

    public function __construct()
    {
        $this->endpoint = config('services.api_gate.sources_data_url');
        $this->token = request()->user()->external_token;
    }

    public function index(Request $request)
    {

        if ($request->input('sortBy')) {
            return response()->json(['clients' => []]);
        }

        $source = $request->input('source');

        /*
        $sourcesApi = new SourcesApiConnector;
        $getInfoByTokenRequest = new GetInfoByToken(
            token: $token,
            source: $source,
        );
        $response = $sourcesApi->send($getInfoByTokenRequest);
        */

        $response = \Http::post($this->endpoint.'getInfoByToken', [
            'token' => $this->token,
            'source' => $source,
        ]);

        return $response->ok() ? response()->json($response->json(), 201) : null;
    }

    public function store(Request $request, string $source)
    {
        $login = $request->input('login');
        $activateSource = $request->input('setStatus');

        $response = \Http::post($this->endpoint.'addAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ]);

        if ($response->ok()) {
            if ($activateSource) {
                $response = \Http::post($this->endpoint.'setState', [
                    'token' => $this->token,
                    'source' => $source,
                    'login' => $login,
                    'setState' => true,
                    'qrLogin' => true,
                ]);
            }
        }

        return response()->json($response->json(), 201);
    }

    public function getQR(Request $request, string $source)
    {
        $login = $request->input('loginQR');

        if ($login === 'undefined') {
            return response()->json([], 201);
        }

        if ($source === 'whatsapp') {

            $qr = \Http::post($this->endpoint.'getQr', [
                'token' => $this->token,
                'source' => $source,
                'login' => $login,
            ]);
        } elseif ($source === 'telegram') {
            $qr = \Http::post($this->endpoint.'getQr', [
                'token' => $this->token,
                'source' => $source,
                'login' => $login,
            ]);
            dd($qr->json());
        }

        return response()->json($qr->json('value'), 201);
    }

    public function getInfoByToken(Request $request, string $source)
    {
        $login = $request->input('login');

        if ($login === 'undefined') {
            return response()->json([], 201);
        }

        $state = \Http::post($this->endpoint.'getInfoByToken', [
            'token' => $this->token,
            'source' => $source,
        ]);
        return response()
            ->json(collect($state->json('clients'))->first(function ($value, $key) use ($login) {
                return $value['login'] === $login;
            }), 201);

    }

    public function solveChallenge(Request $request, string $source)
    {
        $login = $request->input('login');
        $code = $request->input('code');

        if ($login === 'undefined') {
            return response()->json([], 201);
        }

        $result = \Http::post($this->endpoint.'solveChallenge', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
            'code' => $code,
        ]);

        $result = $result->json();

        if ($result['status'] === 'error' && \Str::contains($result['error']['message'], 'expired')) {
            $result = \Http::post($this->endpoint.'solveChallenge', [
                'token' => $this->token,
                'source' => $source,
                'login' => $login,
                'code' => $code,
            ]);
        }

        return response()
            ->json($result, 201);

    }

    public function show($id) {}

    public function update(Request $request, $id) {}

    public function destroy(Request $request, string $source)
    {
        $login = $request->input('login');

        $response = \Http::post($this->endpoint.'deleteAccount', [
            'token' => $this->token,
            'source' => $source,
            'login' => $login,
        ]);

        return response()->json($response->json(), 201);
    }
}
