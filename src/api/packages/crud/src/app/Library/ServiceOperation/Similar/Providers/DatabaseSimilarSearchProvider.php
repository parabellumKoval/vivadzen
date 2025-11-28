<?php

namespace Backpack\CRUD\app\Library\ServiceOperation\Similar\Providers;

use Backpack\CRUD\app\Library\ServiceOperation\Similar\Contracts\SimilarSearchProvider;
use Backpack\CRUD\app\Library\ServiceOperation\Similar\SimilarSearchContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DatabaseSimilarSearchProvider implements SimilarSearchProvider
{
    public function search(SimilarSearchContext $context, array $params = []): Collection
    {
        $fields = $context->getFields();
        $fieldValues = $this->resolveSourceFieldValues($context, $fields);

        if ($fieldValues === []) {
            return collect();
        }

        $terms = $this->buildSearchTerms($fieldValues, (string) ($params['strictness_key'] ?? 'normal'));
        if ($terms === []) {
            $terms = array_values($fieldValues);
        }

        $model = $context->getCrud()->model;
        $builder = $model->newQuery();
        $builder->where($model->getKeyName(), '!=', $context->getEntry()->getKey());

        $builder->where(function ($query) use ($terms, $fields) {
            foreach ($fields as $field) {
                $column = $field['column'] ?? $field['key'] ?? null;

                if (! $column) {
                    continue;
                }

                foreach ($terms as $term) {
                    $query->orWhere($column, 'LIKE', '%'.$term.'%');
                }
            }
        });

        $limit = max(1, (int) ($params['limit'] ?? $context->getLimit()));
        $candidatesLimit = min(max($limit * 5, 20), 200);

        $candidates = $builder->limit($candidatesLimit)->get();
        $threshold = (float) ($params['threshold'] ?? 0);

        $results = [];

        foreach ($candidates as $candidate) {
            $score = $this->calculateSimilarityScore($candidate, $fields, $fieldValues);

            if ($threshold > 0 && $score < $threshold) {
                continue;
            }

            if ($score <= 0 && $threshold > 0) {
                continue;
            }

            $results[] = [
                'model' => $candidate,
                'score' => $score,
                'meta' => [
                    'source' => 'database',
                    'terms' => $terms,
                ],
            ];
        }

        usort($results, fn ($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        return collect($results);
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, string>
     */
    protected function resolveSourceFieldValues(SimilarSearchContext $context, array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            $value = $context->resolveFieldValue($field);

            if ($value !== null) {
                $values[$field['key']] = $value;
            }
        }

        return $values;
    }

    /**
     * @param  array<string, string>  $fieldValues
     * @return array<int, string>
     */
    protected function buildSearchTerms(array $fieldValues, string $mode): array
    {
        $terms = [];

        foreach ($fieldValues as $value) {
            $terms[] = $value;

            if ($mode === 'strict') {
                continue;
            }

            foreach ($this->tokenize($value) as $token) {
                if (mb_strlen($token) < 3) {
                    continue;
                }

                $terms[] = $token;
            }
        }

        $unique = array_values(array_unique(array_filter($terms)));

        return array_slice($unique, 0, 15);
    }

    protected function tokenize(string $value): array
    {
        $normalized = Str::of($value)->squish()->lower()->toString();

        $tokens = preg_split('/[\s,.;:!?\-\/]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return $tokens ?: [];
    }

    /**
     * Calculate similarity score (0-100) for a candidate.
     *
     * @param  array<int, array<string, mixed>>  $fields
     * @param  array<string, string>  $sourceValues
     */
    protected function calculateSimilarityScore(Model $candidate, array $fields, array $sourceValues): float
    {
        $scores = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $sourceValue = $sourceValues[$key] ?? null;

            if ($sourceValue === null) {
                continue;
            }

            $candidateValue = $this->extractCandidateValue($candidate, $field);

            if ($candidateValue === null) {
                continue;
            }

            $scores[] = $this->similarity($sourceValue, $candidateValue);
        }

        if ($scores === []) {
            return 0.0;
        }

        return max($scores);
    }

    protected function extractCandidateValue(Model $candidate, array $field): ?string
    {
        $value = data_get($candidate, $field['key']);

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $flattened = Arr::flatten($value);
            $value = implode(' ', array_map(function ($item) {
                return is_scalar($item) ? (string) $item : '';
            }, $flattened));
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }

        if (! is_scalar($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    protected function similarity(string $source, string $candidate): float
    {
        $a = mb_strtolower($source, 'UTF-8');
        $b = mb_strtolower($candidate, 'UTF-8');

        similar_text($a, $b, $percent);

        return (float) $percent;
    }
}
