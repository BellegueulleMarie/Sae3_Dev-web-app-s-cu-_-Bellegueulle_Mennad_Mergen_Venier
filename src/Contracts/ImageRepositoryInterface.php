<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface ImageRepositoryInterface
 *
 * Contrat pour accéder aux images liées aux touites en base de données.
 */
interface ImageRepositoryInterface
{
    /**
     * Retourne l'instance unique du repository (pattern Singleton).
     *
     * @param \PDO $db Connexion PDO à la base de données
     */
    public static function getInstance(\PDO $db): static;

    /**
     * Retourne toutes les images enregistrées.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array;

    /**
     * Retourne une image par son identifiant, ou null si introuvable.
     *
     * @param int $id Identifiant de l'image
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * Associe une image à un touite existant.
     *
     * @param int    $id          Identifiant du touite
     * @param string $chemin      Nom du fichier image stocké sur le serveur
     * @param string $description Description de l'image (non stockée dans la BDD actuelle)
     */
    public function create(int $id, string $chemin, string $description): void;
}
