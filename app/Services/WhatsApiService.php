<?php

namespace App\Services;

use App\Http\Requests\Services\WhatsApi\EditRequest;
use App\Http\Requests\Services\WhatsApi\StoreRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class WhatsApiService
{
    public function __construct(
        private Client $client,
        private string $endpoint,
        private string $token
    )
    {
    }

    public function index(): object
    {
        $response = $this->client->get($this->endpoint . 'list?token=' . $this->token);

        return json_decode($response->getBody());
    }

    public function getMessages(int $id): object
    {
        $response = $this->client->get($this->endpoint . "view/{$id}/?token={$this->token}");

        return json_decode($response->getBody());
    }

    public function store(StoreRequest $request): object
    {
        $payload = [
            'multipart' => [
                [
                    'name' => 'token',
                    'contents' => $this->token,
                ],
                [
                    'name' => 'name',
                    'contents' => $request->name,
                ],
                [
                    'name' => 'base',
                    'contents' => $request->base,
                ],
                [
                    'name' => 'text',
                    'contents' => $request->text,
                ],
                [
                    'name' => 'ph_col',
                    'contents' => $request->ph_col,
                ],
                [
                    'name' => 'days',
                    'contents' => $request->days,
                ],
                [
                    'name' => 'time_from',
                    'contents' => $request->time_from,
                ],
                [
                    'name' => 'time_to',
                    'contents' => $request->time_to,
                ],
                [
                    'name' => 'timezone',
                    'contents' => $request->timezone,
                ],
                [
                    'name' => 'delay_from',
                    'contents' => $request->delay_from,
                ],
                [
                    'name' => 'delay_to',
                    'contents' => $request->delay_to,
                ],
                [
                    'name' => 'uniq',
                    'contents' => $request->uniq,
                ],
                [
                    'name' => 'exist',
                    'contents' => $request->exist,
                ],
                [
                    'name' => 'random',
                    'contents' => $request->random,
                ],
                [
                    'name' => 'cascade',
                    'contents' => $request->cascade,
                ],
            ]
        ];

        $filesData = $this->getFilesData($request);

        foreach (['file_base', 'file_attach'] as $paramName) {
            if (Arr::exists($filesData, $paramName)) {
                $payload['multipart'][] = [
                    'name' => $paramName,
                    'contents' => $filesData[$paramName]['resource'],
                    'filename' => $filesData[$paramName]['name'],
                ];
            }
        }

        $response = $this->client->post($this->endpoint . 'new/', $payload);

        return json_decode($response->getBody());
    }

    public function edit(EditRequest $request): object
    {
        $response = $this->client->post(
            $this->endpoint . "edit/{$request->id}/",
            [
                'form_params' => [
                    'token' => $this->token,
                    'days' => $request->days,
                    'time_from' => $request->time_from,
                    'time_to' => $request->time_to,
                    'delay_from' => $request->delay_from,
                    'delay_to' => $request->delay_to,
                    'uniq' => $request->uniq,
                    'exist' => $request->exist,
                    'random' => $request->random,
                    'cascade' => $request->cascade,
                ],
            ]
        );

        return json_decode($response->getBody());
    }

    public function editStatus(int $id, int $stateID): object
    {
        $response = $this->client->post(
            $this->endpoint . "state/{$id}/{$stateID}/",
            [
                'form_params' => [
                    'token' => $this->token,
                ],
            ]
        );

        return json_decode($response->getBody());
    }

    public function destroy(int $id): object
    {
        $response = $this->client->post(
            $this->endpoint . "delete/{$id}/",
            [
                'form_params' => [
                    'token' => $this->token,
                ],
            ],
        );

        $response = json_decode($response->getBody());

        if ($response->ok === false || isset($response->error)) {
            return response()->json('error', 400);
        }

        return $response;
    }

    private function getFilesData(Request $request): array
    {
        $filesData = [];

        if (!empty($files = $request->allFiles())) {
            foreach ($files as $paramName => $file) {
                $fileName = Str::random() . '.' . $request->file($paramName)->getClientOriginalExtension();
                $file->storeAs('uploads/files', $fileName);
                $filesData[$paramName] = [
                    'name' => $fileName,
                    'resource' => Storage::readStream('uploads/files/'. $fileName),
                ];
            }
            Storage::deleteDirectory('uploads/files');
        }

        return $filesData;
    }
}
