<?php

namespace Backpack\CRUD\app\Library\ServiceOperation;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\ServiceOperation\Similar\Providers\DatabaseSimilarSearchProvider;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MergeService
{
    public const STRATEGY_TRANSLATIONS = 'translations';
    public const STRATEGY_APPEND = 'append';
    public const STRATEGY_REPLACE = 'replace';

    protected CrudPanel $crud;

    protected ?Model $sourceEntry;

    /**
     * @var array<string, mixed>
     */
    protected array $definition = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $fieldDefinitions = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $relationDefinitions = [];

    /**
     * @var array<string, string>
     */
    protected array $cardViews = [];

    /**
     * @var array<string, mixed>
     */
    protected array $similarDefinition = [];

    public function __construct(CrudPanel $crud, ?Model $sourceEntry = null)
    {
        $this->crud = $crud;
        $this->sourceEntry = $sourceEntry;
        $this->definition = $this->resolveDefinition();
        $this->fieldDefinitions = $this->definition['fields'];
        $this->relationDefinitions = $this->definition['relations'];
        $this->cardViews = $this->definition['cards'] ?? [];
        $this->similarDefinition = $this->definition['similar'] ?? [];
    }

    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFields(): array
    {
        return array_values($this->fieldDefinitions);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRelations(): array
    {
        return array_values($this->relationDefinitions);
    }

    /**
     * @return array<string, string>
     */
    public function getCardViews(): array
    {
        return $this->cardViews;
    }

    public function getEntryCardView(): string
    {
        return $this->cardViews['entry'] ?? 'crud::service.cards.default-entry';
    }

    public function getResultCardView(): string
    {
        return $this->cardViews['result'] ?? 'crud::service.cards.default-result';
    }

    /**
     * @return array<string, mixed>
     */
    public function getSimilarSearchDefinition(): array
    {
        return $this->similarDefinition;
    }

    /**
     * @return array<string>
     */
    public function getFieldKeys(): array
    {
        return array_keys($this->fieldDefinitions);
    }

    /**
     * @return array<string>
     */
    public function getRelationDefaults(): array
    {
        return $this->definition['relation_defaults'] ?? [];
    }

    public function shouldDeleteSourceByDefault(): bool
    {
        return (bool) ($this->definition['delete_source_default'] ?? true);
    }

    public function getCandidateLabelFormat(): string
    {
        return $this->definition['candidate_label'] ?? '#%id% — %label%';
    }

    /**
     * Search candidates for select2 widget.
     */
    public function searchCandidates(?string $term, ?Model $source = null): array
    {
        $model = $this->crud->model;
        $builder = $model->newQuery();

        if ($source) {
            $builder->where($model->getKeyName(), '!=', $source->getKey());
        }

        $builder = $this->applyCandidateQuery($builder, $source);
        $term = trim((string) $term);

        if ($term !== '') {
            $builder->where(function (Builder $query) use ($term, $model) {
                $first = true;
                foreach ($this->getCandidateSearchableColumns($model) as $column) {
                    $method = $first ? 'where' : 'orWhere';
                    $first = false;

                    if ($column === $model->getKeyName() && is_numeric($term)) {
                        $query->{$method}($column, (int) $term);
                        continue;
                    }

                    $query->{$method}($column, 'LIKE', '%'.$term.'%');
                }
            });
        }

        $builder->orderByDesc($model->getKeyName());
        $builder->limit($this->definition['candidate_limit']);

        return $builder->get()->map(function (Model $entry) {
            return $this->makeCandidateOption($entry);
        })->all();
    }

    public function resolveCandidatesByIds(array $ids, ?Model $source = null): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return [];
        }

        $model = $this->crud->model;
        $builder = $model->newQuery()->whereIn($model->getKeyName(), $ids);

        if ($source) {
            $builder->where($model->getKeyName(), '!=', $source->getKey());
        }

        $entries = $builder->get()->sortBy(function (Model $entry) use ($ids) {
            return array_search($entry->getKey(), $ids, true);
        });

        return $entries->values()->map(function (Model $entry) {
            return $this->makeCandidateOption($entry);
        })->all();
    }

    /**
     * Merge $this->sourceEntry into $targetEntry using provided configuration.
     */
    public function mergeInto(Model $targetEntry, array $selectedFields, array $forcedFields, bool $deleteSource, array $relationKeys = [], array $relationOptions = []): array
    {
        if (! $this->sourceEntry) {
            throw new InvalidArgumentException('Источник слияния не определён.');
        }

        $selected = array_values(array_intersect($selectedFields, $this->getFieldKeys()));

        if ($selected === []) {
            throw new InvalidArgumentException('Не выбраны поля для слияния.');
        }

        if ($targetEntry->getKey() === null) {
            throw new InvalidArgumentException('Целевая запись должна существовать.');
        }

        $forceMap = array_flip($forcedFields);
        $filteredRelationOptions = $this->filterRelationOptions($relationOptions, $relationKeys);

        DB::transaction(function () use ($targetEntry, $selected, $forceMap, $deleteSource, $relationKeys, $filteredRelationOptions) {
            foreach ($selected as $fieldName) {
                $definition = $this->fieldDefinitions[$fieldName] ?? null;

                if (! $definition) {
                    continue;
                }

                $force = array_key_exists($fieldName, $forceMap);
                $this->applyFieldMerge($targetEntry, $this->sourceEntry, $definition, $force);
            }

            $this->runWithServiceMergeFlag($targetEntry, function () use ($targetEntry) {
                return $targetEntry->save();
            });

            if ($relationKeys !== []) {
                $this->mergeSelectedRelations(
                    $targetEntry,
                    $this->sourceEntry,
                    $relationKeys,
                    $selected,
                    array_keys($forceMap),
                    $filteredRelationOptions
                );
            }

            if ($deleteSource) {
                $this->sourceEntry->delete();
            }
        });

        return [
            'target' => $targetEntry->fresh(),
            'source_deleted' => $deleteSource,
        ];
    }

    protected function runWithServiceMergeFlag(Model $model, callable $callback)
    {
        $flag = 'skipServiceModificationSync';

        if (! property_exists($model, $flag)) {
            return $callback();
        }

        $model->{$flag} = true;

        try {
            return $callback();
        } finally {
            $model->{$flag} = false;
        }
    }

    protected function applyFieldMerge(Model $target, Model $source, array $definition, bool $force): void
    {
        $field = $definition['key'];
        $handler = $definition['handler'];

        if ($handler) {
            $this->callHandler($target, $source, $handler, $definition, $force);

            return;
        }

        $strategy = $definition['strategy'];

        if ($strategy === static::STRATEGY_TRANSLATIONS) {
            $this->mergeTranslations($target, $source, $field, $force);

            return;
        }

        if ($strategy === static::STRATEGY_APPEND) {
            $this->mergeAppendable($target, $source, $field, $force);

            return;
        }

        $this->mergeReplace($target, $source, $field, $force);
    }

    protected function mergeSelectedRelations(Model $target, Model $source, array $relationKeys, array $selectedFields, array $forcedFields, array $relationOptions = []): void
    {
        $unique = array_values(array_intersect(array_unique($relationKeys), array_keys($this->relationDefinitions)));

        foreach ($unique as $relationKey) {
            $definition = $this->relationDefinitions[$relationKey] ?? null;

            if (! $definition) {
                continue;
            }

            $options = $relationOptions[$relationKey] ?? [];

            $this->applyRelationMerge(
                $target,
                $source,
                $definition,
                $options,
                $selectedFields,
                $forcedFields,
                $unique,
                $relationOptions
            );
        }
    }

    protected function applyRelationMerge(Model $target, Model $source, array $definition, array $options, array $selectedFields, array $forcedFields, array $selectedRelationKeys, array $relationOptions): void
    {
        $handler = $definition['handler'];

        if ($handler) {
            $this->callRelationHandler($target, $source, $handler, $definition);

            return;
        }

        $type = $definition['type'] ?? 'table';

        if ($type === 'table') {
            $this->reassignTableRelation($target, $source, $definition);
            $this->maybeMergeRelationDuplicates(
                $target,
                $definition,
                $options,
                $selectedFields,
                $forcedFields,
                $selectedRelationKeys,
                $relationOptions
            );
        }
    }

    protected function reassignTableRelation(Model $target, Model $source, array $definition): void
    {
        $table = $definition['table'] ?? null;

        if (! $table) {
            return;
        }

        $column = $definition['column'] ?? $source->getForeignKey();
        $sourceId = $source->getKey();
        $targetId = $target->getKey();

        if ($sourceId === null || $targetId === null) {
            return;
        }

        $query = DB::table($table)->where($column, $sourceId);
        $this->applyRelationConstraints($query, $definition);

        $affected = $query->update([$column => $targetId]);

        if (! $affected) {
            return;
        }

        $uniqueColumns = $definition['unique'] ?? [];

        if ($uniqueColumns === []) {
            return;
        }

        $this->deduplicateTableRelations($table, $column, $targetId, $uniqueColumns, $definition);
    }

    protected function deduplicateTableRelations(string $table, string $column, $targetId, array $uniqueColumns, array $definition): void
    {
        $primaryKey = $definition['primary_key'] ?? 'id';
        $groupColumns = array_merge([$column], $uniqueColumns);

        $duplicatesQuery = DB::table($table)
            ->select(array_merge($groupColumns, [DB::raw('COUNT(*) as aggregate')]))
            ->where($column, $targetId);

        $this->applyRelationConstraints($duplicatesQuery, $definition);

        $duplicates = $duplicatesQuery
            ->groupBy($groupColumns)
            ->having('aggregate', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $idsQuery = DB::table($table)
                ->select($primaryKey)
                ->where($column, $targetId);

            $this->applyRelationConstraints($idsQuery, $definition);

            foreach ($uniqueColumns as $uniqueColumn) {
                if (isset($duplicate->{$uniqueColumn})) {
                    $idsQuery->where($uniqueColumn, $duplicate->{$uniqueColumn});
                }
            }

            $ids = $idsQuery->orderBy($primaryKey)->pluck($primaryKey);

            if ($ids->count() <= 1) {
                continue;
            }

            $idsToDelete = $ids->slice(1)->all();

            if ($idsToDelete !== []) {
                DB::table($table)->whereIn($primaryKey, $idsToDelete)->delete();
            }
        }
    }

    protected function applyRelationConstraints($query, array $definition): void
    {
        $constraints = $definition['constraints'] ?? [];

        foreach ($constraints as $constraint) {
            if (is_callable($constraint)) {
                $constraint($query);
                continue;
            }

            if (is_array($constraint) && isset($constraint['column'])) {
                $operator = $constraint['operator'] ?? '=';
                $value = $constraint['value'] ?? null;

                if ($operator === 'in' && is_array($value)) {
                    $query->whereIn($constraint['column'], $value);
                } else {
                    $query->where($constraint['column'], $operator, $value);
                }
            }
        }
    }

    protected function callRelationHandler(Model $target, Model $source, $handler, array $definition): void
    {
        $payload = [
            'relation' => $definition['key'],
            'definition' => $definition,
        ];

        if (is_string($handler) && method_exists($target, $handler)) {
            $target->{$handler}($source, $payload);

            return;
        }

        if (is_callable($handler)) {
            $handler($target, $source, $payload);
        }
    }

    protected function maybeMergeRelationDuplicates(Model $target, array $definition, array $options, array $selectedFields, array $forcedFields, array $selectedRelationKeys, array $relationOptions): void
    {
        $mergeConfig = $definition['merge'] ?? null;

        if (! $mergeConfig) {
            return;
        }

        $enabled = $this->resolveRelationMergeEnabled($mergeConfig, $options);

        if (! $enabled) {
            return;
        }

        $mode = $this->resolveRelationMergeMode($mergeConfig, $options);

        if (! $mode) {
            return;
        }

        $relationName = $definition['key'];

        if (! method_exists($target, $relationName)) {
            return;
        }

        $relation = $target->{$relationName}();

        if (! $relation instanceof Relation) {
            return;
        }

        $entries = $relation->getQuery()->get();

        if ($entries->count() < 2) {
            return;
        }

        $groups = $this->groupRelationDuplicates($entries, $mode);

        if ($groups === []) {
            return;
        }

        $childRelationKeys = array_values(array_filter($selectedRelationKeys, fn ($key) => $key !== $relationName));
        $childRelationOptions = $this->filterRelationOptions($relationOptions, $childRelationKeys);

        foreach ($groups as $group) {
            $this->mergeRelationDuplicateGroup($group, $selectedFields, $forcedFields, $childRelationKeys, $childRelationOptions);
        }
    }

    protected function resolveRelationMergeEnabled(array $mergeConfig, array $options): bool
    {
        $default = (bool) ($mergeConfig['default'] ?? false);
        $settings = $options['merge'] ?? null;

        if (! is_array($settings) || ! array_key_exists('enabled', $settings)) {
            return $default;
        }

        $value = $this->valueToBool($settings['enabled'], null);

        return $value === null ? $default : $value;
    }

    protected function resolveRelationMergeMode(array $mergeConfig, array $options): ?array
    {
        $settings = $options['merge'] ?? null;
        $modeKey = null;

        if (is_array($settings) && isset($settings['mode'])) {
            $candidate = trim((string) $settings['mode']);
            $modeKey = $candidate !== '' ? $candidate : null;
        }

        return $this->getRelationMergeModeByKey($mergeConfig, $modeKey);
    }

    protected function getRelationMergeModeByKey(array $mergeConfig, ?string $modeKey): ?array
    {
        $modes = $mergeConfig['modes_map'] ?? [];

        if ($modeKey && isset($modes[$modeKey])) {
            return $modes[$modeKey];
        }

        $defaultKey = $mergeConfig['default_mode'] ?? null;

        if ($defaultKey && isset($modes[$defaultKey])) {
            return $modes[$defaultKey];
        }

        if ($modes === []) {
            return null;
        }

        $first = reset($modes);

        return $first ?: null;
    }

    protected function groupRelationDuplicates(Collection $entries, array $modeDefinition): array
    {
        $matcher = $modeDefinition['matcher'] ?? null;

        if (is_string($matcher)) {
            $method = 'groupDuplicatesUsing'.Str::studly($matcher);

            if (method_exists($this, $method)) {
                return $this->{$method}($entries, $modeDefinition);
            }
        }

        if (is_callable($matcher)) {
            return $this->normalizeDuplicateGroups($matcher($entries, $modeDefinition));
        }

        return [];
    }

    protected function groupDuplicatesUsingNormalizedAttribute(Collection $entries, array $modeDefinition): array
    {
        $attribute = $modeDefinition['config']['attribute'] ?? ($modeDefinition['config']['field'] ?? null);

        if (! $attribute) {
            return [];
        }

        $precision = array_key_exists('precision', $modeDefinition['config'])
            ? (int) $modeDefinition['config']['precision']
            : null;

        $normalizedGroups = $this->buildDuplicateGroups(
            $this->groupEntriesByComparableString($entries, $attribute)
        );

        if ($normalizedGroups !== []) {
            return $normalizedGroups;
        }

        return $this->buildDuplicateGroups(
            $this->groupEntriesByNumericValue($entries, $attribute, $precision)
        );
    }

    protected function groupDuplicatesUsingNumericAttribute(Collection $entries, array $modeDefinition): array
    {
        return $this->groupDuplicatesUsingNormalizedAttribute($entries, $modeDefinition);
    }

    protected function groupEntriesByComparableString(Collection $entries, string $attribute): array
    {
        $grouped = [];

        foreach ($entries as $entry) {
            if (! $entry instanceof Model) {
                continue;
            }

            $value = data_get($entry, $attribute);
            $value = $this->stringifyValue($value);
            $normalized = $this->normalizeComparableString($value);

            if ($normalized === null) {
                continue;
            }

            $grouped[$normalized][] = $entry;
        }

        return $grouped;
    }

    protected function groupEntriesByNumericValue(Collection $entries, string $attribute, ?int $precision): array
    {
        $grouped = [];

        foreach ($entries as $entry) {
            if (! $entry instanceof Model) {
                continue;
            }

            $value = data_get($entry, $attribute);
            $value = $this->stringifyValue($value);
            $numeric = $this->extractNumericValue($value);

            if ($numeric === null) {
                continue;
            }

            if ($precision !== null) {
                $numeric = (float) number_format($numeric, $precision, '.', '');
            }

            $grouped[(string) $numeric][] = $entry;
        }

        return $grouped;
    }

    protected function buildDuplicateGroups(array $grouped): array
    {
        if ($grouped === []) {
            return [];
        }

        return collect($grouped)
            ->filter(fn ($group) => count($group) > 1)
            ->map(fn ($group) => collect($group))
            ->values()
            ->all();
    }

    protected function normalizeDuplicateGroups($groups): array
    {
        if ($groups instanceof Collection) {
            $groups = $groups->all();
        }

        if (! is_array($groups)) {
            return [];
        }

        $normalized = [];

        foreach ($groups as $group) {
            $collection = $group instanceof Collection ? $group : collect($group);
            $collection = $collection->filter(fn ($entry) => $entry instanceof Model)->values();

            if ($collection->count() > 1) {
                $normalized[] = $collection;
            }
        }

        return $normalized;
    }

    protected function mergeRelationDuplicateGroup(Collection $group, array $selectedFields, array $forcedFields, array $relationKeys, array $relationOptions): void
    {
        if ($group->count() < 2) {
            return;
        }

        $ordered = $group->sortBy(function (Model $entry) {
            return $entry->getKey();
        })->values();

        /** @var Model $target */
        $target = $ordered->shift();

        foreach ($ordered as $duplicate) {
            if (! $duplicate instanceof Model) {
                continue;
            }

            $service = new static($this->crud, $duplicate);
            $result = $service->mergeInto($target, $selectedFields, $forcedFields, true, $relationKeys, $relationOptions);
            $target = $result['target'] ?? $target->fresh();
        }
    }

    protected function extractNumericValue($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $filtered = preg_replace('/[^0-9,\.]/', '', $value);

        if ($filtered === null) {
            return null;
        }

        $filtered = str_replace(',', '.', $filtered);
        $filtered = trim($filtered);

        if ($filtered === '' || ! is_numeric($filtered)) {
            return null;
        }

        return (float) $filtered;
    }

    protected function normalizeComparableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);

        $normalized = preg_replace('/[\s\p{P}\p{S}]+/u', '', $normalized);

        if ($normalized === null) {
            return null;
        }

        return $normalized !== '' ? $normalized : null;
    }

    protected function filterRelationOptions(array $relationOptions, array $relationKeys): array
    {
        if ($relationKeys === []) {
            return [];
        }

        return array_intersect_key($relationOptions, array_flip($relationKeys));
    }

    protected function valueToBool($value, ?bool $default = null): ?bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        if (is_numeric($value)) {
            return ((int) $value) !== 0;
        }

        return $default;
    }

    protected function mergeTranslations(Model $target, Model $source, string $attribute, bool $force): void
    {
        if (! method_exists($target, 'getTranslations') || ! method_exists($target, 'setTranslation')) {
            $this->mergeReplace($target, $source, $attribute, $force);

            return;
        }

        $targetTranslations = (array) $target->getTranslations($attribute);
        $sourceTranslations = (array) $source->getTranslations($attribute);

        foreach ($sourceTranslations as $locale => $value) {
            if ($this->valueIsEmpty($value)) {
                continue;
            }

            $hasValue = array_key_exists($locale, $targetTranslations) && ! $this->valueIsEmpty($targetTranslations[$locale]);

            if (! $hasValue || $force) {
                $target->setTranslation($attribute, $locale, $value);
            }
        }
    }

    protected function mergeAppendable(Model $target, Model $source, string $attribute, bool $force): void
    {
        $targetValue = $target->getAttribute($attribute);
        $sourceValue = $source->getAttribute($attribute);

        if (is_numeric($targetValue) && is_numeric($sourceValue)) {
            $target->setAttribute($attribute, $targetValue + $sourceValue);

            return;
        }

        if (is_string($targetValue) || is_string($sourceValue)) {
            $left = trim((string) $targetValue);
            $right = trim((string) $sourceValue);

            if ($right === '') {
                return;
            }

            if ($force || $left === '') {
                $target->setAttribute($attribute, $right);

                return;
            }

            $target->setAttribute($attribute, trim($left.' '.$right));

            return;
        }

        $targetArray = $this->castToArray($targetValue);
        $sourceArray = $this->castToArray($sourceValue);

        if ($targetArray === null && $sourceArray === null) {
            return;
        }

        if ($this->isAssocArray($targetArray) || $this->isAssocArray($sourceArray)) {
            $merged = $targetArray ?? [];

            foreach ($sourceArray ?? [] as $key => $value) {
                if (! array_key_exists($key, $merged) || $force) {
                    $merged[$key] = $value;
                }
            }

            $target->setAttribute($attribute, $merged);

            return;
        }

        $merged = array_values(array_unique(array_merge($targetArray ?? [], $sourceArray ?? [])));
        $target->setAttribute($attribute, $merged);
    }

    protected function mergeReplace(Model $target, Model $source, string $attribute, bool $force): void
    {
        $sourceValue = $source->getAttribute($attribute);

        if ($this->valueIsEmpty($sourceValue)) {
            return;
        }

        $targetValue = $target->getAttribute($attribute);
        $targetHasValue = ! $this->valueIsEmpty($targetValue);

        if ($targetHasValue && ! $force) {
            return;
        }

        $target->setAttribute($attribute, $sourceValue);
    }

    protected function callHandler(Model $target, Model $source, $handler, array $definition, bool $force): void
    {
        $payload = [
            'field' => $definition['key'],
            'force' => $force,
            'definition' => $definition,
        ];

        if (is_string($handler) && method_exists($target, $handler)) {
            $target->{$handler}($source, $payload);

            return;
        }

        if (is_callable($handler)) {
            $handler($target, $source, $payload);
        }
    }

    protected function resolveDefinition(): array
    {
        $model = $this->crud->model;
        $definition = [];

        if (method_exists($model, 'getServiceMergeConfiguration')) {
            $definition = $model->getServiceMergeConfiguration();
        } elseif (method_exists($model, 'getServiceMergeDefinition')) {
            $definition = $model->getServiceMergeDefinition();
        } elseif (property_exists($model, 'serviceMergeDefinition')) {
            $definition = $model->serviceMergeDefinition;
        }

        $definition = is_array($definition) ? $definition : [];

        return $this->normalizeDefinition($definition);
    }

    protected function normalizeDefinition(array $definition): array
    {
        $label = $definition['label'] ?? 'Слияние записей';
        $description = $definition['description'] ?? 'Объедините данные текущей записи с другой записью.';
        $deleteSource = array_key_exists('delete_source_default', $definition)
            ? (bool) $definition['delete_source_default']
            : true;
        $candidateLimit = (int) ($definition['candidate_limit'] ?? 25);
        $candidateLimit = $candidateLimit > 0 ? $candidateLimit : 25;
        $searchable = Arr::wrap($definition['candidate_search'] ?? []);
        $candidateLabel = $definition['candidate_label'] ?? null;
        $fields = [];
        $defaultFields = [];

        foreach ((array) ($definition['fields'] ?? []) as $key => $config) {
            $normalized = $this->normalizeFieldDefinition($key, $config);
            $fields[$normalized['key']] = $normalized;

            if ($normalized['default']) {
                $defaultFields[] = $normalized['key'];
            }
        }

        $relations = [];
        $relationDefaults = [];

        foreach ((array) ($definition['relations'] ?? []) as $relationKey => $config) {
            $normalized = $this->normalizeRelationDefinition($relationKey, $config);
            $relations[$normalized['key']] = $normalized;

            if ($normalized['default']) {
                $relationDefaults[] = $normalized['key'];
            }
        }

        $candidateQuery = $definition['candidate_query'] ?? null;
        $cards = $this->normalizeCardViews($definition['cards'] ?? null);
        $similar = $this->normalizeSimilarDefinition($definition['similar'] ?? null, $cards);

        return [
            'label' => $label,
            'description' => $description,
            'delete_source_default' => $deleteSource,
            'candidate_label' => $candidateLabel,
            'candidate_limit' => $candidateLimit,
            'candidate_search' => $searchable,
            'candidate_query' => $candidateQuery,
            'default_fields' => $defaultFields,
            'fields' => $fields,
            'relations' => $relations,
            'relation_defaults' => $relationDefaults,
            'cards' => $cards,
            'similar' => $similar,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $config
     * @return array<string, string>
     */
    protected function normalizeCardViews($config): array
    {
        $defaults = [
            'entry' => 'crud::service.cards.default-entry',
            'result' => 'crud::service.cards.default-result',
        ];

        if (! is_array($config)) {
            return $defaults;
        }

        $entryView = $config['entry'] ?? $config['source'] ?? null;
        $resultView = $config['result'] ?? $config['card'] ?? null;

        return [
            'entry' => is_string($entryView) && $entryView !== '' ? $entryView : $defaults['entry'],
            'result' => is_string($resultView) && $resultView !== '' ? $resultView : $defaults['result'],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $definition
     * @return array<string, mixed>
     */
    protected function normalizeSimilarDefinition($definition, array $cards): array
    {
        $definition = is_array($definition) ? $definition : [];

        $fields = $this->normalizeSimilarFields($definition['fields'] ?? []);
        $strictness = $this->normalizeStrictnessOptions($definition['strictness'] ?? []);
        $excludeChildren = $this->normalizeExcludeChildrenDefinition($definition['exclude_children'] ?? null);

        $limit = (int) ($definition['limit'] ?? 20);
        $limit = $limit > 0 ? $limit : 20;

        $cardView = $definition['card_view'] ?? null;
        if (! is_string($cardView) || $cardView === '') {
            $cardView = $cards['result'];
        }

        $providerOptions = $definition['provider_options'] ?? [];
        $providerOptions = is_array($providerOptions) ? $providerOptions : [];

        return [
            'enabled' => (bool) ($definition['enabled'] ?? false),
            'label' => $definition['label'] ?? 'Поиск похожих записей',
            'description' => $definition['description'] ?? null,
            'limit' => $limit,
            'fields' => $fields,
            'strictness' => $strictness,
            'provider' => $definition['provider'] ?? DatabaseSimilarSearchProvider::class,
            'provider_options' => $providerOptions,
            'exclude_children' => $excludeChildren,
            'card_view' => $cardView,
        ];
    }

    /**
     * @param  array<int|string, mixed>  $fields
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeSimilarFields($fields): array
    {
        $normalized = [];

        foreach ((array) $fields as $name => $config) {
            if (is_string($config)) {
                $config = ['key' => $config];
            }

            if (! is_array($config)) {
                continue;
            }

            $key = $config['key'] ?? null;

            if (! is_string($key) || $key === '') {
                if (is_string($name) && $name !== '') {
                    $key = $name;
                } else {
                    continue;
                }
            }

            $column = $config['column'] ?? $key;
            $column = is_string($column) && $column !== '' ? $column : $key;

            $normalized[] = [
                'key' => $key,
                'column' => $column,
                'label' => $config['label'] ?? Str::title(str_replace('_', ' ', $key)),
                'resolver' => $config['resolver'] ?? null,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>|null  $strictness
     * @return array{options: array<string, array<string, mixed>>, default: string}
     */
    protected function normalizeStrictnessOptions($strictness): array
    {
        $defaults = [
            'strict' => [
                'key' => 'strict',
                'label' => __('Жёсткий поиск'),
                'description' => __('Максимально точные совпадения'),
                'threshold' => 85.0,
            ],
            'normal' => [
                'key' => 'normal',
                'label' => __('Стандартный поиск'),
                'description' => __('Баланс точности и количества результатов'),
                'threshold' => 65.0,
            ],
            'relaxed' => [
                'key' => 'relaxed',
                'label' => __('Мягкий поиск'),
                'description' => __('Больше кандидатов при меньшей точности'),
                'threshold' => 45.0,
            ],
        ];

        $strictness = is_array($strictness) ? $strictness : [];
        $optionsSource = $strictness['options'] ?? $strictness;
        $options = [];

        foreach ((array) $optionsSource as $key => $config) {
            if (is_string($config)) {
                $config = ['label' => $config];
            }

            if (! is_array($config)) {
                continue;
            }

            $name = $config['key'] ?? null;
            if (! is_string($name) || $name === '') {
                $name = is_string($key) ? $key : null;
            }

            if (! $name) {
                continue;
            }

            $base = $defaults[$name] ?? [
                'key' => $name,
                'label' => Str::title(str_replace('_', ' ', $name)),
                'description' => null,
                'threshold' => 50.0,
            ];

            $options[$name] = [
                'key' => $name,
                'label' => $config['label'] ?? $base['label'],
                'description' => $config['description'] ?? $base['description'],
                'threshold' => isset($config['threshold'])
                    ? (float) $config['threshold']
                    : (float) $base['threshold'],
            ];
        }

        if ($options === []) {
            $options = $defaults;
        } else {
            foreach ($defaults as $key => $value) {
                if (! isset($options[$key])) {
                    $options[$key] = $value;
                }
            }
        }

        $default = $strictness['default'] ?? null;
        if (! is_string($default) || ! isset($options[$default])) {
            $default = 'normal';
        }

        return [
            'options' => $options,
            'default' => $default,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $config
     * @return array<string, mixed>
     */
    protected function normalizeExcludeChildrenDefinition($config): array
    {
        $config = is_array($config) ? $config : [];
        $enabled = (bool) ($config['enabled'] ?? false);

        $relation = $config['relation'] ?? null;
        $relation = is_string($relation) && $relation !== '' ? $relation : null;

        $column = $config['column'] ?? null;
        $column = is_string($column) && $column !== '' ? $column : null;

        $keyName = $config['key'] ?? null;
        $keyName = is_string($keyName) && $keyName !== ''
            ? $keyName
            : $this->crud->model->getKeyName();

        return [
            'enabled' => $enabled,
            'default' => $enabled ? (bool) ($config['default'] ?? true) : false,
            'relation' => $relation,
            'column' => $column,
            'resolver' => $config['resolver'] ?? null,
            'key' => $keyName,
        ];
    }

    protected function normalizeRelationDefinition(string|int $name, $config): array
    {
        $key = is_string($name) ? trim($name) : '';

        if ($key === '') {
            $key = (string) ($config['name'] ?? '');
        }

        if ($key === '') {
            throw new InvalidArgumentException('Связь слияния должна иметь имя.');
        }

        $label = $config['label'] ?? Str::title(str_replace('_', ' ', $key));
        $type = $config['type'] ?? 'table';
        $handler = $config['handler'] ?? null;
        $default = (bool) ($config['default'] ?? false);
        $help = $config['help'] ?? null;
        $table = $config['table'] ?? null;
        $column = $config['column'] ?? null;
        $constraints = $this->normalizeConstraints($config['constraints'] ?? []);
        $unique = $this->normalizeStringArray($config['unique'] ?? []);
        $primaryKey = $config['primary_key'] ?? 'id';
        $mergeOptions = $this->normalizeRelationMergeOptions($config['merge'] ?? null);

        return [
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'handler' => $handler,
            'default' => $default,
            'help' => $help,
            'table' => $table,
            'column' => $column,
            'constraints' => $constraints,
            'unique' => $unique,
            'primary_key' => $primaryKey,
            'merge' => $mergeOptions,
        ];
    }

    protected function normalizeRelationMergeOptions($config): ?array
    {
        if (! is_array($config)) {
            return null;
        }

        $modes = [];

        foreach ((array) ($config['modes'] ?? []) as $modeKey => $modeDefinition) {
            $normalized = $this->normalizeRelationMergeMode($modeKey, $modeDefinition);
            $modes[$normalized['key']] = $normalized;
        }

        if ($modes === []) {
            return null;
        }

        $defaultMode = $config['default_mode'] ?? null;

        if ($defaultMode && ! isset($modes[$defaultMode])) {
            $defaultMode = null;
        }

        if ($defaultMode === null) {
            foreach ($modes as $mode) {
                if ($mode['default']) {
                    $defaultMode = $mode['key'];
                    break;
                }
            }
        }

        if ($defaultMode === null) {
            $defaultMode = array_key_first($modes);
        }

        return [
            'label' => $config['label'] ?? __('Сшивать найденные дубликаты'),
            'default' => (bool) ($config['default'] ?? false),
            'default_mode' => $defaultMode,
            'modes' => array_values($modes),
            'modes_map' => $modes,
        ];
    }

    protected function normalizeRelationMergeMode(string|int $name, $config): array
    {
        $key = is_string($name) ? trim($name) : '';

        if ($key === '') {
            $key = (string) ($config['key'] ?? $config['name'] ?? '');
        }

        if ($key === '') {
            throw new InvalidArgumentException('Режим слияния связи должен иметь имя.');
        }

        $label = $config['label'] ?? Str::title(str_replace('_', ' ', $key));
        $description = $config['description'] ?? null;
        $matcher = $config['matcher'] ?? ($config['handler'] ?? null);
        $default = (bool) ($config['default'] ?? false);

        $options = $config['config'] ?? Arr::except($config, [
            'label',
            'description',
            'matcher',
            'handler',
            'default',
            'key',
            'name',
            'config',
        ]);

        return [
            'key' => $key,
            'label' => $label,
            'description' => $description,
            'matcher' => $matcher,
            'default' => $default,
            'config' => $options,
        ];
    }

    protected function normalizeFieldDefinition(string|int $name, $config): array
    {
        $key = is_string($name) ? trim($name) : '';

        if ($key === '') {
            $key = (string) ($config['name'] ?? '');
        }

        if ($key === '') {
            throw new InvalidArgumentException('Поле слияния должно иметь имя.');
        }

        $label = $config['label'] ?? Str::title(str_replace('_', ' ', $key));
        $strategy = $config['strategy'] ?? static::STRATEGY_REPLACE;
        $handler = $config['handler'] ?? null;
        $default = (bool) ($config['default'] ?? false);
        $forceDefault = (bool) ($config['force'] ?? false);
        $forceable = array_key_exists('forceable', $config) ? (bool) $config['forceable'] : true;
        $help = $config['help'] ?? null;

        return [
            'key' => $key,
            'label' => $label,
            'strategy' => $strategy,
            'handler' => $handler,
            'default' => $default,
            'force' => $forceDefault,
            'forceable' => $forceable,
            'help' => $help,
        ];
    }

    /**
     * @param  array<int, string>|mixed  $values
     * @return array<int, string>
     */
    protected function normalizeStringArray($values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($value) {
            if (! is_string($value)) {
                return null;
            }

            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }, $values)));
    }

    /**
     * @param  array<int, mixed>|mixed  $constraints
     * @return array<int, mixed>
     */
    protected function normalizeConstraints($constraints): array
    {
        if (! is_array($constraints)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($constraint) {
            if (is_callable($constraint)) {
                return $constraint;
            }

            if (is_array($constraint) && isset($constraint['column'])) {
                $column = trim((string) $constraint['column']);

                if ($column === '') {
                    return null;
                }

                $operator = $constraint['operator'] ?? '=';
                $value = $constraint['value'] ?? null;

                if (is_string($operator)) {
                    $operator = trim($operator);
                }

                return [
                    'column' => $column,
                    'operator' => $operator,
                    'value' => $value,
                ];
            }

            return null;
        }, $constraints)));
    }

    protected function applyCandidateQuery(Builder $builder, ?Model $source): Builder
    {
        $candidateQuery = $this->definition['candidate_query'] ?? null;

        if ($candidateQuery && is_callable($candidateQuery)) {
            $result = $candidateQuery($builder, $source);

            if ($result instanceof Builder) {
                return $result;
            }
        }

        return $builder;
    }

    protected function getCandidateSearchableColumns(Model $model): array
    {
        $columns = $this->definition['candidate_search'];

        if ($columns === []) {
            $columns = [$model->identifiableAttribute(), $model->getKeyName()];
        }

        return array_values(array_filter(array_unique(array_map(function ($column) {
            return is_string($column) ? trim($column) : null;
        }, $columns))));
    }

    protected function formatCandidateLabel(Model $entry): string
    {
        $labelTemplate = $this->definition['candidate_label'];
        $attribute = $entry->identifiableAttribute();
        $value = $entry->getAttribute($attribute);
        $label = $this->stringifyValue($value) ?: '#'.$entry->getKey();

        if (! $labelTemplate) {
            return sprintf('#%s — %s', $entry->getKey(), $label);
        }

        return strtr($labelTemplate, [
            '%id%' => $entry->getKey(),
            '%label%' => $label,
        ]);
    }

    protected function stringifyValue($value): string
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $value = Arr::first(array_filter($value, fn ($item) => ! $this->valueIsEmpty($item)));
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return is_scalar($value) ? (string) $value : '';
    }

    protected function castToArray($value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    protected function isAssocArray(?array $value): bool
    {
        if ($value === null) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }

    protected function valueIsEmpty($value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (! $this->valueIsEmpty($item)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    protected function makeCandidateOption(Model $entry): array
    {
        $label = $this->formatCandidateLabel($entry);
        $uniqHtml = $this->resolveCandidateUniqHtml($entry);
        $uniqString = $this->resolveCandidateUniqString($entry);

        $text = $uniqString
            ?? ($uniqHtml ? trim(strip_tags($uniqHtml)) : null)
            ?? $label;

        $html = $uniqHtml ?? '<div><strong>'.e($text).'</strong></div>';

        return [
            'id' => $entry->getKey(),
            'text' => $text,
            'html' => $html,
            'slug' => method_exists($entry, 'getSlugOrTitleAttribute') ? ($entry->slug_or_title ?? null) : null,
        ];
    }

    protected function resolveCandidateUniqHtml(Model $entry): ?string
    {
        return $this->resolveModelUniqAttribute($entry, 'uniqHtml');
    }

    protected function resolveCandidateUniqString(Model $entry): ?string
    {
        return $this->resolveModelUniqAttribute($entry, 'uniqString');
    }

    protected function resolveModelUniqAttribute(Model $entry, string $attribute): ?string
    {
        $keys = array_unique([$attribute, Str::snake($attribute)]);

        foreach ($keys as $key) {
            $value = $entry->getAttribute($key);

            if ($value === null) {
                continue;
            }

            $stringValue = trim($this->stringifyValue($value));

            if ($stringValue !== '') {
                return $stringValue;
            }
        }

        return null;
    }
}
