<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\TagRepositoryInterface;
use PDO;

final class TagRepository implements TagRepositoryInterface
{
    private static ?TagRepository $instance = null;

    private function __construct(private readonly PDO $db) {}

    public static function getInstance(PDO $db): static
    {
        return self::$instance ??= new self($db);
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id_tag, libelle, description FROM TAG ORDER BY libelle'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT id_tag, libelle, description FROM TAG WHERE id_tag = :id'
        );
        $request->execute(['id' => $id]);
        $tag = $request->fetch();

        return $tag === false ? null : $tag;
    }

    public function create(int $id, string $libelle, string $description): void
    {
        $request = $this->db->prepare(
            'INSERT INTO TAG (id_tag, libelle, description)
             VALUES (:id, :libelle, :description)'
        );
        $request->execute([
            'id' => $id,
            'libelle' => $libelle,
            'description' => $description,
        ]);
    }
}
