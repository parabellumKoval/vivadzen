<?php

namespace ParabellumKoval\Dumper\Data;

use Carbon\CarbonImmutable;

class DumpRecord
{
    public function __construct(
        public readonly string $disk,
        public readonly string $path,
        public readonly string $filename,
        public readonly CarbonImmutable $createdAt,
        public readonly array $tables,
        public readonly string $source,
        public readonly ?string $caseKey,
        public readonly ?string $label,
        public readonly int $size
    ) {
    }

    public function isAuto(): bool
    {
        return $this->source === 'auto';
    }

    public function title(): string
    {
        return $this->label ?: $this->filename;
    }

    public function tablesLabel(): string
    {
        if ($this->tables === ['*']) {
            return 'Вся база данных';
        }

        return implode(', ', $this->tables);
    }

    public function identifier(): string
    {
        $encoded = base64_encode(json_encode([
            'disk' => $this->disk,
            'path' => $this->path,
        ], JSON_UNESCAPED_SLASHES));

        return rtrim(strtr($encoded, '+/', '-_'), '=');
    }
}
