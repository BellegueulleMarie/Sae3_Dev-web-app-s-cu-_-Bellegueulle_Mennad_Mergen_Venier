<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\ImageRepositoryInterface;
use PDO;

/**
 * Classe ImageRepository
 *
 * Accède aux images associées aux touites dans la base de données.
 * Implémente le pattern Singleton pour partager une seule instance par requête.
 */
final class ImageRepository implements ImageRepositoryInterface
{
    /** @var ImageRepository|null Instance unique (Singleton) */
    private static ?ImageRepository $instance = null;

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
     * Retourne toutes les images (chemin de fichier) liées aux touites.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        return $this->db->query(
            'SELECT id, image AS chemin_fichier FROM touites ORDER BY id'
        )->fetchAll();
    }

    /**
     * Retourne l'image d'un touite par son id, ou null si introuvable.
     *
     * @param int $id Identifiant du touite
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT id, image AS chemin_fichier
             FROM touites
             WHERE id = :id'
        );
        $request->execute(['id' => $id]);
        $image = $request->fetch();

        return $image === false ? null : $image;
    }

    /**
     * Associe une image à un touite existant en mettant à jour la colonne `image`.
     *
     * @param int    $id          Identifiant du touite
     * @param string $chemin      Nom du fichier image stocké sur le serveur
     * @param string $description Description (non stockée dans la BDD actuelle)
     */
    public function create(int $id, string $chemin, string $description): void
    {
        $request = $this->db->prepare(
            'UPDATE touites SET image = :chemin WHERE id = :id'
        );
        $request->execute([
            'id'          => $id,
            'chemin'      => $chemin,
            'description' => $description,
        ]);
    }
}
