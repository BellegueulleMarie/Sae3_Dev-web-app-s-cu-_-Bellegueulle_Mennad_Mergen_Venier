<?php

declare(strict_types=1);

namespace App\Contracts;

interface TagServiceInterface
{
    /** @return list<array<string, mixed>> */
    public function list(): array;

    /** @param array<string, mixed> $data */
    public function create(array $data): void;
}
