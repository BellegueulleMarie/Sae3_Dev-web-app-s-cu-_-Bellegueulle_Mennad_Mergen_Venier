<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface TagRepositoryInterface
 *
 * Contrat pour accéder aux données des tags en base de données.
 * Les tags sont les mots-clés (#hashtags) présents dans les touites.
 */
interface TagRepositoryInterface
{
    /**
     * Retourne l'instance unique du repository (pattern Singleton).
     *
     * @param \PDO $db Connexion PDO à la base de données
     */
    public static function getInstance(\PDO $db): static;

    /**
     * Retourne tous les tags existants, triés par nom.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array;

    /**
     * Retourne un tag par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant du tag
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * Retourne un tag par son nom, ou null si introuvable.
     *
     * @param string $name Nom du tag (sans le #)
     * @return array<string, mixed>|null
     */
    public function findByName(string $name): ?array;

    /**
     * Retourne le tag correspondant au nom donné, et le crée s'il n'existe pas.
     *
     * @param string $name Nom du tag (sans le #)
     * @return array<string, mixed> Le tag trouvé ou créé
     */
    public function findOrCreateByName(string $name): array;

    /**
     * Retourne les statistiques de chaque tag :
     * nombre de touites, total de likes, moyenne de likes par touite.
     *
     * @return list<array<string, mixed>>
     */
    public function statistics(): array;

    /**
     * Vérifie si un utilisateur est abonné à un tag.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $tagId  Identifiant du tag
     */
    public function isSubscribed(int $userId, int $tagId): bool;

    /**
     * Abonne un utilisateur à un tag.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $tagId  Identifiant du tag
     */
    public function subscribe(int $userId, int $tagId): void;

    /**
     * Désabonne un utilisateur d'un tag.
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $tagId  Identifiant du tag
     */
    public function unsubscribe(int $userId, int $tagId): void;

    /**
     * Retourne les tendances : les tags triés par nombre de mentions dans les touites.
     *
     * @param string $order Ordre de tri : 'DESC' (plus utilisé en premier) ou 'ASC'
     * @return list<array<string, mixed>>
     */
    public function trends(string $order = 'DESC'): array;
}
