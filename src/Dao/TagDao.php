<?php

declare(strict_types=1);

namespace App\Dao;

use App\Contracts\TagDaoInterface;
use PDO;

final class TagDao implements TagDaoInterface
{
    public function __construct(private readonly PDO $db) {}

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->query('SELECT id_tag, libelle, description FROM TAG ORDER BY libelle')->fetchAll();
    }

    public function create(int $id, string $libelle, ?string $description): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO TAG (id_tag, libelle, description) VALUES (:id, :libelle, :description)'
        );
        $statement->execute(['id' => $id, 'libelle' => $libelle, 'description' => $description]);
    }
}
