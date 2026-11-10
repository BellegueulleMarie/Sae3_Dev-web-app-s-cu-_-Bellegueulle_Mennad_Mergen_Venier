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
        $request = $this->db->query(
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
             ORDER BY t.date_publication DESC, t.id DESC'
        );

        return $request->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function byAuthor(int $authorId): array
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
             WHERE t.auteur_id = :author_id
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      t.image, u.nom_utilisateur
             ORDER BY t.date_publication DESC, t.id DESC'
        );
        $request->execute(['author_id' => $authorId]);

        return $request->fetchAll();
    }

    public function create(string $texte, int $auteurId, ?int $idImg = null): int
    {
        $request = $this->db->prepare(
            'INSERT INTO touites (texte, auteur_id, image)
             VALUES (:texte, :auteur, :id_img)'
        );
        $request->execute([
            'texte' => $texte,
            'auteur' => $auteurId,
            'id_img' => $idImg,
        ]);
        return (int) ($this->db->lastInsertId() ?: 0);
    }



        public function findAuthorIdByName(string $username): ?int
            {
                $request = $this->db->prepare('SELECT id FROM utilisateurs WHERE nom_utilisateur = :username');
                $request->execute(['username' => $username]);
                $id = $request->fetchColumn();

                 return $id !== false ? (int) $id : null;
            }


        public function count(?int $authorId = null): int
        {
            $sql = 'SELECT COUNT(*) FROM touites';
            if ($authorId !== null) {
                $sql .= ' WHERE auteur_id = :author_id';
            }

            $request = $this->db->prepare($sql);
            if ($authorId !== null) {
                $request->bindValue(':author_id', $authorId, PDO::PARAM_INT);
            }
            $request->execute();

            $result = $request->fetchColumn();
            return (int) $result;
        }

        /** @return list<array<string, mixed>> */
        public function paginate(int $page, int $perPage = 10, ?int $authorId = null): array
        {
            $offset = ($page - 1) * $perPage;
            $authorFilter = $authorId === null ? '' : 'WHERE t.auteur_id = :author_id';

            $request = $this->db->prepare(
                'SELECT t.id AS id_touite, t.texte,
                        t.date_publication AS date_touite,
                        t.auteur_id AS id_auteur, t.image AS id_img,
                        u.nom_utilisateur AS auteur,
                        COUNT(l.utilisateur_id) AS nombre_likes
                 FROM touites t
                 JOIN utilisateurs u ON u.id = t.auteur_id
                 LEFT JOIN likes l ON l.touite_id = t.id
                 ' . $authorFilter . '
                 GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                          t.image, u.nom_utilisateur
                 ORDER BY t.date_publication DESC, t.id DESC
                 LIMIT :limit OFFSET :offset'
            );
            if ($authorId !== null) {
                $request->bindValue(':author_id', $authorId, PDO::PARAM_INT);
            }
            $request->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $request->bindValue(':offset', $offset, PDO::PARAM_INT);
            $request->execute();

            return $request->fetchAll();
        }



}
