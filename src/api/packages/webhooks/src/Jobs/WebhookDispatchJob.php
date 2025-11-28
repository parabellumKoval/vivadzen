<?php

namespace ParabellumKoval\Webhooks\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ParabellumKoval\Webhooks\Services\WebhookRegistry;
use ParabellumKoval\Webhooks\Support\EventBuffer;

class WebhookDispatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $unitKey;
    protected array $payloads;
    protected string $origin;
    protected ?string $eventKey;
    protected array $meta;
    protected bool $fromBuffer;
    protected int $timestamp;

    public function __construct(string $unitKey, array $payloads = [], string $origin = 'manual', ?string $eventKey = null, array $meta = [], bool $fromBuffer = false)
    {
        $this->unitKey = $unitKey;
        $this->payloads = $payloads;
        $this->origin = $origin;
        $this->eventKey = $eventKey;
        $this->meta = $meta;
        $this->fromBuffer = $fromBuffer;
        $this->timestamp = time();
    }

    public function handle(): void
    {
        $registry = app(WebhookRegistry::class);
        $unit = $registry->find($this->unitKey);

        if (!$unit) {
            Log::warning('Webhook unit is missing', ['unit_key' => $this->unitKey]);
            return;
        }

        $payloads = $this->resolvePayloads($unit);

        if ($this->origin === 'event' && $this->fromBuffer && $this->eventKey && $payloads === []) {
            Log::debug('Skipping webhook job because buffer was empty', ['unit_key' => $this->unitKey, 'event' => $this->eventKey]);
            return;
        }

        $frontendUrl = rtrim(config('webhooks.frontend_url'), '/');
        $globalTimeout = config('webhooks.timeout', 30);
        $timeout = $unit['timeout'] ?? $globalTimeout;
        $isUnlimited = ($timeout === 0);

        $this->prepareExecutionLimits($timeout, $isUnlimited, $unit);

        $requests = $this->buildRequests($unit, $payloads);
        if ($requests === []) {
            $this->updateJobStatus('skipped', [
                'reason' => 'No payloads to process',
                'origin' => $this->origin,
                'event' => $this->eventKey,
            ]);
            return;
        }

        Log::info('Webhook dispatch started', [
            'unit' => $unit['title'],
            'origin' => $this->origin,
            'event' => $this->eventKey,
            'requests' => count($requests),
        ]);

        $this->updateJobStatus('running', [
            'origin' => $this->origin,
            'event' => $this->eventKey,
            'timestamp' => $this->timestamp,
        ]);

        $results = [];
        $overallSuccess = true;

        foreach ($requests as $request) {
            $urlsToTry = $this->buildConcreteUrls($frontendUrl, $request['url']);
            $currentPayloads = $request['payloads'];

            foreach ($urlsToTry as $tryUrl) {
                try {
                    $response = $this->sendRequest($tryUrl, $unit, $timeout, $isUnlimited, $currentPayloads, $request['meta']);

                    if ($response['success']) {
                        $results[$request['url']] = [
                            'status' => 'success',
                            'url' => $response['url'],
                            'status_code' => $response['status'],
                            'payload_items' => count($currentPayloads),
                        ];
                        break;
                    }

                    $overallSuccess = false;
                    $results[$request['url']] = [
                        'status' => 'failed',
                        'error' => $response['error'],
                        'status_code' => $response['status'],
                    ];
                } catch (\Throwable $e) {
                    $overallSuccess = false;
                    Log::warning('Webhook request failed', [
                        'unit' => $unit['title'],
                        'url' => $tryUrl,
                        'error' => $e->getMessage(),
                    ]);
                    $results[$request['url']] = [
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                        'status_code' => null,
                    ];
                }
            }
        }

        if ($overallSuccess) {
            $this->updateJobStatus('success', [
                'results' => $results,
                'origin' => $this->origin,
                'event' => $this->eventKey,
                'payload_items' => count($payloads),
            ]);
        } else {
            $this->updateJobStatus('failed', [
                'results' => $results,
                'origin' => $this->origin,
                'event' => $this->eventKey,
                'payload_items' => count($payloads),
            ]);
        }
    }

    protected function resolvePayloads(array $unit): array
    {
        if (!$this->fromBuffer) {
            return $this->deduplicatePayloads($this->payloads, $unit['payload']['placeholder_key'] ?? null);
        }

        $buffer = app(EventBuffer::class);
        $payloads = $buffer->pull($this->unitKey, $this->eventKey ?? 'event');
        $buffer->forgetJobReservation($this->unitKey, $this->eventKey ?? 'event');

        return $this->deduplicatePayloads($payloads, $unit['payload']['placeholder_key'] ?? null);
    }

    protected function deduplicatePayloads(array $payloads, ?string $uniqueKey): array
    {
        if (!$uniqueKey) {
            return $payloads;
        }

        $seen = [];
        $result = [];

        foreach ($payloads as $payload) {
            $value = data_get($payload, $uniqueKey);
            if ($value === null) {
                continue;
            }
            $value = (string) $value;
            if (isset($seen[$value])) {
                continue;
            }
            $seen[$value] = true;
            $result[] = $payload;
        }

        return $result;
    }

    protected function buildRequests(array $unit, array $payloads): array
    {
        $requests = [];
        $placeholderKey = $unit['payload']['placeholder_key'] ?? null;
        $placeholderToken = $this->detectPlaceholder($unit['urls']);
        $batchConfig = $unit['payload']['batch'] ?? [];

        if ($placeholderKey && $placeholderToken) {
            $values = $this->extractValues($payloads, $placeholderKey);
            if ($values === []) {
                return [];
            }

            if (count($values) === 1) {
                $value = $values[0];
                foreach ($unit['urls'] as $url) {
                    $requests[] = [
                        'url' => $this->applyPlaceholder($url, $placeholderToken, $value),
                        'payloads' => $this->filterPayloadsByValue($payloads, $placeholderKey, $value),
                        'meta' => ['placeholder_value' => $value],
                    ];
                }

                return $requests;
            }

            if (!empty($batchConfig['url'])) {
                $requests[] = [
                    'url' => $batchConfig['url'],
                    'payloads' => $payloads,
                    'meta' => ['batch_values' => $values, 'batch' => true],
                ];

                return $requests;
            }

            foreach ($values as $value) {
                foreach ($unit['urls'] as $url) {
                    $requests[] = [
                        'url' => $this->applyPlaceholder($url, $placeholderToken, $value),
                        'payloads' => $this->filterPayloadsByValue($payloads, $placeholderKey, $value),
                        'meta' => ['placeholder_value' => $value, 'batch_fallback' => true],
                    ];
                }
            }

            return $requests;
        }

        foreach ($unit['urls'] as $url) {
            $requests[] = [
                'url' => $url,
                'payloads' => $payloads,
                'meta' => [],
            ];
        }

        return $requests;
    }

    protected function extractValues(array $payloads, string $key): array
    {
        $values = [];
        foreach ($payloads as $payload) {
            $value = data_get($payload, $key);
            if ($value === null) {
                continue;
            }
            $values[] = (string) $value;
        }

        return array_values(array_unique($values));
    }

    protected function filterPayloadsByValue(array $payloads, string $key, string $value): array
    {
        return array_values(array_filter($payloads, fn ($payload) => (string) data_get($payload, $key) === $value));
    }

    protected function detectPlaceholder(array $urls): ?string
    {
        $first = $urls[0] ?? null;
        if (!$first) {
            return null;
        }

        if (preg_match('/:([a-zA-Z0-9_]+)/', $first, $matches)) {
            return $matches[0];
        }

        return null;
    }

    protected function applyPlaceholder(string $url, string $placeholder, string $value): string
    {
        return str_replace($placeholder, $value, $url);
    }

    protected function buildConcreteUrls(string $frontendUrl, string $unitUrl): array
    {
        $fullUrl = $frontendUrl . $unitUrl;
        $urlsToTry = [$fullUrl];

        if (str_contains($fullUrl, 'localhost')) {
            $urlsToTry[] = str_replace('localhost', 'host.docker.internal', $fullUrl);
            $urlsToTry[] = str_replace('localhost', '172.17.0.1', $fullUrl);
        }

        if (str_contains($fullUrl, 'host.docker.internal')) {
            $urlsToTry[] = str_replace('host.docker.internal', 'localhost', $fullUrl);
        }

        return array_values(array_unique($urlsToTry));
    }

    protected function sendRequest(string $url, array $unit, int $timeout, bool $isUnlimited, array $payloads, array $meta): array
    {
        $client = Http::acceptJson()->withHeaders([
            'User-Agent' => 'Laravel-Webhooks/1.0',
            'X-Admin-Cache-Refresh' => 'true',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Connection' => 'close',
        ]);

        if ($isUnlimited) {
            $client = $client->timeout(3600)->connectTimeout(60);
        } else {
            $client = $client->timeout($timeout)->connectTimeout(10)->retry(2, 1000);
        }

        $client = $client->withOptions([
            'verify' => false,
            'http_errors' => false,
            'allow_redirects' => true,
            'max_redirects' => 3,
        ]);

        $body = [
            'timestamp' => $this->timestamp,
            'source' => 'admin-dashboard',
            'origin' => $this->origin,
            'unit' => $this->unitKey,
            'event' => $this->eventKey,
            'meta' => $this->meta,
        ];

        if ($payloads !== []) {
            $body['items'] = $payloads;
        }

        if (isset($meta['placeholder_value']) && isset($unit['payload']['placeholder_key'])) {
            $body[$unit['payload']['placeholder_key']] = $meta['placeholder_value'];
        }

        if (!empty($meta['batch']) && isset($unit['payload']['batch']['body_key'])) {
            $body[$unit['payload']['batch']['body_key']] = $meta['batch_values'] ?? [];
        }

        $method = strtolower($unit['method'] ?? 'post');
        $response = $client->{$method}($url, $body);

        $success = $response->successful();

        Log::info('Webhook request finished', [
            'unit' => $unit['title'],
            'url' => $url,
            'status' => $response->status(),
            'success' => $success,
        ]);

        return [
            'success' => $success,
            'status' => $response->status(),
            'error' => $success ? null : $response->body(),
            'url' => $url,
        ];
    }

    protected function prepareExecutionLimits(int $timeout, bool $isUnlimited, array $unit): void
    {
        if ($isUnlimited && function_exists('set_time_limit')) {
            @set_time_limit(0);
            @ini_set('max_execution_time', '0');
            Log::info('Set unlimited execution time for webhook job', ['unit' => $unit['title']]);
        }

        if (!$isUnlimited && $timeout >= 25 && php_sapi_name() !== 'cli') {
            $safetyMargin = $timeout + 10;
            @set_time_limit($safetyMargin);
        }
    }

    protected function updateJobStatus(string $status, array $data = []): void
    {
        $latestStatusKey = 'webhooks.latest.' . $this->unitKey;
        Cache::put($latestStatusKey, array_merge([
            'status' => $status,
            'timestamp' => time(),
            'unit_key' => $this->unitKey,
            'updated_at' => time(),
        ], $data), 3600);
    }
}
