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
            'SELECT id, nom FROM tags ORDER BY nom'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT id, nom FROM tags WHERE id = :id'
        );
        $request->execute(['id' => $id]);
        $tag = $request->fetch();

        return $tag === false ? null : $tag;
    }

    public function findByName(string $name): ?array
    {
        $request = $this->db->prepare('SELECT id, nom FROM tags WHERE nom = :nom');
        $request->execute(['nom' => $name]);
        $tag = $request->fetch();

        return $tag === false ? null : $tag;
    }

    /** @return list<array<string, mixed>> */
    public function statistics(): array
    {
        return $this->db->query(
            'SELECT t.id, t.nom,
                    COUNT(DISTINCT tt.touite_id) AS nombre_touites,
                    COUNT(l.utilisateur_id) AS total_likes,
                    COALESCE(
                        ROUND(
                            COUNT(l.utilisateur_id) /
                            NULLIF(COUNT(DISTINCT tt.touite_id), 0),
                            2
                        ),
                        0
                    ) AS moyenne_likes
             FROM tags t
             LEFT JOIN touite_tags tt ON tt.tag_id = t.id
             LEFT JOIN likes l ON l.touite_id = tt.touite_id
             GROUP BY t.id, t.nom
             ORDER BY total_likes DESC, nombre_touites DESC, t.nom ASC'
        )->fetchAll();
    }

    public function isSubscribed(int $userId, int $tagId): bool
    {
        $request = $this->db->prepare(
            'SELECT 1 FROM abonnements_tags
             WHERE utilisateur_id = :user_id AND tag_id = :tag_id'
        );
        $request->execute(['user_id' => $userId, 'tag_id' => $tagId]);

        return $request->fetchColumn() !== false;
    }

    public function subscribe(int $userId, int $tagId): void
    {
        $request = $this->db->prepare(
            'INSERT IGNORE INTO abonnements_tags (utilisateur_id, tag_id)
             VALUES (:user_id, :tag_id)'
        );
        $request->execute(['user_id' => $userId, 'tag_id' => $tagId]);
    }

    public function unsubscribe(int $userId, int $tagId): void
    {
        $request = $this->db->prepare(
            'DELETE FROM abonnements_tags
             WHERE utilisateur_id = :user_id AND tag_id = :tag_id'
        );
        $request->execute(['user_id' => $userId, 'tag_id' => $tagId]);
    }

}
