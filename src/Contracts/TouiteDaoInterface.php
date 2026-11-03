<?php

declare(strict_types=1);

namespace App\Contracts;

interface TouiteDaoInterface
{
    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 50): array;

    public function create(int $id, string $texte, int $authorId, ?int $imageId): void;
}
