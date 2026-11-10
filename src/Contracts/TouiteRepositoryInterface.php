<?php

declare(strict_types=1);

namespace App\Contracts;

interface TouiteRepositoryInterface
{
    public static function getInstance(\PDO $db): static;

    /** @return list<array<string, mixed>> */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    /** @return list<array<string, mixed>> */
    public function wall(int $userId): array;

    /** @return list<array<string, mixed>> */
    public function byAuthor(int $authorId): array;

    public function create(string $texte, int $auteurId, ?int $idImg = null): int;

    public function count(?int $authorId = null): int;

    /** @return list<array<string, mixed>> */
    public function paginate(int $page, int $perPage = 10, ?int $authorId = null): array;
}
