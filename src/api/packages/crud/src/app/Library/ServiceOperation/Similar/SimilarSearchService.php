<?php

namespace Backpack\CRUD\app\Library\ServiceOperation\Similar;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\ServiceOperation\Similar\Contracts\SimilarSearchProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SimilarSearchService
{
    protected CrudPanel $crud;

    protected Model $entry;

    /**
     * @var array<string, mixed>
     */
    protected array $definition;

    /**
     * @var array<string, string>
     */
    protected array $cardViews;

    protected SimilarSearchContext $context;

    /**
     * @var array<int, int|string>|null
     */
    protected ?array $childIdsCache = null;

    protected ?string $lastStrictnessKey = null;

    public function __construct(CrudPanel $crud, Model $entry, array $definition, array $cardViews = [])
    {
        $this->crud = $crud;
        $this->entry = $entry;
        $this->definition = $definition;
        $this->cardViews = $cardViews;
        $this->context = new SimilarSearchContext($crud, $entry, $definition);
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->definition['enabled'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getStrictnessOptions(): array
    {
        return (array) data_get($this->definition, 'strictness.options', []);
    }

    public function getDefaultStrictnessKey(): string
    {
        return (string) data_get($this->definition, 'strictness.default', 'normal');
    }

    public function getResultCardView(): string
    {
        if (! empty($this->definition['card_view'])) {
            return $this->definition['card_view'];
        }

        if (! empty($this->cardViews['result'])) {
            return $this->cardViews['result'];
        }

        return 'crud::service.cards.default-result';
    }

    /**
     * Execute the provider and post-process the results.
     */
    public function search(array $params = []): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        $strictnessKey = $this->resolveStrictnessKey($params['strictness'] ?? null);
        $this->lastStrictnessKey = $strictnessKey;
        $options = $this->getStrictnessOptions();
        $strictness = $options[$strictnessKey] ?? ['threshold' => 0];
        $threshold = (float) ($strictness['threshold'] ?? 0);
        $limit = max(1, (int) ($params['limit'] ?? $this->definition['limit'] ?? 20));
        $excludeChildren = $this->shouldExcludeChildren($params);

        $providerParams = array_merge($params, [
            'strictness_key' => $strictnessKey,
            'strictness' => $strictness,
            'threshold' => $threshold,
            'limit' => $limit,
        ]);

        $provider = $this->makeProvider();
        $results = $provider->search($this->context, $providerParams)
            ->filter(function ($item) {
                if (! is_array($item) || ! array_key_exists('model', $item)) {
                    return false;
                }

                return $item['model'] instanceof Model;
            })
            ->map(function (array $item) {
                $item['score'] = array_key_exists('score', $item) ? (float) $item['score'] : null;
                $item['meta'] = $item['meta'] ?? [];

                return $item;
            });

        $filtered = $this->applyCommonFilters($results, $excludeChildren);

        return $filtered->values()->take($limit);
    }

    public function getLastStrictnessKey(): ?string
    {
        return $this->lastStrictnessKey;
    }

    protected function resolveStrictnessKey(?string $requested): string
    {
        $requested = $requested ? trim($requested) : null;
        $options = $this->getStrictnessOptions();

        if ($requested && isset($options[$requested])) {
            return $requested;
        }

        $default = $this->getDefaultStrictnessKey();
        if ($default && isset($options[$default])) {
            return $default;
        }

        return array_key_first($options) ?? 'normal';
    }

    /**
     * Keep unique ids, remove source record and optionally children.
     */
    protected function applyCommonFilters(Collection $results, bool $excludeChildren): Collection
    {
        $entryId = $this->entry->getKey();

        $unique = $results->unique(function ($item) {
            return $item['model']->getKey();
        })->reject(function ($item) use ($entryId) {
            return $item['model']->getKey() === $entryId;
        });

        if (! $excludeChildren) {
            return $unique;
        }

        $childIds = $this->resolveChildIds();
        if ($childIds === []) {
            return $unique;
        }

        return $unique->reject(function ($item) use ($childIds) {
            return in_array($item['model']->getKey(), $childIds, true);
        });
    }

    protected function shouldExcludeChildren(array $params): bool
    {
        $config = (array) ($this->definition['exclude_children'] ?? []);

        if (! ($config['enabled'] ?? false)) {
            return false;
        }

        if (array_key_exists('exclude_children', $params)) {
            return filter_var($params['exclude_children'], FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) ($config['default'] ?? true);
    }

    /**
     * @return array<int, int|string>
     */
    protected function resolveChildIds(): array
    {
        if ($this->childIdsCache !== null) {
            return $this->childIdsCache;
        }

        $config = (array) ($this->definition['exclude_children'] ?? []);

        if (! ($config['enabled'] ?? false)) {
            return $this->childIdsCache = [];
        }

        $resolver = $config['resolver'] ?? null;
        if ($resolver) {
            $ids = $this->callChildResolver($resolver);

            return $this->childIdsCache = $this->normalizeChildIds($ids);
        }

        if (! empty($config['relation']) && method_exists($this->entry, $config['relation'])) {
            $relation = $config['relation'];
            $this->entry->loadMissing($relation);
            $related = $this->entry->getRelation($relation);
            $keyName = $config['key'] ?? $this->entry->getKeyName();

            if ($related instanceof Collection) {
                return $this->childIdsCache = $this->normalizeChildIds($related->pluck($keyName)->all());
            }
        }

        if (! empty($config['column'])) {
            $column = $config['column'];
            $keyName = $config['key'] ?? $this->crud->model->getKeyName();
            $ids = $this->crud->model->newQuery()
                ->where($column, $this->entry->getKey())
                ->pluck($keyName)
                ->all();

            return $this->childIdsCache = $this->normalizeChildIds($ids);
        }

        return $this->childIdsCache = [];
    }

    /**
     * @param  callable|string  $resolver
     */
    protected function callChildResolver($resolver): array
    {
        if (is_string($resolver) && method_exists($this->entry, $resolver)) {
            return (array) $this->entry->{$resolver}();
        }

        if (is_callable($resolver)) {
            return (array) $resolver($this->entry);
        }

        return [];
    }

    /**
     * @param  mixed  $ids
     * @return array<int, int|string>
     */
    protected function normalizeChildIds($ids): array
    {
        if ($ids instanceof Collection) {
            $ids = $ids->all();
        }

        if (! is_array($ids)) {
            return [];
        }

        $normalized = [];

        foreach ($ids as $value) {
            if (is_numeric($value)) {
                $normalized[] = (int) $value;

                continue;
            }

            if (is_scalar($value)) {
                $trimmed = trim((string) $value);

                if ($trimmed !== '') {
                    $normalized[] = $trimmed;
                }
            }
        }

        return array_values(array_unique($normalized));
    }

    protected function makeProvider(): SimilarSearchProvider
    {
        $providerClass = $this->definition['provider'] ?? null;

        if (! $providerClass || ! class_exists($providerClass)) {
            $providerClass = \Backpack\CRUD\app\Library\ServiceOperation\Similar\Providers\DatabaseSimilarSearchProvider::class;
        }

        return app($providerClass);
    }
}
