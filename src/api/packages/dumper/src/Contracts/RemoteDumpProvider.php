<?php

namespace ParabellumKoval\Dumper\Contracts;

use ParabellumKoval\Dumper\Data\DumpRecord;

interface RemoteDumpProvider
{
    public function key(): string;

    public function label(): string;

    public function isConfigured(): bool;

    public function upload(DumpRecord $record, string $absoluteDumpPath, ?string $absoluteMetaPath = null): void;

    public function delete(DumpRecord $record): void;
}
