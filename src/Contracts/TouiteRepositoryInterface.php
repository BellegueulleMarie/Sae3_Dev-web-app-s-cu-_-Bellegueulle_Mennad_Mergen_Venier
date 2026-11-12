<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface TouiteRepositoryInterface
 *
 * Contrat pour accéder aux données des touites en base de données.
 * Un touite est un message court publié par un utilisateur.
 */
interface TouiteRepositoryInterface
{
    /**
     * Retourne l'instance unique du repository (pattern Singleton).
     *
     * @param \PDO $db Connexion PDO à la base de données
     */
    public static function getInstance(\PDO $db): static;

    /**
     * Retourne tous les touites, du plus récent au plus ancien.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array;

    /**
     * Retourne un touite par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant du touite
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * Retourne le mur d'un utilisateur : les touites des utilisateurs qu'il suit
     * et des tags auxquels il est abonné.
     *
     * @param int $userId Identifiant de l'utilisateur connecté
     * @return list<array<string, mixed>>
     */
    public function wall(int $userId): array;

    /**
     * Retourne tous les touites publiés par un auteur donné.
     *
     * @param int $authorId Identifiant de l'auteur
     * @return list<array<string, mixed>>
     */
    public function byAuthor(int $authorId): array;

    /**
     * Retourne tous les touites associés à un tag donné.
     *
     * @param int $tagId Identifiant du tag
     * @return list<array<string, mixed>>
     */
    public function byTag(int $tagId): array;

    /**
     * Crée un nouveau touite et extrait automatiquement ses tags.
     *
     * @param string      $texte    Texte du touite (max 235 caractères)
     * @param int         $auteurId Identifiant de l'auteur
     * @param string|null $idImg    Nom du fichier image associé (ou null)
     * @return int Identifiant du touite créé
     */
    public function create(string $texte, int $auteurId, ?string $idImg = null): int;

    /**
     * Compte le nombre total de touites, avec filtre optionnel par auteur.
     *
     * @param int|null $authorId Identifiant de l'auteur (null = tous)
     */
    public function count(?int $authorId = null): int;

    /**
     * Calcule le score moyen (likes / nombre de touites) d'un auteur.
     *
     * @param int $authorId Identifiant de l'auteur
     */
    public function averageScore(int $authorId): float;

    /**
     * Retourne une page de touites paginée.
     *
     * @param int      $page     Numéro de page (commence à 1)
     * @param int      $perPage  Nombre de touites par page
     * @param int|null $authorId Filtre optionnel par auteur
     * @return list<array<string, mixed>>
     */
    public function paginate(int $page, int $perPage = 10, ?int $authorId = null): array;

    /**
     * Enregistre un vote (+1 like ou -1 dislike) pour un touite.
     * Retourne false si l'utilisateur a déjà voté.
     *
     * @param int $userId   Identifiant de l'utilisateur votant
     * @param int $touiteId Identifiant du touite
     * @param int $valeur   +1 (like) ou -1 (dislike)
     */
    public function evaluate(int $userId, int $touiteId, int $valeur): bool;

    /**
     * Enregistre un like positif dans la table legacy `likes`.
     *
     * @param int $userId   Identifiant de l'utilisateur
     * @param int $touiteId Identifiant du touite
     */
    public function evaluatePositive(int $userId, int $touiteId): bool;

    /**
     * Vérifie si un utilisateur a déjà voté pour un touite.
     *
     * @param int $userId   Identifiant de l'utilisateur
     * @param int $touiteId Identifiant du touite
     */
    public function hasEvaluated(int $userId, int $touiteId): bool;

    /**
     * Retourne la valeur du vote d'un utilisateur pour un touite (+1, -1, ou null).
     *
     * @param int $userId   Identifiant de l'utilisateur
     * @param int $touiteId Identifiant du touite
     */
    public function getUserEvaluation(int $userId, int $touiteId): ?int;

    /**
     * Retourne le score total d'un touite (somme des évaluations).
     *
     * @param int $touiteId Identifiant du touite
     */
    public function getScore(int $touiteId): int;
}
