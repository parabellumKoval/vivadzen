<?php

namespace App\Repositories\Contracts;

interface TaxonomyRepositoryInterface
{
    /** @return array<string, array<string, mixed>> */
    public function byType(string $type): array;

    /** @return array<string, mixed>|null */
    public function find(string $type, string $key): ?array;

    /** @return array{type:string, key:string, data:array<string,mixed>}|null */
    public function resolveSegment(string $segment): ?array;
}
