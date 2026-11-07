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
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur, t.image AS id_img,
                    u.nom_utilisateur AS auteur,
                    COUNT(l.utilisateur_id) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             LEFT JOIN likes l ON l.touite_id = t.id
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      t.image, u.nom_utilisateur
             ORDER BY date_publication DESC'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur, t.image AS id_img,
                    u.nom_utilisateur AS auteur,
                    COUNT(l.utilisateur_id) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             LEFT JOIN likes l ON l.touite_id = t.id
             WHERE t.id = :id
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      t.image, u.nom_utilisateur'
        );
        $request->execute(['id' => $id]);
        $touite = $request->fetch();

        return $touite === false ? null : $touite;
    }

    /** @return list<array<string, mixed>> */
    public function wall(int $userId): array
    {
        $request = $this->db->prepare(
            'SELECT DISTINCT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur, t.image AS id_img
             FROM touites t
             WHERE EXISTS (
                 SELECT 1
                 FROM suivis s
                 WHERE s.suiveur_id = :followed_by
                   AND s.suivi_id = t.auteur_id
             )
             OR EXISTS (
                 SELECT 1
                 FROM touite_tags ct
                 JOIN abonnements_tags at ON at.tag_id = ct.tag_id
                 WHERE at.utilisateur_id = :subscribed_by
                   AND ct.touite_id = t.id
             )
             ORDER BY t.date_publication DESC, t.id DESC'
        );
        $request->execute([
            'own_user' => $userId,
            'followed_by' => $userId,
            'subscribed_by' => $userId,
        ]);

        return $request->fetchAll();
    }

    public function create(int $id, string $texte, int $auteur): void
    {
        $request = $this->db->prepare(
            'INSERT INTO touites (id, texte, auteur_id)
             VALUES (:id, :texte, :auteur)'
        );
        $request->execute([
            'id' => $id,
            'texte' => $texte,
            'auteur' => $auteur,
        ]);
    }
}
