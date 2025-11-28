<?php

namespace Backpack\CRUD\app\Library\ServiceOperation\Similar;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SimilarSearchContext
{
    protected CrudPanel $crud;

    protected Model $entry;

    /**
     * @var array<string, mixed>
     */
    protected array $definition;

    /**
     * @var array<string, ?string>
     */
    protected array $fieldValueCache = [];

    public function __construct(CrudPanel $crud, Model $entry, array $definition)
    {
        $this->crud = $crud;
        $this->entry = $entry;
        $this->definition = $definition;
    }

    public function getCrud(): CrudPanel
    {
        return $this->crud;
    }

    public function getEntry(): Model
    {
        return $this->entry;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFields(): array
    {
        return $this->definition['fields'] ?? [];
    }

    public function getLimit(): int
    {
        return (int) ($this->definition['limit'] ?? 20);
    }

    /**
     * Resolve textual value for a configured field.
     */
    public function resolveFieldValue(array $field, ?string $locale = null): ?string
    {
        $cacheKey = ($field['key'] ?? 'unknown').'|'.($locale ?? '*');

        if (array_key_exists($cacheKey, $this->fieldValueCache)) {
            return $this->fieldValueCache[$cacheKey];
        }

        $value = $this->extractValue($field, $locale);
        $normalized = $this->normalizeScalarValue($value, $locale);

        return $this->fieldValueCache[$cacheKey] = $normalized !== '' ? $normalized : null;
    }

    /**
     * Extract raw field value using resolver or attribute access.
     *
     * @return mixed
     */
    protected function extractValue(array $field, ?string $locale = null)
    {
        $entry = $this->entry;
        $resolver = $field['resolver'] ?? null;
        $key = $field['key'] ?? null;

        if ($resolver) {
            if (is_string($resolver) && method_exists($entry, $resolver)) {
                return $entry->{$resolver}($locale);
            }

            if (is_callable($resolver)) {
                return $resolver($entry, $locale);
            }
        }

        if ($locale !== null
            && method_exists($entry, 'isTranslatableAttribute')
            && $key
            && $entry->isTranslatableAttribute($key)
        ) {
            return $entry->getTranslation($key, $locale, true);
        }

        return $key ? data_get($entry, $key) : null;
    }

    protected function normalizeScalarValue($value, ?string $locale = null): string
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            if ($locale !== null && array_key_exists($locale, $value)) {
                $value = $value[$locale];
            } else {
                $flattened = Arr::flatten($value);
                $value = implode(' ', array_map(function ($item) {
                    return is_scalar($item) ? (string) $item : '';
                }, $flattened));
            }
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }

        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
