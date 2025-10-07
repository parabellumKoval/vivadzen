<?php

namespace ParabellumKoval\BackpackImages\Traits;

use RuntimeException;

trait HasImagesCrudComponents
{
    protected function addImagesField(array $overrides = []): void
    {
        $this->crud->addField($this->resolveImagesFieldDefinition($overrides));
    }

    protected function addImagesColumn(array $overrides = []): void
    {
        $this->crud->addColumn($this->resolveImagesColumnDefinition($overrides));
    }

    protected function resolveImagesFieldDefinition(array $overrides = []): array
    {
        $modelClass = $this->resolveCrudModelClass();

        if (!method_exists($modelClass, 'defaultImagesFieldDefinition')) {
            throw new RuntimeException('Model must use the HasImages trait to provide field definitions.');
        }

        return array_replace_recursive($modelClass::defaultImagesFieldDefinition(), $overrides);
    }

    protected function resolveImagesColumnDefinition(array $overrides = []): array
    {
        $modelClass = $this->resolveCrudModelClass();

        if (!method_exists($modelClass, 'defaultImagesColumnDefinition')) {
            throw new RuntimeException('Model must use the HasImages trait to provide column definitions.');
        }

        return array_replace_recursive($modelClass::defaultImagesColumnDefinition(), $overrides);
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
