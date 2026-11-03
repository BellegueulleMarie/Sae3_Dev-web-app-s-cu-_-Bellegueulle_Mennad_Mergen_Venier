<?php

declare(strict_types=1);

namespace App\Contracts;

interface TouiteServiceInterface
{
    /** @return list<array<string, mixed>> */
    public function latest(): array;

    /** @param array<string, mixed> $data */
    public function create(array $data): void;
}
