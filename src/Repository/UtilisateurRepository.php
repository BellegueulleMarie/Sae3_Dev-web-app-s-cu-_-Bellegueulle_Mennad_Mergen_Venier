<?php

declare(strict_types=1);

namespace App\Repository;

use App\Contracts\UtilisateurRepositoryInterface;
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
            'SELECT id_util, nom, prenom, email, nb_touites FROM UTILISATEUR ORDER BY id_util'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id_util, nom, prenom, email, nb_touites FROM UTILISATEUR WHERE id_util = :id'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    public function create(string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO UTILISATEUR (nom, prenom, email)
             VALUES (:nom, :prenom, :email)'
        );
        $statement->execute(compact('nom', 'prenom', 'email'));
    }

    public function update(int $id, string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'UPDATE UTILISATEUR
             SET nom = :nom, prenom = :prenom, email = :email
             WHERE id_util = :id'
        );
        $statement->execute(compact('id', 'nom', 'prenom', 'email'));
    }
    public function delete(int $id): void {
        $statement = $this->db->prepare(
            'DELETE FROM UTILISATEUR WHERE id_util = :id'
        );
        $statement->execute(['id' => $id]);
    }
}
