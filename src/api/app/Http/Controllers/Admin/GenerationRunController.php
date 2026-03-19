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
        $expectedCommand = $this->expectedCommandForType($type);

        $runs = GenerationRun::query()
            ->ofType($type)
            ->when($expectedCommand !== null, fn ($query) => $query->where('command', $expectedCommand))
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
        $expectedCommand = $this->expectedCommandForType($type);
        $run->refresh();
        abort_unless($run->type === $type, 404);
        abort_unless($expectedCommand === null || $run->command === $expectedCommand, 404);

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
            'with_avatars' => ['nullable', 'boolean'],
            'dry_run' => ['nullable', 'boolean'],
        ])->validate();

        try {
            $withAvatarsDefault = (bool) \Settings::get(
                'profile.bot_generation.generate_avatars_by_default',
                true
            );
        } catch (\Throwable) {
            $withAvatarsDefault = true;
        }
        $withAvatars = (bool) Arr::get($payload, 'with_avatars', $withAvatarsDefault);

        $options = [
            'count' => (string) $payload['count'],
            '--batch' => (string) ($payload['batch'] ?? min(50, (int) $payload['count'])),
            '--language' => $this->normalizeList($payload['languages'] ?? [], true),
            '--country' => $this->normalizeList($payload['countries'] ?? [], false, true),
            '--dry-run' => (bool) Arr::get($payload, 'dry_run', false),
        ];

        if ($withAvatars) {
            $options['--with-avatars'] = true;
        } else {
            $options['--without-avatars'] = true;
        }

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
                'with_avatars' => $withAvatars,
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
            'prevent_duplicate_reviewers' => ['nullable', 'boolean'],
            'publish_now' => ['nullable', 'boolean'],
            'schedule_start' => ['nullable', 'string', 'max:255'],
            'schedule_min_per_day' => ['nullable', 'integer', 'min:1', 'max:100'],
            'schedule_max_per_day' => ['nullable', 'integer', 'min:1', 'max:100'],
            'schedule_hour_from' => ['nullable', 'integer', 'min:0', 'max:23'],
            'schedule_hour_to' => ['nullable', 'integer', 'min:0', 'max:23'],
            'photo_review_chance_numerator' => ['nullable', 'integer', 'min:0', 'max:100'],
            'photo_review_chance_denominator' => ['nullable', 'integer', 'min:1', 'max:100'],
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

            $photoChanceNumerator = (int) $request->input('photo_review_chance_numerator', 0);
            $photoChanceDenominator = (int) $request->input('photo_review_chance_denominator', 10);

            if ($photoChanceNumerator > $photoChanceDenominator) {
                $validator->errors()->add('photo_review_chance_numerator', 'Photo review chance numerator must not exceed denominator.');
            }
        })->validate();

        $options = [
            '--min' => (string) $payload['min_reviews'],
            '--max' => (string) $payload['max_reviews'],
            '--locale' => $this->normalizeList($payload['locales'] ?? [], true),
            '--country' => $this->normalizeList($payload['countries'] ?? [], false, true),
            '--skip-existing' => (bool) Arr::get($payload, 'skip_existing', false),
            '--prevent-duplicate-reviewers' => Arr::get($payload, 'prevent_duplicate_reviewers', true) ? '1' : '0',
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

        $photoReviewChanceNumerator = (int) Arr::get($payload, 'photo_review_chance_numerator', 0);
        $photoReviewChanceDenominator = (int) Arr::get($payload, 'photo_review_chance_denominator', 10);

        if ($photoReviewChanceNumerator > 0) {
            $options['--photo-review-chance'] = sprintf('%d/%d', $photoReviewChanceNumerator, $photoReviewChanceDenominator);
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
                'prevent_duplicate_reviewers' => (bool) Arr::get($payload, 'prevent_duplicate_reviewers', true),
                'dry_run' => (bool) Arr::get($payload, 'dry_run', false),
                'publish_now' => (bool) Arr::get($payload, 'publish_now', false),
                'photo_review_chance' => sprintf('%d/%d', $photoReviewChanceNumerator, $photoReviewChanceDenominator),
            ],
        ]);

        RunGenerationCommand::dispatch($run->id);

        return response()->json([
            'data' => $this->serializeRun($run->fresh()),
        ], 201);
    }

    public function storePhotos(Request $request): JsonResponse
    {
        $photoConfig = (array) config('backpack.reviews.generated_product_photos', []);
        $imageDriverOptions = array_keys((array) ($photoConfig['image_driver_options'] ?? []));
        $imageModelOptions = array_keys((array) ($photoConfig['image_model_options'] ?? []));
        $promptDriverOptions = array_keys((array) ($photoConfig['prompt_driver_options'] ?? []));
        $promptModelOptions = array_keys((array) ($photoConfig['prompt_model_options'] ?? []));

        $appendOption = static function (?string $fallbackValue, array $options): array {
            if (is_string($fallbackValue) && trim($fallbackValue) !== '' && !in_array($fallbackValue, $options, true)) {
                $options[] = $fallbackValue;
            }
            return $options;
        };

        $imageDriverOptions = $appendOption($photoConfig['image_driver'] ?? null, $imageDriverOptions);
        $imageModelOptions = $appendOption($photoConfig['image_model'] ?? null, $imageModelOptions);
        $promptDriverOptions = $appendOption($photoConfig['prompt_driver'] ?? null, $promptDriverOptions);
        $promptModelOptions = $appendOption($photoConfig['prompt_model'] ?? null, $promptModelOptions);

        $payload = Validator::make($request->all(), [
            'selection_mode' => ['required', Rule::in(['all', 'category', 'brand', 'products'])],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'brand_id' => ['nullable', 'integer', 'min:1'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'min:1'],
            'products_limit' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'photos_per_product' => ['required', 'integer', 'min:1', 'max:50'],
            'photos_limit_total' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'skip_existing' => ['nullable', 'boolean'],
            'validate_reference' => ['nullable', 'boolean'],
            'ai_prompt_variations' => ['nullable', 'boolean'],
            'watermark_crop_right_percent' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'watermark_crop_bottom_percent' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'image_driver' => ['nullable', 'string', 'max:64', Rule::in($imageDriverOptions)],
            'image_model' => ['nullable', 'string', 'max:128', Rule::in($imageModelOptions)],
            'prompt_driver' => ['nullable', 'string', 'max:64', Rule::in($promptDriverOptions)],
            'prompt_model' => ['nullable', 'string', 'max:128', Rule::in($promptModelOptions)],
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
        })->validate();

        $selectionMode = (string) $payload['selection_mode'];

        $options = [
            '--photos-per-product' => (string) $payload['photos_per_product'],
            '--skip-existing' => (bool) Arr::get($payload, 'skip_existing', false),
            '--validate-reference' => (bool) Arr::get($payload, 'validate_reference', false),
            '--ai-prompt-variations' => (bool) Arr::get($payload, 'ai_prompt_variations', true),
            '--dry-run' => (bool) Arr::get($payload, 'dry_run', false),
        ];

        if (! empty($payload['products_limit'])) {
            $options['--limit'] = (string) $payload['products_limit'];
        }

        if (! empty($payload['photos_limit_total'])) {
            $options['--photos-limit-total'] = (string) $payload['photos_limit_total'];
        }

        if (array_key_exists('watermark_crop_right_percent', $payload) && $payload['watermark_crop_right_percent'] !== null) {
            $options['--watermark-crop-right-percent'] = (string) $payload['watermark_crop_right_percent'];
        }

        if (array_key_exists('watermark_crop_bottom_percent', $payload) && $payload['watermark_crop_bottom_percent'] !== null) {
            $options['--watermark-crop-bottom-percent'] = (string) $payload['watermark_crop_bottom_percent'];
        }

        if (! empty($payload['image_driver'])) {
            $options['--image-driver'] = (string) $payload['image_driver'];
        }

        if (! empty($payload['image_model'])) {
            $options['--image-model'] = (string) $payload['image_model'];
        }

        if (! empty($payload['prompt_driver'])) {
            $options['--prompt-driver'] = (string) $payload['prompt_driver'];
        }

        if (! empty($payload['prompt_model'])) {
            $options['--prompt-model'] = (string) $payload['prompt_model'];
        }

        if (in_array($selectionMode, ['all'], true)) {
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

        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_PRODUCT_REVIEW_PHOTOS,
            'status' => GenerationRun::STATUS_QUEUED,
            'command' => 'reviews:generate-product-photos',
            'initiator_id' => backpack_user()?->id,
            'progress_total' => 0,
            'progress_current' => 0,
            'options' => array_filter($options, fn ($value) => $value !== null && $value !== [] && $value !== ''),
            'meta' => [
                'selection_mode' => $selectionMode,
                'products_limit' => Arr::get($payload, 'products_limit'),
                'photos_per_product' => (int) Arr::get($payload, 'photos_per_product', 1),
                'photos_limit_total' => Arr::get($payload, 'photos_limit_total'),
                'skip_existing' => (bool) Arr::get($payload, 'skip_existing', false),
                'validate_reference' => (bool) Arr::get($payload, 'validate_reference', false),
                'ai_prompt_variations' => (bool) Arr::get($payload, 'ai_prompt_variations', true),
                'dry_run' => (bool) Arr::get($payload, 'dry_run', false),
            ],
        ]);

        RunGenerationCommand::dispatch($run->id);

        return response()->json([
            'data' => $this->serializeRun($run->fresh()),
        ], 201);
    }

    protected function resolveType(Request $request): string
    {
        $allowedTypes = [
            GenerationRun::TYPE_BOT_USERS,
            GenerationRun::TYPE_PRODUCT_REVIEWS,
            GenerationRun::TYPE_PRODUCT_REVIEW_PHOTOS,
        ];
        $requestedType = (string) $request->query('type', '');
        $type = in_array($requestedType, $allowedTypes, true)
            ? $requestedType
            : (string) $request->route('generation_type', '');

        if (! in_array($type, $allowedTypes, true)) {
            $path = ltrim($request->path(), '/');
            $segments = explode('/', $path);
            $reviewSubsection = $segments[2] ?? null;

            $type = match ($segments[1] ?? null) {
                'review' => match ($reviewSubsection) {
                    'photo-generation-runs' => GenerationRun::TYPE_PRODUCT_REVIEW_PHOTOS,
                    default => GenerationRun::TYPE_PRODUCT_REVIEWS,
                },
                'profile' => GenerationRun::TYPE_BOT_USERS,
                default => '',
            };
        }

        abort_unless(in_array($type, $allowedTypes, true), 404);

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

        if ($run->type === GenerationRun::TYPE_PRODUCT_REVIEW_PHOTOS) {
            $productsDone = (int) $run->progress_current;
            $productsTotal = (int) $run->progress_total;
            $photosGenerated = (int) Arr::get($meta, 'generated_photos', 0);
            $skipped = (int) Arr::get($meta, 'skipped_products', 0);
            $failed = (int) Arr::get($meta, 'failed_products', 0);

            $parts = [];

            if ($productsTotal > 0) {
                $parts[] = sprintf('Товары: %d/%d', $productsDone, $productsTotal);
            }

            if ($photosGenerated > 0) {
                $parts[] = sprintf('Фото: %d', $photosGenerated);
            }

            if ($skipped > 0) {
                $parts[] = sprintf('Пропущено: %d', $skipped);
            }

            if ($failed > 0) {
                $parts[] = sprintf('Ошибки: %d', $failed);
            }

            return $parts !== [] ? implode(' • ', $parts) : 'Генерация фото товаров';
        }

        $productsDone = (int) $run->progress_current;
        $productsTotal = (int) $run->progress_total;
        $reviewsCreated = (int) Arr::get($meta, 'created_reviews', 0);
        $photoReviews = (int) Arr::get($meta, 'photo_reviews', 0);
        $skipped = (int) Arr::get($meta, 'skipped_products', 0);

        $parts = [];

        if ($productsTotal > 0) {
            $parts[] = sprintf('Товары: %d/%d', $productsDone, $productsTotal);
        }

        if ($reviewsCreated > 0) {
            $parts[] = sprintf('Отзывы: %d', $reviewsCreated);
        }

        if ($photoReviews > 0) {
            $parts[] = sprintf('Фото: %d', $photoReviews);
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

    protected function expectedCommandForType(string $type): ?string
    {
        return match ($type) {
            GenerationRun::TYPE_BOT_USERS => 'profile:generate-bots',
            GenerationRun::TYPE_PRODUCT_REVIEWS => 'reviews:generate',
            GenerationRun::TYPE_PRODUCT_REVIEW_PHOTOS => 'reviews:generate-product-photos',
            default => null,
        };
    }
}
