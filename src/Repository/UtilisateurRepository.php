<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use PDO;

/**
 * Classe UtilisateurRepository
 *
 * Accède aux données des utilisateurs en base de données.
 * Implémente le pattern Singleton pour partager une seule instance par requête.
 */
final class UtilisateurRepository implements UtilisateurRepositoryInterface
{
    /** @var UtilisateurRepository|null Instance unique (Singleton) */
    private static ?UtilisateurRepository $instance = null;

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
     * Retourne tous les utilisateurs avec leur rôle et nombre de touites publiés.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT u.id AS id_util, u.nom_utilisateur AS nom, u.email, u.role,
                    COUNT(t.id) AS nb_touites
             FROM utilisateurs u
             LEFT JOIN touites t ON t.auteur_id = u.id
             GROUP BY u.id, u.nom_utilisateur, u.email, u.role
             ORDER BY u.id'
        )->fetchAll();
    }

    /**
     * Retourne tous les utilisateurs triés par nombre de suiveurs (croissant).
     * Utilisé pour la vue d'administration.
     *
     * @return list<array<string, mixed>>
     */
    public function usersByFollowerCount(): array
    {
        return $this->db->query(
            'SELECT u.id AS id_util, u.nom_utilisateur AS nom,
                    COUNT(s.suiveur_id) AS nb_suiveurs
             FROM utilisateurs u
             LEFT JOIN suivis s ON s.suivi_id = u.id
             GROUP BY u.id, u.nom_utilisateur
             ORDER BY nb_suiveurs ASC, u.nom_utilisateur ASC'
        )->fetchAll();
    }

    /**
     * Retourne un utilisateur par son identifiant, ou null si introuvable.
     * Inclut son rôle et son nombre de touites.
     *
     * @param int $id Identifiant de l'utilisateur
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id AS id_util, u.nom_utilisateur AS nom, u.email, u.role,
                    COUNT(t.id) AS nb_touites
             FROM utilisateurs u
             LEFT JOIN touites t ON t.auteur_id = u.id
             WHERE u.id = :id
             GROUP BY u.id, u.nom_utilisateur, u.email, u.role'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    /**
     * Abonne un utilisateur à un autre (insert dans la table `suivis`).
     *
     * @param int $followerId Identifiant de l'abonné
     * @param int $followedId Identifiant de la personne suivie
     * @throws InvalidArgumentException Si l'utilisateur tente de se suivre lui-même
     */
    public function follow(int $followerId, int $followedId): void
    {
        if ($followerId === $followedId) {
            throw new InvalidArgumentException('Un utilisateur ne peut pas se suivre lui-même.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO suivis (suiveur_id, suivi_id)
             VALUES (:follower, :followed)'
        );
        $statement->execute([
            'follower' => $followerId,
            'followed' => $followedId,
        ]);
    }

    /**
     * Vérifie si un utilisateur suit un autre.
     *
     * @param int $followerId Identifiant de l'abonné
     * @param int $followedId Identifiant de la personne suivie
     */
    public function isFollowing(int $followerId, int $followedId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM suivis
             WHERE suiveur_id = :follower AND suivi_id = :followed'
        );
        $statement->execute([
            'follower' => $followerId,
            'followed' => $followedId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * Désabonne un utilisateur d'un autre.
     *
     * @param int $followerId Identifiant de l'abonné
     * @param int $followedId Identifiant de la personne suivie
     */
    public function unfollow(int $followerId, int $followedId): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM suivis
             WHERE suiveur_id = :follower AND suivi_id = :followed'
        );
        $statement->execute([
            'follower' => $followerId,
            'followed' => $followedId,
        ]);
    }

    /**
     * Retourne le nombre total d'abonnés d'un utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur
     */
    public function followerCount(int $userId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM suivis WHERE suivi_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * Retourne la liste des abonnés d'un utilisateur, triée alphabétiquement.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @return list<array<string, mixed>>
     */
    public function followers(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT u.id AS id_util, u.nom_utilisateur AS nom, u.email
             FROM utilisateurs u
             JOIN suivis s ON s.suiveur_id = u.id
             WHERE s.suivi_id = :user_id
             ORDER BY u.nom_utilisateur ASC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    /**
     * Crée un nouvel utilisateur avec un mot de passe aléatoire hashé.
     * Le nom affiché est "prénom nom".
     *
     * @param string $nom    Nom de famille
     * @param string $prenom Prénom
     * @param string $email  Adresse e-mail (doit être unique)
     */
    public function create(string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe)
             VALUES (:nom_utilisateur, :email, :mot_de_passe)'
        );
        $statement->execute([
            'nom_utilisateur' => trim($prenom . ' ' . $nom),
            'email'           => $email,
            'mot_de_passe'    => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
        ]);
    }

    /**
     * Met à jour le nom affiché et l'e-mail d'un utilisateur.
     * Le nom affiché est reconstruit en "prénom nom".
     *
     * @param int    $id     Identifiant de l'utilisateur
     * @param string $nom    Nouveau nom de famille
     * @param string $prenom Nouveau prénom
     * @param string $email  Nouvelle adresse e-mail
     */
    public function update(int $id, string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'UPDATE utilisateurs
             SET nom_utilisateur = :nom_utilisateur, email = :email
             WHERE id = :id'
        );
        $statement->execute([
            'id'              => $id,
            'nom_utilisateur' => trim($prenom . ' ' . $nom),
            'email'           => $email,
        ]);
    }

    /**
     * Supprime un utilisateur de la base de données.
     *
     * @param int $id Identifiant de l'utilisateur à supprimer
     */
    public function delete(int $id): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM utilisateurs WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    /**
     * Retourne les influenceurs : tous les utilisateurs triés par nombre de suiveurs.
     * Inclut le rôle et l'e-mail de chaque utilisateur.
     *
     * @param string $order 'DESC' = du plus suivi au moins suivi (défaut), 'ASC' = inverse
     * @return list<array<string, mixed>>
     */
    public function influencers(string $order = 'DESC'): array
    {
        // Sécurise la valeur d'ordre pour éviter toute injection SQL
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $statement = $this->db->prepare(
            "SELECT u.id AS id_util, u.nom_utilisateur AS nom, u.email, u.role,
                    COUNT(s.suiveur_id) AS nb_suiveurs
             FROM utilisateurs u
             LEFT JOIN suivis s ON s.suivi_id = u.id
             GROUP BY u.id, u.nom_utilisateur, u.email, u.role
             ORDER BY nb_suiveurs {$order}, u.nom_utilisateur ASC"
        );
        $statement->execute();

        return $statement->fetchAll();
    }
}
