<?php

namespace ParabellumKoval\BackpackImages\Traits;

use RuntimeException;

trait HasImagesCrudComponents
{
    protected function addImagesField(array $overrides = []): void
    {
        $attribute = $overrides['name'] ?? null;
        $definition = $this->resolveImagesFieldDefinition($attribute, $overrides);

        $this->crud->addField($definition);
    }

    protected function addImagesColumn(array $overrides = []): void
    {
        $attribute = $overrides['name'] ?? null;
        $definition = $this->resolveImagesColumnDefinition($attribute, $overrides);

        $this->crud->addColumn($definition);
    }

    protected function resolveImagesFieldDefinition(?string $attribute, array $overrides = []): array
    {
        $modelClass = $this->resolveCrudModelClass();

        if (!method_exists($modelClass, 'imageFieldDefinition')) {
            throw new RuntimeException('Model must use the HasImages trait to provide field definitions.');
        }

        if ($attribute === null && isset($overrides['name'])) {
            $attribute = (string) $overrides['name'];
        }

        $definition = $modelClass::imageFieldDefinition($attribute);
        unset($overrides['name']);

        $definition = array_replace_recursive($definition, $overrides);
        $definition['name'] = $attribute ?? $definition['name'];

        return $definition;
    }

    protected function resolveImagesColumnDefinition(?string $attribute, array $overrides = []): array
    {
        $modelClass = $this->resolveCrudModelClass();

        if (!method_exists($modelClass, 'imageColumnDefinition')) {
            throw new RuntimeException('Model must use the HasImages trait to provide column definitions.');
        }

        if ($attribute === null && isset($overrides['name'])) {
            $attribute = (string) $overrides['name'];
        }

        $definition = $modelClass::imageColumnDefinition($attribute);
        unset($overrides['name']);

        $definition = array_replace_recursive($definition, $overrides);
        $definition['name'] = $attribute ?? $definition['name'];

        return $definition;
    }

    protected function resolveCrudModelClass(): string
    {
        $model = $this->crud->getModel();

        if (is_string($model)) {
            return $model;
        }

        return get_class($model);
    }
}
