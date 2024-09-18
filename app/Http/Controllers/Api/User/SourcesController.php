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
        dd($response->json());
        if ($response->ok()) {
            if ($activateSource) {
                $response = \Http::post($this->endpoint.'setState', [
                    'token' => $this->token,
                    'source' => $source,
                    'login' => $login,
                    'setState' => true,
                ]);
            }
        }

        return response()->json($response->json(), 201);
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
