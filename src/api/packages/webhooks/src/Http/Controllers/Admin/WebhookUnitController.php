<?php

namespace ParabellumKoval\Webhooks\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use ParabellumKoval\Webhooks\Services\WebhookDispatcher;
use ParabellumKoval\Webhooks\Services\WebhookRegistry;

class WebhookUnitController extends Controller
{
    public function __construct(
        protected WebhookRegistry $registry,
        protected WebhookDispatcher $dispatcher
    ) {
        $this->middleware(backpack_middleware());
    }

    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'unit_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $unitKey = $request->input('unit_key');
        $unit = $this->registry->find($unitKey);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook unit not found',
            ], 404);
        }

        $latestStatusKey = $this->latestStatusKey($unitKey);
        $latestStatus = Cache::get($latestStatusKey);

        if ($latestStatus && ($latestStatus['status'] ?? null) === 'running') {
            $timeSinceStart = time() - ($latestStatus['timestamp'] ?? time());
            if ($timeSinceStart < 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'Webhook for this unit is already in progress',
                    'data' => [
                        'running_since' => $timeSinceStart,
                        'unit' => $unit['title'],
                    ],
                ], 409);
            }
        }

        Cache::put($latestStatusKey, [
            'status' => 'running',
            'timestamp' => time(),
            'unit' => $unit['title'],
            'origin' => 'manual',
        ], 3600);

        try {
            $this->dispatcher->dispatchManual($unitKey);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch webhook job', [
                'unit_key' => $unitKey,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch webhook job',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook job has been queued',
            'data' => [
                'unit' => $unit['title'],
                'unit_key' => $unitKey,
                'urls' => $unit['urls'],
            ],
        ]);
    }

    public function status(): JsonResponse
    {
        $statuses = [];

        foreach ($this->registry->all() as $unitKey => $unit) {
            $latestStatus = Cache::get($this->latestStatusKey($unitKey));
            $statuses[] = [
                'unit' => $unit,
                'urls' => $unit['urls'],
                'status' => $latestStatus['status'] ?? 'never_run',
                'last_run' => $latestStatus['timestamp'] ?? null,
                'last_updated' => $latestStatus['updated_at'] ?? null,
                'data' => $latestStatus['data'] ?? null,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }

    public function unitStatus(string $unit): JsonResponse
    {
        $unitConfig = $this->registry->find($unit);

        if (!$unitConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found',
            ], 404);
        }

        $latestStatus = Cache::get($this->latestStatusKey($unit));

        return response()->json([
            'success' => true,
            'data' => [
                'unit' => $unitConfig,
                'status' => $latestStatus['status'] ?? 'never_run',
                'last_run' => $latestStatus['timestamp'] ?? null,
                'last_updated' => $latestStatus['updated_at'] ?? null,
                'data' => $latestStatus['data'] ?? [],
            ],
        ]);
    }

    public function clearStatusCache(): JsonResponse
    {
        foreach ($this->registry->all() as $unitKey => $unit) {
            Cache::forget($this->latestStatusKey($unitKey));
        }

        return response()->json([
            'success' => true,
            'message' => 'Statuses cleared',
        ]);
    }

    protected function latestStatusKey(string $unitKey): string
    {
        return 'webhooks.latest.' . $unitKey;
    }
}
