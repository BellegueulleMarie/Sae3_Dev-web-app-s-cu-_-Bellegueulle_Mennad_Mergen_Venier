<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\TagRepositoryInterface;
use PDO;

/**
 * Classe TagRepository
 *
 * Accède aux données des tags (#hashtags) en base de données.
 * Implémente le pattern Singleton pour partager une seule instance par requête.
 */
final class TagRepository implements TagRepositoryInterface
{
    /** @var TagRepository|null Instance unique (Singleton) */
    private static ?TagRepository $instance = null;

    /**
     * Constructeur privé : utiliser getInstance() pour obtenir l'instance.
     *
     * @param PDO $db Connexion PDO à la base de données
     */
    private function __construct(private readonly PDO $db) {}

    /**
     * Retourne l'instance unique du repository.
     *
     * @param PDO $db Connexion PDO à la base de données
     */
    public static function getInstance(PDO $db): static
    {
        return self::$instance ??= new self($db);
    }

    /**
     * Retourne tous les tags existants, triés par nom alphabétique.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, nom FROM tags ORDER BY nom'
        )->fetchAll();
    }

    /**
     * Retourne un tag par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant du tag
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT id, nom FROM tags WHERE id = :id'
        );
        $request->execute(['id' => $id]);
        $tag = $request->fetch();

        return $tag === false ? null : $tag;
    }

    /**
     * Retourne un tag par son nom exact, ou null si introuvable.
     *
     * @param string $name Nom du tag (sans #)
     * @return array<string, mixed>|null
     */
    public function findByName(string $name): ?array
    {
        $request = $this->db->prepare('SELECT id, nom FROM tags WHERE nom = :nom');
        $request->execute(['nom' => $name]);
        $tag = $request->fetch();

        return $tag === false ? null : $tag;
    }

    /**
     * Retourne le tag correspondant au nom donné.
     * Si le tag n'existe pas encore, il est créé automatiquement.
     *
     * @param string $name Nom du tag (avec ou sans #, en majuscules ou non)
     * @return array<string, mixed>
     */
    public function findOrCreateByName(string $name): array
    {
        // Normalise le nom : minuscules, sans # initial
        $nom = ltrim(mb_strtolower(trim($name)), '#');
        $tag = $this->findByName($nom);
        if ($tag !== null) {
            return $tag;
        }

        // Crée le tag s'il n'existe pas
        $request = $this->db->prepare('INSERT INTO tags (nom) VALUES (:nom)');
        $request->execute(['nom' => $nom]);

        return [
            'id'  => (int) $this->db->lastInsertId(),
            'nom' => $nom,
        ];
    }

    /**
     * Retourne les statistiques de tous les tags :
     * nombre de touites, total de likes, et moyenne de likes par touite.
     *
     * @return list<array<string, mixed>>
     */
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

    /**
     * Vérifie si un utilisateur est abonné à un tag.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $tagId  Identifiant du tag
     */
    public function isSubscribed(int $userId, int $tagId): bool
    {
        $request = $this->db->prepare(
            'SELECT 1 FROM abonnements_tags
             WHERE utilisateur_id = :user_id AND tag_id = :tag_id'
        );
        $request->execute(['user_id' => $userId, 'tag_id' => $tagId]);

        return $request->fetchColumn() !== false;
    }

    /**
     * Abonne un utilisateur à un tag (INSERT IGNORE pour éviter les doublons).
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $tagId  Identifiant du tag
     */
    public function subscribe(int $userId, int $tagId): void
    {
        $request = $this->db->prepare(
            'INSERT IGNORE INTO abonnements_tags (utilisateur_id, tag_id)
             VALUES (:user_id, :tag_id)'
        );
        $request->execute(['user_id' => $userId, 'tag_id' => $tagId]);
    }

    /**
     * Désabonne un utilisateur d'un tag.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $tagId  Identifiant du tag
     */
    public function unsubscribe(int $userId, int $tagId): void
    {
        $request = $this->db->prepare(
            'DELETE FROM abonnements_tags
             WHERE utilisateur_id = :user_id AND tag_id = :tag_id'
        );
        $request->execute(['user_id' => $userId, 'tag_id' => $tagId]);
    }

    /**
     * Retourne les tendances : les tags triés par nombre de mentions dans les touites.
     *
     * @param string $order 'DESC' = du plus utilisé au moins utilisé (défaut), 'ASC' = inverse
     * @return list<array<string, mixed>>
     */
    public function trends(string $order = 'DESC'): array
    {
        // Sécurise la valeur d'ordre pour éviter toute injection SQL
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $request = $this->db->prepare(
            "SELECT t.id, t.nom, COUNT(tt.touite_id) AS nb_mentions
             FROM tags t
             LEFT JOIN touite_tags tt ON tt.tag_id = t.id
             GROUP BY t.id, t.nom
             ORDER BY nb_mentions {$order}, t.nom ASC"
        );
        $request->execute();

        return $request->fetchAll();
    }
}
