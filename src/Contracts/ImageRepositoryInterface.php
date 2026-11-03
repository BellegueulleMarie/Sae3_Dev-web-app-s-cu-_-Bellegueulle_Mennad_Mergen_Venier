<?php

declare(strict_types=1);

namespace App\Contracts;

interface ImageRepositoryInterface
{
    public static function getInstance(\PDO $db): static;

    /** @return list<array<string, mixed>> */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    public function create(int $id, string $chemin, string $description): void;
}
