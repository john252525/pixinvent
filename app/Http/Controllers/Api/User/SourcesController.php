<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Integrations\SourcesApi\Requests\GetInfoByToken;
use App\Http\Integrations\SourcesApi\SourcesApiConnector;
use Illuminate\Http\Request;

class SourcesController extends Controller
{
    public function index(Request $request)
    {

        $source = $request->input('source');
        $token = $request->user()->external_token;
        $endpoint = config('services.api_gate.sources_data_url').'getInfoByToken';

        /*
        $sourcesApi = new SourcesApiConnector;
        $getInfoByTokenRequest = new GetInfoByToken(
            token: $token,
            source: $source,
        );
        $response = $sourcesApi->send($getInfoByTokenRequest);
        */

        $response = \Http::post($endpoint, [
            'token' => $token,
            'source' => $source,
        ]);

        return $response->ok() ? response()->json($response->json(), 201) : null;
    }

    public function store(Request $request) {}

    public function show($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
