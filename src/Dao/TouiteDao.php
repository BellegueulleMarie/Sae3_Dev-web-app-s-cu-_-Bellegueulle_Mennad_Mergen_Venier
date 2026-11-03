<?php

declare(strict_types=1);

namespace App\Dao;

use App\Contracts\TouiteDaoInterface;
use PDO;

final class TouiteDao implements TouiteDaoInterface
{
    public function __construct(private readonly PDO $db) {}

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 50): array
    {
        $limit = max(1, min($limit, 100));
        return $this->db->query(
            'SELECT id_touite, texte, date_touite, id_auteur, id_img
             FROM TOUITE ORDER BY date_touite DESC FETCH FIRST ' . $limit . ' ROWS ONLY'
        )->fetchAll();
    }

    public function create(int $id, string $texte, int $authorId, ?int $imageId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO TOUITE (id_touite, texte, id_auteur, id_img)
             VALUES (:id, :texte, :author_id, :image_id)'
        );
        $statement->execute([
            'id' => $id, 'texte' => $texte, 'author_id' => $authorId, 'image_id' => $imageId,
        ]);
    }
}
