<?php

namespace App\Services\NovaPoshta;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class NovaPoshtaClient
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = (string) config('novaposhta.api_url');
        $this->apiKey = (string) config('novaposhta.api_key');

        if ($this->apiKey === '') {
            throw new RuntimeException('NOVAPOSHTA_KEY is not set.');
        }
    }

    public function fetchSettlements(int $page, int $limit): array
    {
        return $this->request('Address', 'getSettlements', [
            'Page' => (string) $page,
            'Limit' => (string) $limit,
            'Warehouse' => '1',
        ]);
    }

    public function fetchWarehouses(int $page, int $limit): array
    {
        return $this->request('AddressGeneral', 'getWarehouses', [
            'Page' => (string) $page,
            'Limit' => (string) $limit,
        ]);
    }

    public function searchStreets(string $settlementRef, ?string $query, int $limit = 50, int $page = 1): array
    {
        return $this->request('Address', 'searchSettlementStreets', [
            'Page' => (string) $page,
            'Limit' => (string) $limit,
            'SettlementRef' => $settlementRef,
            'StreetName' => (string) ($query ?? ''),
        ]);
    }

    private function request(string $modelName, string $calledMethod, array $methodProperties): array
    {
        $response = Http::timeout(30)
            ->acceptJson()
            ->post($this->apiUrl, [
                'apiKey' => $this->apiKey,
                'modelName' => $modelName,
                'calledMethod' => $calledMethod,
                'methodProperties' => $methodProperties,
            ]);

        if (!$response->ok()) {
            throw new RuntimeException('Nova Poshta API request failed with status '.$response->status());
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            throw new RuntimeException('Nova Poshta API returned invalid JSON.');
        }

        if (!($payload['success'] ?? false)) {
            $errors = array_filter([...(array) ($payload['errors'] ?? []), ...(array) ($payload['warnings'] ?? [])]);
            $message = $errors ? implode('; ', $errors) : 'Unknown Nova Poshta API error.';
            throw new RuntimeException($message);
        }

        return $payload['data'] ?? [];
    }
}
