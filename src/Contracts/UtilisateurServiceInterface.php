<?php

declare(strict_types=1);

namespace App\Contracts;

interface UtilisateurServiceInterface
{
    /** @return list<array<string, mixed>> */
    public function list(): array;

    /** @param array<string, mixed> $data */
    public function create(array $data): void;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void;
}
