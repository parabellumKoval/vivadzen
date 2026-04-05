<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessengerDeliveryReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $expectedApiKey = (string) \Settings::get(
            'shipping.messenger.reporting.api_key',
            (string) config('services.messenger.delivery_reporting_api_key', '')
        );

        if ($expectedApiKey === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Messenger delivery reporting API key is not configured.',
            ], 503);
        }

        $providedApiKey = (string) $request->header('X-API-KEY', '');

        if (! hash_equals($expectedApiKey, $providedApiKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key.',
            ], 401);
        }

        $payload = $request->all();

        if ($payload === []) {
            $decoded = json_decode((string) $request->getContent(), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        if (isset($payload['reports']) && is_array($payload['reports']) && array_is_list($payload['reports'])) {
            $payload = $payload['reports'];
        }

        if (! is_array($payload) || ! array_is_list($payload) || $payload === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'Expected a non-empty JSON array.',
            ], 422);
        }

        $validator = Validator::make(
            ['reports' => $payload],
            [
                'reports' => ['required', 'array', 'min:1'],
                'reports.*.order_number' => ['required', 'string', 'max:64'],
                'reports.*.recipient_fullname' => ['required', 'string', 'max:255'],
                'reports.*.recipient_actual_fullname' => ['nullable', 'string', 'max:255'],
                'reports.*.id_card_number' => ['required', 'string', 'max:100'],
                'reports.*.id_card_type' => ['required', 'in:op,passport,residence'],
                'reports.*.handover_place' => ['required', 'string', 'max:255'],
                'reports.*.handover_datetime' => ['required', 'date_format:Y-m-d H:i:s'],
                'reports.*.sender_fullname' => ['required', 'string', 'max:255'],
                'reports.*.customer_signature' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                    $this->validateSignature($attribute, $value, $fail);
                }],
                'reports.*.seller_signature' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                    $this->validateSignature($attribute, $value, $fail);
                }],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $isTest = $this->isTestRun($request);
        $accepted = [];
        $skipped = [];

        foreach ($validator->validated()['reports'] as $index => $reportData) {
            $exists = DeliveryReport::query()
                ->where('provider', 'messenger')
                ->where('order_number', $reportData['order_number'])
                ->exists();

            if ($exists) {
                $skipped[] = [
                    'index' => $index,
                    'order_number' => $reportData['order_number'],
                    'reason' => 'duplicate',
                ];
                continue;
            }

            $report = DeliveryReport::query()->create([
                'provider' => 'messenger',
                'order_number' => $reportData['order_number'],
                'recipient_fullname' => $reportData['recipient_fullname'],
                'recipient_actual_fullname' => $reportData['recipient_actual_fullname'] ?? null,
                'id_card_number' => $reportData['id_card_number'],
                'id_card_type' => $reportData['id_card_type'],
                'handover_place' => $reportData['handover_place'],
                'handover_datetime' => $reportData['handover_datetime'],
                'sender_fullname' => $reportData['sender_fullname'],
                'customer_signature' => $reportData['customer_signature'],
                'seller_signature' => $reportData['seller_signature'],
                'payload' => $reportData,
                'is_test' => $isTest,
            ]);

            $accepted[] = [
                'id' => $report->id,
                'order_number' => $report->order_number,
            ];
        }

        if ($accepted === [] && $skipped !== []) {
            return response()->json([
                'status' => 'skipped',
                'accepted' => 0,
                'skipped' => count($skipped),
                'results' => [
                    'accepted' => [],
                    'skipped' => $skipped,
                ],
            ], 409);
        }

        return response()->json([
            'status' => 'ok',
            'accepted' => count($accepted),
            'skipped' => count($skipped),
            'results' => [
                'accepted' => $accepted,
                'skipped' => $skipped,
            ],
        ]);
    }

    protected function isTestRun(Request $request): bool
    {
        $value = $request->header('X-Test-Run');

        if ($value === null) {
            return false;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes'], true);
    }

    protected function validateSignature(string $attribute, mixed $value, \Closure $fail): void
    {
        if (! is_string($value) || ! str_starts_with($value, 'data:image/png;base64,')) {
            $fail(sprintf('The %s field must be a PNG data URI.', $attribute));
            return;
        }

        $encoded = substr($value, strlen('data:image/png;base64,'));

        if ($encoded === '' || base64_decode($encoded, true) === false) {
            $fail(sprintf('The %s field must contain valid base64 PNG data.', $attribute));
        }
    }
}
