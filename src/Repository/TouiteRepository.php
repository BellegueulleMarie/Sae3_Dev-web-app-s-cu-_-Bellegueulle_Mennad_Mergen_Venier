<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\TouiteRepositoryInterface;
use PDO;

final class TouiteRepository implements TouiteRepositoryInterface
{
    private static ?TouiteRepository $instance = null;

    private function __construct(private readonly PDO $db) {}

    public static function getInstance(PDO $db): static
    {
        return self::$instance ??= new self($db);
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->query(
            'SELECT id_touite, texte, date_touite, id_auteur, id_img
             FROM TOUITE
             ORDER BY date_touite DESC'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT id_touite, texte, date_touite, id_auteur, id_img
             FROM TOUITE
             WHERE id_touite = :id'
        );
        $request->execute(['id' => $id]);
        $touite = $request->fetch();

        return $touite === false ? null : $touite;
    }

    public function create(int $id, string $texte, int $auteur): void
    {
        $request = $this->db->prepare(
            'INSERT INTO TOUITE (id_touite, texte, id_auteur)
             VALUES (:id, :texte, :auteur)'
        );
        $request->execute([
            'id' => $id,
            'texte' => $texte,
            'auteur' => $auteur,
        ]);
    }
}
