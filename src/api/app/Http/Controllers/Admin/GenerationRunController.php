<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunGenerationCommand;
use App\Models\GenerationRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GenerationRunController extends Controller
{
    private const DEFAULT_STALE_RUNNING_MINUTES = 30;

    public function index(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $this->finalizeStaleRunningRuns($type);
        $limit = max(1, min(25, (int) $request->integer('limit', 10)));

        $runs = GenerationRun::query()
            ->ofType($type)
            ->latest('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $runs->map(fn (GenerationRun $run) => $this->serializeRun($run))->values(),
        ]);
    }

    public function show(Request $request, GenerationRun $run): JsonResponse
    {
        $type = $this->resolveType($request);
        $this->finalizeStaleRunningRuns($type);
        $run->refresh();
        abort_unless($run->type === $type, 404);

        return response()->json([
            'data' => $this->serializeRun($run),
        ]);
    }

    public function storeBots(Request $request): JsonResponse
    {
        $payload = Validator::make($request->all(), [
            'count' => ['required', 'integer', 'min:1', 'max:5000'],
            'batch' => ['nullable', 'integer', 'min:1', 'max:500'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'min:2', 'max:8'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'email_domain' => ['nullable', 'string', 'max:255'],
            'verified' => ['nullable', 'boolean'],
            'dry_run' => ['nullable', 'boolean'],
        ])->validate();

        $options = [
            'count' => (string) $payload['count'],
            '--batch' => (string) ($payload['batch'] ?? min(50, (int) $payload['count'])),
            '--language' => $this->normalizeList($payload['languages'] ?? [], true),
            '--country' => $this->normalizeList($payload['countries'] ?? [], false, true),
            '--dry-run' => (bool) Arr::get($payload, 'dry_run', false),
        ];

        if (Arr::get($payload, 'verified', true)) {
            $options['--verified'] = true;
        } else {
            $options['--unverified'] = true;
        }

        if (! empty($payload['password'])) {
            $options['--password'] = $payload['password'];
        }

        if (! empty($payload['email_domain'])) {
            $options['--email-domain'] = trim((string) $payload['email_domain']);
        }

        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_BOT_USERS,
            'status' => GenerationRun::STATUS_QUEUED,
            'command' => 'profile:generate-bots',
            'initiator_id' => backpack_user()?->id,
            'progress_total' => 0,
            'progress_current' => 0,
            'options' => array_filter($options, fn ($value) => $value !== null && $value !== [] && $value !== ''),
            'meta' => [
                'requested_count' => (int) $payload['count'],
                'languages' => $this->normalizeList($payload['languages'] ?? [], true),
                'countries' => $this->normalizeList($payload['countries'] ?? [], false, true),
                'dry_run' => (bool) Arr::get($payload, 'dry_run', false),
            ],
        ]);

        RunGenerationCommand::dispatch($run->id);

        return response()->json([
            'data' => $this->serializeRun($run->fresh()),
        ], 201);
    }

    public function storeReviews(Request $request): JsonResponse
    {
        $payload = Validator::make($request->all(), [
            'selection_mode' => ['required', Rule::in(['all', 'category', 'brand', 'products', 'no_reviews', 'low_reviews'])],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'brand_id' => ['nullable', 'integer', 'min:1'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'min:1'],
            'review_count_max' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'product_limit' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'min_reviews' => ['required', 'integer', 'min:1', 'max:100'],
            'max_reviews' => ['required', 'integer', 'min:1', 'max:100'],
            'locales' => ['nullable', 'array'],
            'locales.*' => ['string', 'min:2', 'max:8'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string', 'size:2'],
            'skip_existing' => ['nullable', 'boolean'],
            'publish_now' => ['nullable', 'boolean'],
            'schedule_start' => ['nullable', 'string', 'max:255'],
            'schedule_min_per_day' => ['nullable', 'integer', 'min:1', 'max:100'],
            'schedule_max_per_day' => ['nullable', 'integer', 'min:1', 'max:100'],
            'schedule_hour_from' => ['nullable', 'integer', 'min:0', 'max:23'],
            'schedule_hour_to' => ['nullable', 'integer', 'min:0', 'max:23'],
            'dry_run' => ['nullable', 'boolean'],
        ])->after(function ($validator) use ($request) {
            $mode = $request->input('selection_mode');

            if ($mode === 'category' && ! $request->filled('category_id')) {
                $validator->errors()->add('category_id', 'Category is required for this selection mode.');
            }

            if ($mode === 'brand' && ! $request->filled('brand_id')) {
                $validator->errors()->add('brand_id', 'Brand is required for this selection mode.');
            }

            if ($mode === 'products' && empty($request->input('product_ids', []))) {
                $validator->errors()->add('product_ids', 'Choose at least one product.');
            }

            if ($mode === 'low_reviews' && ! $request->filled('review_count_max')) {
                $validator->errors()->add('review_count_max', 'Threshold is required for low review selection.');
            }

            if ((int) $request->input('max_reviews') < (int) $request->input('min_reviews')) {
                $validator->errors()->add('max_reviews', 'Max reviews must be greater than or equal to min reviews.');
            }
        })->validate();

        $options = [
            '--min' => (string) $payload['min_reviews'],
            '--max' => (string) $payload['max_reviews'],
            '--locale' => $this->normalizeList($payload['locales'] ?? [], true),
            '--country' => $this->normalizeList($payload['countries'] ?? [], false, true),
            '--skip-existing' => (bool) Arr::get($payload, 'skip_existing', false),
            '--publish-now' => (bool) Arr::get($payload, 'publish_now', false),
            '--dry-run' => (bool) Arr::get($payload, 'dry_run', false),
        ];

        $selectionMode = (string) $payload['selection_mode'];

        if (in_array($selectionMode, ['all', 'no_reviews', 'low_reviews'], true)) {
            $options['--all'] = true;
        }

        if ($selectionMode === 'category') {
            $options['--category'] = (string) $payload['category_id'];
        }

        if ($selectionMode === 'brand') {
            $options['--brand'] = (string) $payload['brand_id'];
        }

        if ($selectionMode === 'products') {
            $options['--products'] = array_map('strval', array_values($payload['product_ids'] ?? []));
        }

        if ($selectionMode === 'no_reviews') {
            $options['--review-count-max'] = '0';
        }

        if ($selectionMode === 'low_reviews') {
            $options['--review-count-max'] = (string) $payload['review_count_max'];
        }

        if (! empty($payload['product_limit'])) {
            $options['--limit'] = (string) $payload['product_limit'];
        }

        if (! empty($payload['schedule_start'])) {
            $options['--schedule-start'] = trim((string) $payload['schedule_start']);
        }

        foreach ([
            'schedule_min_per_day' => '--schedule-min-per-day',
            'schedule_max_per_day' => '--schedule-max-per-day',
            'schedule_hour_from' => '--schedule-hour-from',
            'schedule_hour_to' => '--schedule-hour-to',
        ] as $field => $option) {
            if (array_key_exists($field, $payload) && $payload[$field] !== null) {
                $options[$option] = (string) $payload[$field];
            }
        }

        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_PRODUCT_REVIEWS,
            'status' => GenerationRun::STATUS_QUEUED,
            'command' => 'reviews:generate',
            'initiator_id' => backpack_user()?->id,
            'progress_total' => 0,
            'progress_current' => 0,
            'options' => array_filter($options, fn ($value) => $value !== null && $value !== [] && $value !== ''),
            'meta' => [
                'selection_mode' => $selectionMode,
                'review_count_max' => Arr::get($payload, 'review_count_max'),
                'product_limit' => Arr::get($payload, 'product_limit'),
                'locales' => $this->normalizeList($payload['locales'] ?? [], true),
                'countries' => $this->normalizeList($payload['countries'] ?? [], false, true),
                'dry_run' => (bool) Arr::get($payload, 'dry_run', false),
                'publish_now' => (bool) Arr::get($payload, 'publish_now', false),
            ],
        ]);

        RunGenerationCommand::dispatch($run->id);

        return response()->json([
            'data' => $this->serializeRun($run->fresh()),
        ], 201);
    }

    protected function resolveType(Request $request): string
    {
        $type = (string) $request->route('generation_type', '');

        if (! in_array($type, [GenerationRun::TYPE_BOT_USERS, GenerationRun::TYPE_PRODUCT_REVIEWS], true)) {
            $path = ltrim($request->path(), '/');
            $segments = explode('/', $path);

            $type = match ($segments[1] ?? null) {
                'profile' => GenerationRun::TYPE_BOT_USERS,
                'review' => GenerationRun::TYPE_PRODUCT_REVIEWS,
                default => '',
            };
        }

        abort_unless(in_array($type, [GenerationRun::TYPE_BOT_USERS, GenerationRun::TYPE_PRODUCT_REVIEWS], true), 404);

        return $type;
    }

    protected function serializeRun(GenerationRun $run): array
    {
        $meta = $run->meta ?? [];
        $result = $run->result ?? [];

        return [
            'id' => $run->id,
            'type' => $run->type,
            'status' => $run->status,
            'command' => $run->command,
            'progress' => [
                'current' => (int) $run->progress_current,
                'total' => (int) $run->progress_total,
                'percent' => $run->progress_percent,
            ],
            'meta' => $meta,
            'result' => $result,
            'summary' => $this->summary($run),
            'error_message' => $run->error_message,
            'output' => $run->output,
            'started_at' => optional($run->started_at)->toDateTimeString(),
            'finished_at' => optional($run->finished_at)->toDateTimeString(),
            'created_at' => optional($run->created_at)->toDateTimeString(),
            'updated_at' => optional($run->updated_at)->toDateTimeString(),
        ];
    }

    protected function summary(GenerationRun $run): string
    {
        $meta = $run->meta ?? [];

        if ($run->type === GenerationRun::TYPE_BOT_USERS) {
            $created = (int) Arr::get($meta, 'created_count', 0);
            $total = (int) ($run->progress_total ?: Arr::get($meta, 'requested_count', 0));

            return $total > 0
                ? sprintf('Боты: %d/%d', $created ?: (int) $run->progress_current, $total)
                : 'Генерация ботов';
        }

        $productsDone = (int) $run->progress_current;
        $productsTotal = (int) $run->progress_total;
        $reviewsCreated = (int) Arr::get($meta, 'created_reviews', 0);
        $skipped = (int) Arr::get($meta, 'skipped_products', 0);

        $parts = [];

        if ($productsTotal > 0) {
            $parts[] = sprintf('Товары: %d/%d', $productsDone, $productsTotal);
        }

        if ($reviewsCreated > 0) {
            $parts[] = sprintf('Отзывы: %d', $reviewsCreated);
        }

        if ($skipped > 0) {
            $parts[] = sprintf('Пропущено: %d', $skipped);
        }

        return $parts !== [] ? implode(' • ', $parts) : 'Генерация отзывов';
    }

    protected function normalizeList(array $values, bool $lower = false, bool $upper = false): array
    {
        $normalized = [];

        foreach ($values as $value) {
            $item = trim((string) $value);
            if ($item === '') {
                continue;
            }

            if ($lower) {
                $item = strtolower($item);
            }

            if ($upper) {
                $item = strtoupper($item);
            }

            $normalized[] = $item;
        }

        return array_values(array_unique($normalized));
    }

    protected function finalizeStaleRunningRuns(string $type): void
    {
        $staleMinutes = $this->staleThresholdMinutes();
        $staleBefore = now()->subMinutes($staleMinutes);

        GenerationRun::query()
            ->ofType($type)
            ->where('status', GenerationRun::STATUS_RUNNING)
            ->whereNull('finished_at')
            ->whereNotNull('started_at')
            ->where('updated_at', '<', $staleBefore)
            ->get()
            ->each(function (GenerationRun $run) use ($staleMinutes): void {
                $run->markFailed(
                    sprintf('Generation run was marked as stalled after %d minutes without progress.', $staleMinutes),
                    $run->output,
                    [
                        'stale' => true,
                        'stale_minutes' => $staleMinutes,
                    ]
                );
            });
    }

    protected function staleThresholdMinutes(): int
    {
        return max(1, (int) config('queue.generation_run.stale_minutes', self::DEFAULT_STALE_RUNNING_MINUTES));
    }
}
