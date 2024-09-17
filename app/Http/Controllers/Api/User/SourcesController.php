<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Integrations\SourcesApi\Requests\GetInfoByToken;
use App\Http\Integrations\SourcesApi\SourcesApiConnector;
use Illuminate\Http\Request;

class SourcesController extends Controller
{
    public function __construct(
        protected $sourcesApi = new SourcesApiConnector,
    ) {}

    public function index(Request $request)
    {
        $source = $request->input('source');

        $getInfoByTokenRequest = new GetInfoByToken(
            source: $source,
        );
        $response = $this->sourcesApi->send($getInfoByTokenRequest);

        return $response->json();
    }

    public function store(Request $request) {}

    public function show($id) {}

    public function update(Request $request, $id) {}

    public function destroy($id) {}
}
