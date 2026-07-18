<?php

namespace App\Services\Composio;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class ComposioClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.composio.key'))
            && filled(config('services.composio.google_calendar_auth_config_id'));
    }

    /**
     * @return array{redirect_url: string, connected_account_id: string}
     */
    public function createGoogleCalendarLink(string $userId, string $callbackUrl): array
    {
        $response = $this->request('POST', '/api/v3/connected_accounts/link', [
            'auth_config_id' => config('services.composio.google_calendar_auth_config_id'),
            'user_id' => $userId,
            'callback_url' => $callbackUrl,
        ]);

        $redirectUrl = $response['redirect_url'] ?? $response['redirectUrl'] ?? null;
        $connectedAccountId = $response['connected_account_id'] ?? $response['connectedAccountId'] ?? null;

        if (! is_string($redirectUrl) || $redirectUrl === '' || ! is_string($connectedAccountId) || $connectedAccountId === '') {
            throw new RuntimeException('Composio connect link response was missing redirect_url or connected_account_id.');
        }

        return [
            'redirect_url' => $redirectUrl,
            'connected_account_id' => $connectedAccountId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getConnectedAccount(string $connectedAccountId): array
    {
        return $this->request('GET', '/api/v3/connected_accounts/'.$connectedAccountId);
    }

    public function deleteConnectedAccount(string $connectedAccountId): void
    {
        $this->request('DELETE', '/api/v3/connected_accounts/'.$connectedAccountId);
    }

    /**
     * Composio injects OAuth credentials — raw access tokens are masked on account GET.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function proxyExecute(array $payload): array
    {
        try {
            return $this->request('POST', '/api/v3/tools/execute/proxy', $payload, timeout: 45);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Composio proxy execute failed: '.$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    public function executeTool(string $toolSlug, string $userId, string $connectedAccountId, array $arguments): array
    {
        try {
            return $this->request('POST', '/api/v3/tools/execute/'.$toolSlug, [
                'user_id' => $userId,
                'connected_account_id' => $connectedAccountId,
                'arguments' => $arguments,
            ]);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Composio tool '.$toolSlug.' failed: '.$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $json = null, int $timeout = 30): array
    {
        $apiKey = config('services.composio.key');

        if (! filled($apiKey)) {
            throw new RuntimeException('COMPOSIO_API_KEY is not configured.');
        }

        $baseUrl = rtrim((string) config('services.composio.base_url', 'https://backend.composio.dev'), '/');

        $client = new Client([
            'base_uri' => $baseUrl.'/',
            'timeout' => $timeout,
            'http_errors' => false,
            'headers' => [
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ],
        ]);

        $options = [];
        if ($json !== null && strtoupper($method) !== 'GET') {
            $options['json'] = $json;
        }

        try {
            $response = $client->request(strtoupper($method), ltrim($path, '/'), $options);
        } catch (GuzzleException $e) {
            throw new RuntimeException('Composio HTTP request failed: '.$e->getMessage(), previous: $e);
        }

        $body = (string) $response->getBody();
        $status = $response->getStatusCode();

        if ($status >= 400) {
            throw new RuntimeException($body !== '' ? $body : 'Composio request failed with HTTP '.$status.'.');
        }

        if ($body === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException('Composio returned invalid JSON: '.$body, previous: $e);
        }

        return is_array($data) ? $data : [];
    }
}
