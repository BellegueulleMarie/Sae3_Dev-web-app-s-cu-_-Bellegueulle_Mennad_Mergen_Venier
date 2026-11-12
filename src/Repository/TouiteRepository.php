<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\TouiteRepositoryInterface;
use PDO;

/**
 * Classe TouiteRepository
 *
 * Accède aux données des touites en base de données.
 * Implémente le pattern Singleton pour partager une seule instance par requête.
 *
 * Le score d'un touite est calculé via la table `evaluations` (valeur +1/-1).
 * La table `likes` est conservée pour compatibilité avec l'ancien code.
 */
final class TouiteRepository implements TouiteRepositoryInterface
{
    /** @var TouiteRepository|null Instance unique (Singleton) */
    private static ?TouiteRepository $instance = null;

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
     * Retourne tous les touites, du plus récent au plus ancien.
     * Le score inclut les évaluations (+1/-1) et les anciens likes.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur,
                    u.nom_utilisateur AS auteur,
                    COALESCE((SELECT SUM(valeur) FROM evaluations WHERE touite_id = t.id), COUNT(DISTINCT l.utilisateur_id), 0) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             LEFT JOIN likes l ON l.touite_id = t.id
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      u.nom_utilisateur
             ORDER BY date_publication DESC'
        )->fetchAll();
    }

    /**
     * Retourne un touite par son identifiant, ou null si introuvable.
     * Inclut le nom de l'image associée (id_img).
     *
     * @param int $id Identifiant du touite
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur, t.image AS id_img,
                    u.nom_utilisateur AS auteur,
                    COALESCE((SELECT SUM(valeur) FROM evaluations WHERE touite_id = t.id), COUNT(DISTINCT l.utilisateur_id), 0) AS nombre_likes
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

    /**
     * Retourne le mur de l'utilisateur : les touites des personnes suivies
     * ET des tags auxquels il est abonné, triés du plus récent au plus ancien.
     *
     * @param int $userId Identifiant de l'utilisateur connecté
     * @return list<array<string, mixed>>
     */
    public function wall(int $userId): array
    {
        $request = $this->db->prepare(
            'SELECT DISTINCT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur,
                    u.nom_utilisateur AS auteur,
                    COALESCE((SELECT SUM(valeur) FROM evaluations WHERE touite_id = t.id), COUNT(DISTINCT l.utilisateur_id), 0) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             LEFT JOIN likes l ON l.touite_id = t.id
             WHERE EXISTS (
                 SELECT 1
                 FROM suivis s
                 WHERE s.suiveur_id = :user_id_followed
                   AND s.suivi_id = t.auteur_id
             )
             OR EXISTS (
                 SELECT 1
                 FROM touite_tags tt
                 JOIN abonnements_tags at ON at.tag_id = tt.tag_id
                 WHERE at.utilisateur_id = :user_id_tags
                   AND tt.touite_id = t.id
             )
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      u.nom_utilisateur
             ORDER BY t.date_publication DESC, t.id DESC'
        );
        $request->execute([
            'user_id_followed' => $userId,
            'user_id_tags'     => $userId,
        ]);

        return $request->fetchAll();
    }

    /**
     * Retourne tous les touites d'un auteur, du plus récent au plus ancien.
     *
     * @param int $authorId Identifiant de l'auteur
     * @return list<array<string, mixed>>
     */
    public function byAuthor(int $authorId): array
    {
        $request = $this->db->prepare(
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur,
                    u.nom_utilisateur AS auteur,
                    COALESCE((SELECT SUM(valeur) FROM evaluations WHERE touite_id = t.id), COUNT(DISTINCT l.utilisateur_id), 0) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             LEFT JOIN likes l ON l.touite_id = t.id
             WHERE t.auteur_id = :author_id
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      u.nom_utilisateur
             ORDER BY t.date_publication DESC, t.id DESC'
        );
        $request->execute(['author_id' => $authorId]);

        return $request->fetchAll();
    }

    /**
     * Retourne tous les touites associés à un tag, du plus récent au plus ancien.
     *
     * @param int $tagId Identifiant du tag
     * @return list<array<string, mixed>>
     */
    public function byTag(int $tagId): array
    {
        $request = $this->db->prepare(
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur,
                    u.nom_utilisateur AS auteur,
                    COALESCE((SELECT SUM(valeur) FROM evaluations WHERE touite_id = t.id), COUNT(DISTINCT l.utilisateur_id), 0) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             JOIN touite_tags tt ON tt.touite_id = t.id
             LEFT JOIN likes l ON l.touite_id = t.id
             WHERE tt.tag_id = :tag_id
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      u.nom_utilisateur
             ORDER BY t.date_publication DESC, t.id DESC'
        );
        $request->execute(['tag_id' => $tagId]);

        return $request->fetchAll();
    }

    /**
     * Crée un nouveau touite et extrait automatiquement ses tags (#hashtags).
     * Toute la création se fait dans une transaction pour garantir la cohérence.
     *
     * @param string      $texte    Contenu du touite (max 235 caractères)
     * @param int         $auteurId Identifiant de l'auteur
     * @param string|null $idImg    Nom du fichier image associé (ou null)
     * @return int Identifiant du touite nouvellement créé
     * @throws \Throwable En cas d'erreur BDD (rollback automatique)
     */
    public function create(string $texte, int $auteurId, ?string $idImg = null): int
    {
        $this->db->beginTransaction();
        try {
            // Insère le touite principal
            $request = $this->db->prepare(
                'INSERT INTO touites (texte, auteur_id, image)
                 VALUES (:texte, :auteur, :id_img)'
            );
            $request->execute([
                'texte'  => $texte,
                'auteur' => $auteurId,
                'id_img' => $idImg,
            ]);
            $touiteId = (int) $this->db->lastInsertId();

            // Extrait les tags (#mot) du texte
            preg_match_all('/#([\p{L}\p{N}_-]+)/u', $texte, $matches);
            $tagNames = array_values(array_unique(array_map('mb_strtolower', $matches[1] ?? [])));

            // Prépare les requêtes de gestion des tags
            $findTag   = $this->db->prepare('SELECT id FROM tags WHERE nom = :nom');
            $createTag = $this->db->prepare('INSERT IGNORE INTO tags (nom) VALUES (:nom)');
            $linkTag   = $this->db->prepare(
                'INSERT IGNORE INTO touite_tags (touite_id, tag_id)
                 VALUES (:touite_id, :tag_id)'
            );

            foreach ($tagNames as $tagName) {
                $createTag->execute(['nom' => $tagName]);
                $findTag->execute(['nom' => $tagName]);
                $tagId = $findTag->fetchColumn();
                if ($tagId !== false) {
                    $linkTag->execute(['touite_id' => $touiteId, 'tag_id' => (int) $tagId]);
                }
            }

            $this->db->commit();
            return $touiteId;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    /**
     * Retourne l'identifiant d'un auteur d'après son nom d'utilisateur.
     *
     * @param string $username Nom d'utilisateur
     * @return int|null Identifiant ou null si introuvable
     */
    public function findAuthorIdByName(string $username): ?int
    {
        $request = $this->db->prepare('SELECT id FROM utilisateurs WHERE nom_utilisateur = :username');
        $request->execute(['username' => $username]);
        $id = $request->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /**
     * Compte le nombre total de touites, avec filtre optionnel par auteur.
     *
     * @param int|null $authorId Identifiant de l'auteur (null = tous les touites)
     */
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

    /**
     * Calcule le score moyen de likes par touite pour un auteur donné.
     * Basé sur la table `likes` (legacy).
     *
     * @param int $authorId Identifiant de l'auteur
     * @return float Moyenne arrondie à 2 décimales, ou 0.0 si aucun touite
     */
    public function averageScore(int $authorId): float
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(l.utilisateur_id) AS total_likes, COUNT(DISTINCT t.id) AS total_touites
             FROM touites t
             LEFT JOIN likes l ON l.touite_id = t.id
             WHERE t.auteur_id = :author_id'
        );
        $statement->execute(['author_id' => $authorId]);
        $row = $statement->fetch();

        if (!$row || (int) $row['total_touites'] === 0) {
            return 0.0;
        }

        return round((int) $row['total_likes'] / (int) $row['total_touites'], 2);
    }

    /**
     * Retourne une page de touites paginée, avec filtre optionnel par auteur.
     *
     * @param int      $page     Numéro de page (commence à 1)
     * @param int      $perPage  Nombre de touites par page (défaut 10)
     * @param int|null $authorId Filtre optionnel par auteur
     * @return list<array<string, mixed>>
     */
    public function paginate(int $page, int $perPage = 10, ?int $authorId = null): array
    {
        $offset       = ($page - 1) * $perPage;
        $authorFilter = $authorId === null ? '' : 'WHERE t.auteur_id = :author_id';

        $request = $this->db->prepare(
            'SELECT t.id AS id_touite, t.texte,
                    t.date_publication AS date_touite,
                    t.auteur_id AS id_auteur,
                    u.nom_utilisateur AS auteur,
                    COALESCE((SELECT SUM(valeur) FROM evaluations WHERE touite_id = t.id), COUNT(DISTINCT l.utilisateur_id), 0) AS nombre_likes
             FROM touites t
             JOIN utilisateurs u ON u.id = t.auteur_id
             LEFT JOIN likes l ON l.touite_id = t.id
             ' . $authorFilter . '
             GROUP BY t.id, t.texte, t.date_publication, t.auteur_id,
                      u.nom_utilisateur
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

    /**
     * Enregistre un vote (+1 like ou -1 dislike) pour un touite.
     * Si l'utilisateur a déjà voté, retourne false sans rien faire.
     * Pour un like, synchronise aussi la table `likes` (legacy).
     * Pour un dislike, supprime l'éventuel like legacy.
     *
     * @param int $userId   Identifiant de l'utilisateur qui vote
     * @param int $touiteId Identifiant du touite voté
     * @param int $valeur   +1 (like) ou -1 (dislike)
     * @return bool true si le vote a été enregistré, false s'il existait déjà
     */
    public function evaluate(int $userId, int $touiteId, int $valeur): bool
    {
        if ($this->hasEvaluated($userId, $touiteId)) {
            return false;
        }

        // Normalise la valeur : uniquement +1 ou -1
        $valeur = $valeur < 0 ? -1 : 1;

        $request = $this->db->prepare(
            'INSERT INTO evaluations (utilisateur_id, touite_id, valeur)
             VALUES (:user_id, :touite_id, :valeur)'
        );
        $request->execute([
            'user_id'   => $userId,
            'touite_id' => $touiteId,
            'valeur'    => $valeur,
        ]);

        if ($valeur === 1) {
            // Synchronise le like dans la table legacy
            $this->evaluatePositive($userId, $touiteId);
        } else {
            // Supprime le like legacy en cas de dislike
            $del = $this->db->prepare(
                'DELETE FROM likes WHERE utilisateur_id = :user_id AND touite_id = :touite_id'
            );
            $del->execute([
                'user_id'   => $userId,
                'touite_id' => $touiteId,
            ]);
        }

        return true;
    }

    /**
     * Insère un like dans la table legacy `likes` (INSERT IGNORE pour éviter les doublons).
     *
     * @param int $userId   Identifiant de l'utilisateur
     * @param int $touiteId Identifiant du touite
     * @return bool true si le like a été inséré, false s'il existait déjà
     */
    public function evaluatePositive(int $userId, int $touiteId): bool
    {
        $request = $this->db->prepare(
            'INSERT IGNORE INTO likes (utilisateur_id, touite_id)
             VALUES (:user_id, :touite_id)'
        );

        $request->execute([
            'user_id'   => $userId,
            'touite_id' => $touiteId,
        ]);

        return $request->rowCount() > 0;
    }

    /**
     * Vérifie si un utilisateur a déjà voté pour un touite.
     * Vérifie à la fois la table `evaluations` ET la table `likes` (legacy).
     *
     * @param int $userId   Identifiant de l'utilisateur
     * @param int $touiteId Identifiant du touite
     */
    public function hasEvaluated(int $userId, int $touiteId): bool
    {
        // Vérifie dans la table évaluations
        $request = $this->db->prepare(
            'SELECT COUNT(*)
             FROM evaluations
             WHERE utilisateur_id = :user_id
             AND touite_id = :touite_id'
        );
        $request->execute([
            'user_id'   => $userId,
            'touite_id' => $touiteId,
        ]);

        if ((int) $request->fetchColumn() > 0) {
            return true;
        }

        // Vérifie dans la table likes (legacy)
        $requestLikes = $this->db->prepare(
            'SELECT COUNT(*) FROM likes WHERE utilisateur_id = :user_id AND touite_id = :touite_id'
        );
        $requestLikes->execute([
            'user_id'   => $userId,
            'touite_id' => $touiteId,
        ]);

        return (int) $requestLikes->fetchColumn() > 0;
    }

    /**
     * Retourne la valeur du vote d'un utilisateur pour un touite.
     * Retourne +1, -1 ou null (pas encore voté).
     * Si un like legacy existe mais pas d'évaluation, retourne +1 par convention.
     *
     * @param int $userId   Identifiant de l'utilisateur
     * @param int $touiteId Identifiant du touite
     */
    public function getUserEvaluation(int $userId, int $touiteId): ?int
    {
        // Cherche d'abord dans la table evaluations
        $request = $this->db->prepare(
            'SELECT valeur FROM evaluations WHERE utilisateur_id = :user_id AND touite_id = :touite_id'
        );
        $request->execute([
            'user_id'   => $userId,
            'touite_id' => $touiteId,
        ]);
        $val = $request->fetchColumn();
        if ($val !== false && $val !== null) {
            return (int) $val;
        }

        // Fallback : si un like legacy existe, considère que c'est +1
        if ($this->hasEvaluated($userId, $touiteId)) {
            return 1;
        }

        return null;
    }

    /**
     * Retourne le score total d'un touite (somme des valeurs d'évaluation).
     * Fallback sur le nombre de likes de la table legacy si aucune évaluation.
     *
     * @param int $touiteId Identifiant du touite
     */
    public function getScore(int $touiteId): int
    {
        // Calcule le score depuis la table evaluations
        $request = $this->db->prepare(
            'SELECT SUM(valeur) FROM evaluations WHERE touite_id = :touite_id'
        );
        $request->execute(['touite_id' => $touiteId]);
        $score = $request->fetchColumn();

        if ($score !== false && $score !== null) {
            return (int) $score;
        }

        // Fallback sur le nombre de likes legacy
        $requestLikes = $this->db->prepare(
            'SELECT COUNT(*) FROM likes WHERE touite_id = :touite_id'
        );
        $requestLikes->execute(['touite_id' => $touiteId]);

        return (int) $requestLikes->fetchColumn();
    }
}
