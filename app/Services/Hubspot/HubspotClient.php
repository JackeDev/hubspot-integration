<?php

namespace App\Services\Hubspot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

abstract class HubspotClient extends HubSpotAuth
{
    private string $apiUrl;

    private string $token;

    private function getApiUrl(): string
    {
        return config('hubspot.base_url');
    }

    private function client()
    {
        return Http::baseUrl($this->getApiUrl())
            ->withToken($this->getToken())
            ->acceptJson()
            ->contentType('application/json')
            ->timeout(15);
    }

    protected function request(
        string $method,
        string $endpoint,
        array $payload = []
    ): Response {

        $response = $this->client()->send(
            $method,
            $endpoint,
            [
                'json' => $payload
            ]
        );

        if ($response->unauthorized()) {

            Log::channel('hubspot')->warning(
                'HubSpot unauthorized request',
                [
                    'endpoint' => $endpoint
                ]
            );

            $this->handleExpiredToken();

            throw new \Exception('HubSpot unauthorized');
        }

        if ($response->failed()) {

            Log::channel('hubspot')->error(
                'HubSpot request failed',
                [
                    'endpoint' => $endpoint,
                    'status'   => $response->status(),
                    'body'     => $response->json(),
                ]
            );

            throw new \Exception('HubSpot request failed');
        }

        return $response;
    }
}