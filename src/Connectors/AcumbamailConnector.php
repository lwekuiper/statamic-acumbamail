<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicAcumbamail\Connectors;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Blink;

class AcumbamailConnector
{
    protected $authToken;

    public function __construct()
    {
        $this->authToken = config('statamic.acumbamail.auth_token');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->authToken);
    }

    public function addSubscriber(int $listId, array $mergeFields, bool $doubleOptin = false): ?array
    {
        $response = Http::asForm()->post('https://acumbamail.com/api/1/addSubscriber/', [
            'auth_token' => $this->authToken,
            'list_id' => $listId,
            'merge_fields' => $mergeFields,
            'double_optin' => $doubleOptin ? 1 : 0,
            'update_subscriber' => 1,
            'complete_json' => 1,
        ]);

        return $this->handleResponse($response, 'Failed to add subscriber', [
            'list_id' => $listId,
        ]);
    }

    public function getLists(): array
    {
        return Blink::once('acumbamail::lists', function () {
            $response = Http::asForm()->post('https://acumbamail.com/api/1/getLists/', [
                'auth_token' => $this->authToken,
            ]);

            if (! $response->successful()) {
                Log::error('Acumbamail: Failed to get lists', ['response' => $response->json()]);

                return [];
            }

            return $response->json() ?? [];
        });
    }

    public function getList(int $id): ?array
    {
        $lists = $this->getLists();

        return $lists[$id] ?? null;
    }

    public function getMergeFields(int $listId): array
    {
        return Blink::once("acumbamail::merge-fields::{$listId}", function () use ($listId) {
            $response = Http::asForm()->post('https://acumbamail.com/api/1/getMergeFields/', [
                'auth_token' => $this->authToken,
                'list_id' => $listId,
            ]);

            if (! $response->successful()) {
                Log::error('Acumbamail: Failed to get merge fields', [
                    'list_id' => $listId,
                    'response' => $response->json(),
                ]);

                return [];
            }

            return $response->json() ?? [];
        });
    }

    private function handleResponse(Response $response, string $errorMessage, array $context = []): ?array
    {
        if (! $response->successful()) {
            Log::error("Acumbamail: {$errorMessage}", array_merge(['response' => $response->json()], $context));

            return null;
        }

        return $response->json();
    }
}
