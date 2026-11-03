<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\ImageRepositoryInterface;
use PDO;

final class ImageRepository implements ImageRepositoryInterface
{
    private static ?ImageRepository $instance = null;

    private function __construct(private readonly PDO $db) {}

    public static function getInstance(PDO $db): static
    {
        return self::$instance ??= new self($db);
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id_img, chemin_fichier, description_img FROM IMAGE ORDER BY id_img'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $request = $this->db->prepare(
            'SELECT id_img, chemin_fichier, description_img
             FROM IMAGE
             WHERE id_img = :id'
        );
        $request->execute(['id' => $id]);
        $image = $request->fetch();

        return $image === false ? null : $image;
    }

    public function create(int $id, string $chemin, string $description): void
    {
        $request = $this->db->prepare(
            'INSERT INTO IMAGE (id_img, chemin_fichier, description_img)
             VALUES (:id, :chemin, :description)'
        );
        $request->execute([
            'id' => $id,
            'chemin' => $chemin,
            'description' => $description,
        ]);
    }
}
