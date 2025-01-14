<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class IndiparserController extends Controller
{
    public function getSettings(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|min:1',
        ]);

        $userID = $request->user_id;
        $data = $request->all();

        $client = new Client();
        try {
            $response = $client->post("https://indiparser.apitter.com/?user_id={$userID}", [
                'json' => $data,
                'verify' => false,  // TODO Без проверки SSL сертификата. Настроить.
            ]);
            return (string) $response->getBody();
            //return response()->json(json_decode($response->getBody()->getContents(), true, 20, JSON_UNESCAPED_UNICODE));
        } catch (RequestException $e) {
            return response()->json([
                'error' => 'Error during request for fetching settings',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|min:1',
            'text' => 'required|string|min:1',
        ]);

        $userID = $request->user_id;
        $data = $request->all();

        $client = new Client();
        try {
            $response = $client->post("https://indiparser.apitter.com/?user_id={$userID}", [
                'json' => $data,
                'verify' => false // TODO Без проверки SSL сертификата. Настроить.
            ]);
            return (string) $response->getBody();
            //return response()->json(json_decode($response->getBody()->getContents(), true, 20, JSON_UNESCAPED_UNICODE));
        } catch (RequestException $e) {
            return response()->json([
                'error' => 'Error during request for saving settings',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
