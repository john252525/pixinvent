<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Services\SourceService;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SourcesController extends Controller
{
    private SourceService $sourceService;

    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }

    /**
     * Display a listing of the sources.
     *
     * @return mixed
     */
    public function index(Request $request, string $source)
    {
        return $this->sourceService->index($request, $source);
    }

    /**
     * Store a newly created source.
     *
     * @return mixed
     */
    public function store(Request $request, string $source)
    {
        return $this->sourceService->store($request, $source);
    }

    /**
     * Store a newly created source.
     *
     * @return mixed
     */
    public function addAccount(Request $request)
    {
        $result = $this->sourceService->addAccount($request);

        return response()->json([
            ...$result,
            'success' => true,
            'message' => $result['message'] ?? 'Account added successfully.',
        ], 201);
    }

    /**
     * Force stop the specified source action.
     *
     * @return mixed
     */
    public function forceStopAction(Request $request, string $source)
    {
        return $this->sourceService->forceStopAction($request, $source);
    }

    /**
     * Set the state of the specified source action.
     *
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function setStateAction(Request $request, string $source)
    {
        return $this->sourceService->setStateAction($request, $source);
    }

    /**
     * Switch authentication for the specified source.
     *
     * @return mixed
     */
    public function switchAuth(Request $request, string $source)
    {
        return $this->sourceService->switchAuth($request, $source);
    }

    /**
     * Switch the state of the specified source.
     *
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function switchState(Request $request, string $source)
    {
        return $this->sourceService->switchState($request, $source);
    }

    /**
     * Get the QR code for the specified source.
     *
     * @return mixed
     */
    public function getQR(Request $request, string $source)
    {
        return $this->sourceService->getQR($request, $source);
    }

    /**
     * Get information by token for the specified source.
     *
     * @return mixed
     */
    public function getInfoByToken(Request $request, string $source)
    {
        return $this->sourceService->getInfoByToken($request, $source);
    }

    /**
     * Solve a challenge for the specified source.
     *
     * @return PromiseInterface|Response|JsonResponse
     *
     * @throws ConnectionException
     */
    public function solveChallenge(Request $request, string $source)
    {
        return $this->sourceService->solveChallenge($request, $source);
    }

    /**
     * @throws ConnectionException
     */
    public function twoFactorAuth(Request $request, string $source)
    {
        return $this->sourceService->twoFactorAuth($request, $source);
    }

    /**
     * @throws ConnectionException
     */
    public function sendTelegramCode(Request $request, string $source)
    {
        return $this->sourceService->sendTelegramCode($request, $source);
    }

    /**
     * Clear the session for the specified source.
     *
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function clearSessionAction(Request $request, string $source)
    {
        return $this->sourceService->clearSessionAction($request, $source);
    }

    /**
     * Get information for the specified source.
     *
     * @return mixed
     *
     * @throws ConnectionException
     */
    public function getInfo(Request $request, string $source)
    {
        return $this->sourceService->getInfo($request, $source);
    }

    /**
     * Remove the specified source.
     *
     * @return mixed
     */
    public function update(Request $request, string $source)
    {
        $type = $request->get('type');

        return match ($type) {
            'update-webhook-urls' => $this->sourceService->updateWebhookUrls($request, $source),
            default => $this->sourceService->getInfo($request, $source)
        };
    }

    /**
     * Remove the specified source.
     *
     * @return mixed
     */
    public function destroy(Request $request, string $source)
    {
        return $this->sourceService->destroy($request, $source);
    }
}
