<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use PDO;

final class UtilisateurRepository implements UtilisateurRepositoryInterface
{
    private static ?UtilisateurRepository $instance = null;

    private function __construct(private readonly PDO $db) {}

    public static function getInstance(PDO $db): static
    {
        return self::$instance ??= new self($db);
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->query(
            'SELECT u.id AS id_util, u.nom_utilisateur AS nom, u.email,
                    COUNT(t.id) AS nb_touites
             FROM utilisateurs u
             LEFT JOIN touites t ON t.auteur_id = u.id
             GROUP BY u.id, u.nom_utilisateur, u.email
             ORDER BY u.id'
        )->fetchAll();
    }

    /** @return list<array<string, mixed>> */
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

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id AS id_util, u.nom_utilisateur AS nom, u.email,
                    u.role, COUNT(t.id) AS nb_touites
             FROM utilisateurs u
             LEFT JOIN touites t ON t.auteur_id = u.id
             WHERE u.id = :id
             GROUP BY u.id, u.nom_utilisateur, u.email, u.role'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

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

    public function followerCount(int $userId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM suivis WHERE suivi_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
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

    public function create(string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe)
             VALUES (:nom_utilisateur, :email, :mot_de_passe)'
        );
        $statement->execute([
            'nom_utilisateur' => trim($prenom . ' ' . $nom),
            'email' => $email,
            'mot_de_passe' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
        ]);
    }

    public function update(int $id, string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'UPDATE utilisateurs
             SET nom_utilisateur = :nom_utilisateur, email = :email
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'nom_utilisateur' => trim($prenom . ' ' . $nom),
            'email' => $email,
        ]);
    }
    public function delete(int $id): void {
        $statement = $this->db->prepare(
            'DELETE FROM utilisateurs WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

}
