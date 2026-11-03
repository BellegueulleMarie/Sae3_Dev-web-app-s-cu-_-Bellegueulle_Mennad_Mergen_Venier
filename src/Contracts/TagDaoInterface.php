<?php

declare(strict_types=1);

namespace App\Contracts;

interface TagDaoInterface
{
    /** @return list<array<string, mixed>> */
    public function all(): array;

    public function create(int $id, string $libelle, ?string $description): void;
}
