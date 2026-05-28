<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(string $slug): ?array;

    /** @return array<int, array<string, mixed>> */
    public function byTaxonomy(string $type, string $key): array;

    /** @return array<int, array<string, mixed>> */
    public function related(string $slug, int $limit = 4): array;
}
