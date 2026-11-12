<?php

declare(strict_types=1);

namespace App\Contracts;

use PDO;

/**
 * Interface UtilisateurRepositoryInterface
 *
 * Contrat pour accéder aux données des utilisateurs en base de données.
 */
interface UtilisateurRepositoryInterface
{
    /**
     * Retourne l'instance unique du repository (pattern Singleton).
     *
     * @param PDO $db Connexion PDO à la base de données
     */
    public static function getInstance(PDO $db): static;

    /**
     * Retourne tous les utilisateurs avec leur nombre de touites.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array;

    /**
     * Retourne tous les utilisateurs triés par nombre de suiveurs croissant.
     * Utilisé pour la vue d'administration.
     *
     * @return list<array<string, mixed>>
     */
    public function usersByFollowerCount(): array;

    /**
     * Retourne un utilisateur par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant de l'utilisateur
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * Abonne un utilisateur à un autre.
     *
     * @param int $followerId Identifiant de l'abonné
     * @param int $followedId Identifiant de la personne suivie
     * @throws \InvalidArgumentException Si l'utilisateur tente de se suivre lui-même
     */
    public function follow(int $followerId, int $followedId): void;

    /**
     * Désabonne un utilisateur d'un autre.
     *
     * @param int $followerId Identifiant de l'abonné
     * @param int $followedId Identifiant de la personne suivie
     */
    public function unfollow(int $followerId, int $followedId): void;

    /**
     * Vérifie si un utilisateur suit un autre.
     *
     * @param int $followerId Identifiant de l'abonné
     * @param int $followedId Identifiant de la personne suivie
     */
    public function isFollowing(int $followerId, int $followedId): bool;

    /**
     * Retourne le nombre d'abonnés d'un utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur
     */
    public function followerCount(int $userId): int;

    /**
     * Retourne la liste des abonnés d'un utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @return list<array<string, mixed>>
     */
    public function followers(int $userId): array;

    /**
     * Crée un nouvel utilisateur en base de données.
     *
     * @param string $nom    Nom de famille
     * @param string $prenom Prénom
     * @param string $email  Adresse e-mail (doit être unique)
     */
    public function create(string $nom, string $prenom, string $email): void;

    /**
     * Met à jour les informations d'un utilisateur existant.
     *
     * @param int    $id     Identifiant de l'utilisateur
     * @param string $nom    Nouveau nom
     * @param string $prenom Nouveau prénom
     * @param string $email  Nouvelle adresse e-mail
     */
    public function update(int $id, string $nom, string $prenom, string $email): void;

    /**
     * Retourne les influenceurs : utilisateurs triés par nombre de suiveurs.
     *
     * @param string $order 'DESC' = du plus suivi au moins suivi (défaut), 'ASC' = inverse
     * @return list<array<string, mixed>>
     */
    public function influencers(string $order = 'DESC'): array;
}
