<?php

declare(strict_types=1);

namespace App\Contracts;

interface TagRepositoryInterface
{
    public static function getInstance(\PDO $db): static;

    /** @return list<array<string, mixed>> */
    public function all(): array;

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;

    /** @return array<string, mixed>|null */
    public function findByName(string $name): ?array;

    public function isSubscribed(int $userId, int $tagId): bool;

    public function subscribe(int $userId, int $tagId): void;

    public function unsubscribe(int $userId, int $tagId): void;

}
